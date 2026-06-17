<?php

namespace Tests\Feature;

use App\Models\AffiliateClick;
use App\Models\AffiliateCommission;
use App\Models\AffiliateDomain;
use App\Models\AffiliateLink;
use App\Models\AffiliateOrderAttribution;
use App\Models\AffiliatePackageCommission;
use App\Models\AffiliateUser;
use App\Models\AffiliateWithdrawalRequest;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Offer;
use App\Models\Order;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AffiliateTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear caches and seed default settings for tests
        \App\Models\Setting::updateOrCreate(
            ['key' => 'affiliate_withholding_rate'],
            ['value' => '10.0', 'group' => 'affiliate']
        );
    }

    /**
     * Test affiliate user registration.
     */
    public function test_affiliate_user_registration(): void
    {
        $response = $this->post(route('affiliate.register.attempt'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '5551234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'tax_type' => 'individual',
            'tax_number' => '11111111111',
            'address' => 'Bireysel test adresi mah. sok.',
            'iban' => 'TR990006200000000012345678',
        ]);

        $response->assertRedirect(route('affiliate.login'));
        $this->assertDatabaseHas('affiliate_users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'status' => 'pending',
            'tax_type' => 'individual',
            'tax_number' => '11111111111',
            'address' => 'Bireysel test adresi mah. sok.',
        ]);
    }

    /**
     * Test affiliate login workflow.
     */
    public function test_affiliate_login_workflow(): void
    {
        // 1. Create a user starting as pending
        $affiliate = AffiliateUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '5557654321',
            'password' => 'secret123',
            'affiliate_code' => 'AFF-JANE123',
            'status' => 'pending',
            'tax_type' => 'company',
            'company_name' => 'Jane Corp',
            'tax_office' => 'Kadikoy V.D.',
            'tax_number' => '1234567890',
            'iban' => 'TR990006200000000087654321',
        ]);

        // 2. Try logging in while pending - should fail
        $response = $this->post(route('affiliate.login.attempt'), [
            'email' => 'jane@example.com',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertFalse(auth('affiliate')->check());

        // 3. Approve affiliate user
        $affiliate->update(['status' => 'active']);

        // 4. Try logging in again - should succeed
        $response = $this->post(route('affiliate.login.attempt'), [
            'email' => 'jane@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('affiliate.dashboard'));
        $this->assertTrue(auth('affiliate')->check());
        $this->assertEquals($affiliate->id, auth('affiliate')->id());
    }

    /**
     * Test full affiliate visit tracking, checkout attribution, commission snapshots, and cargo lifecycle.
     */
    public function test_full_affiliate_tracking_and_cargo_lifecycle(): void
    {
        // 1. Setup Domain & Offer
        $domain = Domain::create([
            'domain_name' => 'nipponsampuan.com',
            'title' => 'Nippon Şampuan Satış Sitesi',
            'slug' => 'nippon-sampuan',
            'is_active' => true,
        ]);

        $offer = Offer::create([
            'domain_id' => $domain->id,
            'offer_name' => '3 Al 2 Öde Nippon Şampuan',
            'price' => 299.90,
            'quantity' => 3,
        ]);

        // 2. Setup Affiliate User (Active)
        $affiliate = AffiliateUser::create([
            'name' => 'Affiliate Marketer',
            'email' => 'marketer@example.com',
            'password' => 'password123',
            'affiliate_code' => 'AFF-MARKET',
            'status' => 'active',
            'tax_type' => 'individual', // 10% withholding tax
            'iban' => 'TR990006200000000055555555',
        ]);

        // 3. Setup Domain Settings for Affiliate
        $affSetting = AffiliateDomain::create([
            'domain_id' => $domain->id,
            'is_affiliate_active' => true,
            'cookie_days' => 15,
            'attribution_rule' => 'last_click',
        ]);

        // 4. Setup Package Level Commission Rate (Sabit 50 TL)
        $pkgCommission = AffiliatePackageCommission::create([
            'domain_id' => $domain->id,
            'package_id' => $offer->id,
            'is_affiliate_active' => true,
            'commission_type' => 'fixed',
            'commission_amount' => 50.00,
        ]);

        // 5. Setup Affiliate Link
        $link = AffiliateLink::create([
            'affiliate_id' => $affiliate->id,
            'domain_id' => $domain->id,
            'domain_url' => 'http://nipponsampuan.com',
            'short_code' => 'nippon3',
            'full_affiliate_url' => 'http://teksat.com.tr/t/nippon3',
            'target_path' => '/',
            'status' => 'active',
        ]);

        // 6. Step: Visited Affiliate Link (Sets cookie and logs click)
        $response = $this->get('http://nipponsampuan.com/t/nippon3');

        $response->assertRedirect('/');
        $response->assertCookie('ts_affiliate_click');

        $this->assertDatabaseHas('affiliate_clicks', [
            'affiliate_id' => $affiliate->id,
            'domain_id' => $domain->id,
            'affiliate_link_id' => $link->id,
        ]);

        $click = AffiliateClick::first();
        $this->assertNotNull($click);

        // 7. Step: Customer purchases via landing page with click cookie
        // Mock standard checkout submit request
        $checkoutData = [
            'offer_id' => $offer->id,
            'name' => 'Ahmet Yilmaz',
            'phone' => '05321112233',
            'city' => 'Istanbul',
            'district' => 'Kadikoy',
            'address' => 'Moda Cd. No:45 D:2',
            'payment_method' => 'cod',
        ];

        // Perform checkout post with the tracking cookie
        $checkoutResponse = $this->withCookie('ts_affiliate_click', json_encode([
            'click_id' => $click->click_id,
            'affiliate_id' => $affiliate->id,
            'link_id' => $link->id,
            'domain_id' => $domain->id,
            'timestamp' => time(),
        ]))->post('http://nipponsampuan.com/order/submit', $checkoutData);

        $checkoutResponse->assertStatus(200);

        // Verify Order creation
        $order = Order::first();
        $this->assertNotNull($order);

        // Verify Order Attribution Logged
        $this->assertDatabaseHas('affiliate_order_attributions', [
            'order_id' => $order->id,
            'affiliate_id' => $affiliate->id,
            'click_id' => $click->click_id,
        ]);

        // Verify Commission snapshot was created
        // Withholding calculation: gross = 50.00, individual withholding = 10% (5.00), net = 45.00
        $this->assertDatabaseHas('affiliate_commissions', [
            'order_id' => $order->id,
            'affiliate_id' => $affiliate->id,
            'gross_commission' => 50.00,
            'tax_type' => 'individual',
            'withholding_amount' => 5.00,
            'vat_amount' => 0.00,
            'net_amount' => 45.00,
            'status' => 'pending',
        ]);

        $commission = AffiliateCommission::first();
        $this->assertNotNull($commission);

        // 8. Cargo Reconciliation - Reconciled (Approved)
        // Simulate cargo payment reconciled controller action
        $reconciliationController = new \App\Http\Controllers\Admin\CargoReconciliationController();
        
        // Mock Admin login
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@teksat.com.tr',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);
        $this->actingAs($admin);

        // Call Paid action
        $responsePaid = $this->post(route('admin.cargo.reconciliation.paid', $order));
        
        // Confirm commission status became approved
        $this->assertEquals('approved', $commission->fresh()->status);
        $this->assertNotNull($commission->fresh()->approved_at);

        // 9. Revert Cargo Paid Reconciliation
        $responseUnpaid = $this->post(route('admin.cargo.reconciliation.unpaid', $order));
        
        // Confirm commission status rollbacked to pending
        $this->assertEquals('pending', $commission->fresh()->status);
        $this->assertNull($commission->fresh()->approved_at);

        // 10. Order Cancellation/Return - Rejects/Cancels Commission
        // Call order status update which cancels/rejects the commission
        $orderController = new \App\Http\Controllers\Admin\OrderController();
        
        // Status code for cancel/return (cancelled)
        $responseCancel = $this->post(route('admin.orders.status', $order), [
            'status' => 'iptal',
        ]);

        // Confirm commission status becomes rejected
        $this->assertEquals('rejected', $commission->fresh()->status);
        $this->assertNotNull($commission->fresh()->rejected_at);
    }

    /**
     * Test affiliate withdrawals.
     */
    public function test_affiliate_withdrawals(): void
    {
        // Setup Affiliate User (Active)
        $affiliate = AffiliateUser::create([
            'name' => 'Affiliate Marketer',
            'email' => 'withdrawing@example.com',
            'password' => 'password123',
            'affiliate_code' => 'AFF-WITHDRAW',
            'status' => 'active',
            'tax_type' => 'none', // No tax split (Gross = Net)
            'iban' => 'TR990006200000000077777777',
        ]);

        $domain = Domain::create([
            'domain_name' => 'drclinicdamla.com',
            'title' => 'Dr Clinic Damla',
            'slug' => 'drclinic',
            'is_active' => true,
        ]);

        $offer = Offer::create([
            'domain_id' => $domain->id,
            'offer_name' => 'Package 1',
            'price' => 100.00,
            'quantity' => 1,
        ]);

        $link = AffiliateLink::create([
            'affiliate_id' => $affiliate->id,
            'domain_id' => $domain->id,
            'domain_url' => 'http://drclinicdamla.com',
            'short_code' => 'damla1',
            'full_affiliate_url' => 'http://teksat.com.tr/t/damla1',
            'target_path' => '/',
            'status' => 'active',
        ]);

        $customer = Customer::create([
            'full_name' => 'Veli Yilmaz',
            'phone' => '5550001122',
        ]);

        $order = Order::create([
            'domain_id' => $domain->id,
            'offer_id' => $offer->id,
            'customer_id' => $customer->id,
            'grand_total' => 100.00,
        ]);

        // Setup 2 Approved Commissions
        $commission1 = AffiliateCommission::create([
            'affiliate_id' => $affiliate->id,
            'order_id' => $order->id,
            'domain_id' => $domain->id,
            'purchased_package_id' => $offer->id,
            'affiliate_link_id' => $link->id,
            'order_total' => 100.00,
            'commission_type_snapshot' => 'fixed',
            'commission_amount_snapshot' => 300.00,
            'gross_commission' => 300.00,
            'tax_type' => 'none',
            'net_amount' => 300.00,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $commission2 = AffiliateCommission::create([
            'affiliate_id' => $affiliate->id,
            'order_id' => $order->id,
            'domain_id' => $domain->id,
            'purchased_package_id' => $offer->id,
            'affiliate_link_id' => $link->id,
            'order_total' => 100.00,
            'commission_type_snapshot' => 'fixed',
            'commission_amount_snapshot' => 200.00,
            'gross_commission' => 200.00,
            'tax_type' => 'none',
            'net_amount' => 200.00,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Login as affiliate
        $this->actingAs($affiliate, 'affiliate');

        // Request a withdrawal
        $response = $this->post(route('affiliate.withdrawals.request'), [
            'amount' => 500.00,
            'iban' => 'TR990006200000000077777777',
        ]);

        $response->assertRedirect(route('affiliate.withdrawals'));
        
        $this->assertDatabaseHas('affiliate_withdrawal_requests', [
            'affiliate_id' => $affiliate->id,
            'requested_amount' => 500.00,
            'net_payment' => 500.00,
            'status' => 'pending',
            'iban' => 'TR990006200000000077777777',
        ]);

        // Confirm the commission statuses updated to 'withdrawing'
        $this->assertEquals('withdrawing', $commission1->fresh()->status);
        $this->assertEquals('withdrawing', $commission2->fresh()->status);

        $withdrawal = AffiliateWithdrawalRequest::first();
        $this->assertNotNull($withdrawal);

        // Simulate admin approving the withdrawal request
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@teksat.com.tr',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);
        $this->actingAs($admin);

        // Call Admin status approve
        $responseAdmin = $this->post(route('admin.affiliate.withdrawals.status', $withdrawal), [
            'status' => 'paid',
            'admin_note' => 'Paid successfully',
        ]);

        $responseAdmin->assertRedirect();
        $this->assertEquals('paid', $withdrawal->fresh()->status);
        $this->assertEquals('paid', $commission1->fresh()->status);
        $this->assertEquals('paid', $commission2->fresh()->status);
    }

    /**
     * Test admin impersonating an affiliate user.
     */
    public function test_admin_impersonating_affiliate_user(): void
    {
        // 1. Create affiliate user
        $affiliate = AffiliateUser::create([
            'name' => 'Impersonated User',
            'email' => 'impersonated@example.com',
            'password' => 'password123',
            'affiliate_code' => 'AFF-IMPERSONATE',
            'status' => 'active',
            'tax_type' => 'none',
            'iban' => 'TR990006200000000099999999',
        ]);

        // 2. Try impersonating as anonymous/guest - should be redirected to login
        $response = $this->post(route('admin.affiliate.users.impersonate', $affiliate));
        $response->assertRedirect();

        // 3. Login as regular user without permission - should get 403
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular@teksat.com.tr',
            'password' => Hash::make('password'),
            'role' => UserRole::User,
        ]);
        $this->actingAs($regularUser);
        $response = $this->post(route('admin.affiliate.users.impersonate', $affiliate));
        $response->assertStatus(403);

        // 4. Login as global admin - should succeed
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin-impersonate@teksat.com.tr',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);
        $this->actingAs($admin);
        
        $response = $this->post(route('admin.affiliate.users.impersonate', $affiliate));
        $response->assertRedirect(route('affiliate.dashboard'));
        
        // Confirm the affiliate user is authenticated in the affiliate guard
        $this->assertTrue(auth('affiliate')->check());
        $this->assertEquals($affiliate->id, auth('affiliate')->id());
    }

    /**
     * Test affiliate promotional welcome landing page.
     */
    public function test_affiliate_promotional_landing_page(): void
    {
        // 1. Access as guest - should return 200
        $response = $this->get(route('affiliate.welcome'));
        $response->assertStatus(200);
        $response->assertSee('TEKSAT SATIŞ ORTAKLIĞI');
        $response->assertSee('Kazanç Potansiyeli');

        // 2. Access as logged in affiliate - should redirect to dashboard
        $affiliate = AffiliateUser::create([
            'name' => 'Logged Affiliate',
            'email' => 'logged@example.com',
            'password' => 'password123',
            'affiliate_code' => 'AFF-LOGGED',
            'status' => 'active',
            'tax_type' => 'none',
            'iban' => 'TR990006200000000088888888',
        ]);
        $this->actingAs($affiliate, 'affiliate');

        $response = $this->get(route('affiliate.welcome'));
        $response->assertRedirect(route('affiliate.dashboard'));
    }

    /**
     * Test admin general affiliate statistics page.
     */
    public function test_admin_affiliate_stats_page(): void
    {
        // 1. Access as guest - should redirect to login
        $response = $this->get(route('admin.affiliate.stats'));
        $response->assertRedirect(route('login'));

        // 2. Access as regular user - should fail with 403
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular-stats@teksat.com.tr',
            'password' => Hash::make('password'),
            'role' => UserRole::User,
        ]);
        $this->actingAs($regularUser);
        $response = $this->get(route('admin.affiliate.stats'));
        $response->assertStatus(403);

        // 3. Access as admin - should succeed
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin-stats@teksat.com.tr',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);
        $this->actingAs($admin);

        $response = $this->get(route('admin.affiliate.stats'));
        $response->assertStatus(200);
        $response->assertSee('Affiliate Genel İstatistikleri');
        $response->assertSee('Toplam Tıklama');
        $response->assertSee('Dönüşüm Oranı');
        $response->assertSee('Toplam Dağıtılan Komisyon');
    }
}
