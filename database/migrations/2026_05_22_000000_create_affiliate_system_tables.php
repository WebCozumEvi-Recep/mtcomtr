<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. affiliate_users
        Schema::create('affiliate_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('affiliate_code')->unique();
            $table->string('status')->default('pending'); // pending, active, suspended
            $table->string('tax_type')->default('individual'); // individual, company, none
            $table->string('iban')->nullable();
            $table->string('tax_office')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('company_name')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. affiliate_domains
        Schema::create('affiliate_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->onDelete('cascade');
            $table->boolean('is_affiliate_active')->default(false);
            $table->string('affiliate_title')->nullable();
            $table->text('affiliate_description')->nullable();
            $table->integer('cookie_days')->default(30);
            $table->string('attribution_rule')->default('last_click');
            $table->boolean('media_enabled')->default(true);
            $table->text('warning_text')->nullable();
            $table->text('forbidden_terms')->nullable();
            $table->timestamps();
        });

        // 3. affiliate_package_commissions
        Schema::create('affiliate_package_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->onDelete('cascade');
            $table->foreignId('package_id')->constrained('offers')->onDelete('cascade');
            $table->string('package_api_key')->nullable();
            $table->boolean('is_affiliate_active')->default(true);
            $table->boolean('visible_to_affiliate')->default(true);
            $table->string('commission_type')->default('fixed'); // fixed, percentage
            $table->decimal('commission_amount', 10, 2)->default(0.00);
            $table->decimal('commission_rate', 5, 2)->default(0.00);
            $table->text('affiliate_description')->nullable();
            $table->timestamps();
        });

        // 4. affiliate_media
        Schema::create('affiliate_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->onDelete('cascade');
            $table->string('title');
            $table->string('media_type')->default('image'); // image, video, banner
            $table->string('channel')->nullable(); // instagram, whatsapp, etc.
            $table->string('size_label')->nullable(); // 1080x1080, etc.
            $table->string('file_path');
            $table->text('share_text')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        // 5. affiliate_links
        Schema::create('affiliate_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliate_users')->onDelete('cascade');
            $table->foreignId('domain_id')->constrained('domains')->onDelete('cascade');
            $table->string('domain_url');
            $table->string('channel')->nullable();
            $table->string('keyword')->nullable();
            $table->foreignId('media_id')->nullable()->constrained('affiliate_media')->onDelete('set null');
            $table->string('short_code')->unique();
            $table->string('full_affiliate_url');
            $table->string('target_path')->default('/');
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        // 6. affiliate_clicks
        Schema::create('affiliate_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliate_users')->onDelete('cascade');
            $table->foreignId('affiliate_link_id')->constrained('affiliate_links')->onDelete('cascade');
            $table->foreignId('domain_id')->constrained('domains')->onDelete('cascade');
            $table->string('channel')->nullable();
            $table->string('keyword')->nullable();
            $table->foreignId('media_id')->nullable()->constrained('affiliate_media')->onDelete('set null');
            $table->string('click_id')->unique();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->string('device')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 7. affiliate_order_attributions
        Schema::create('affiliate_order_attributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('affiliate_id')->constrained('affiliate_users')->onDelete('cascade');
            $table->foreignId('affiliate_link_id')->constrained('affiliate_links')->onDelete('cascade');
            $table->string('click_id');
            $table->foreignId('domain_id')->constrained('domains')->onDelete('cascade');
            $table->string('channel')->nullable();
            $table->string('keyword')->nullable();
            $table->foreignId('media_id')->nullable()->constrained('affiliate_media')->onDelete('set null');
            $table->timestamp('created_at')->useCurrent();
        });

        // 8. affiliate_commissions
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliate_users')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('domain_id')->constrained('domains')->onDelete('cascade');
            $table->foreignId('purchased_package_id')->constrained('offers')->onDelete('cascade');
            $table->foreignId('affiliate_link_id')->constrained('affiliate_links')->onDelete('cascade');
            $table->string('channel')->nullable();
            $table->string('keyword')->nullable();
            $table->decimal('order_total', 10, 2);
            $table->string('commission_type_snapshot'); // fixed, percentage
            $table->decimal('commission_amount_snapshot', 10, 2)->default(0.00);
            $table->decimal('commission_rate_snapshot', 5, 2)->default(0.00);
            $table->decimal('gross_commission', 10, 2);
            $table->string('tax_type')->default('individual'); // individual, company, none
            $table->decimal('withholding_amount', 10, 2)->default(0.00);
            $table->decimal('vat_amount', 10, 2)->default(0.00);
            $table->decimal('net_amount', 10, 2);
            $table->string('status')->default('pending'); // pending, approved, rejected, withdrawing, paid, cancelled
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // 9. affiliate_withdrawal_requests
        Schema::create('affiliate_withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliate_users')->onDelete('cascade');
            $table->decimal('requested_amount', 10, 2);
            $table->decimal('gross_amount', 10, 2);
            $table->decimal('withholding_amount', 10, 2)->default(0.00);
            $table->decimal('vat_amount', 10, 2)->default(0.00);
            $table->decimal('net_payment', 10, 2);
            $table->string('status')->default('pending'); // pending, approved, rejected, paid
            $table->string('iban');
            $table->text('admin_note')->nullable();
            $table->string('payment_receipt')->nullable();
            $table->timestamps();
            $table->timestamp('paid_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_withdrawal_requests');
        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('affiliate_order_attributions');
        Schema::dropIfExists('affiliate_clicks');
        Schema::dropIfExists('affiliate_links');
        Schema::dropIfExists('affiliate_media');
        Schema::dropIfExists('affiliate_package_commissions');
        Schema::dropIfExists('affiliate_domains');
        Schema::dropIfExists('affiliate_users');
    }
};
