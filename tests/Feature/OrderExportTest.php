<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_orders_as_xlsx(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        // Create dependent models
        $domain = Domain::create([
            'domain_name' => 'example.com',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'full_name' => 'Test Customer',
            'phone' => '05555555555',
        ]);

        // Create an order
        $order = Order::create([
            'domain_id' => $domain->id,
            'customer_id' => $customer->id,
            'grand_total' => 1250.00,
            'status' => 'pending',
            'address' => 'Test Address',
            'city' => 'Istanbul',
            'district' => 'Kadikoy',
        ]);

        // Hit the export route
        $response = $this->actingAs($admin)
            ->get(route('admin.orders.export'));

        // Check if the status is 200 (download request initiated)
        $response->assertStatus(200);

        // Assert download headers for Excel XLSX
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename=' . 'siparisler_' . now()->format('Ymd_His') . '.xlsx');

        // Let's assert that the returned Excel content is indeed valid XLSX by attempting to load it using PhpSpreadsheet
        $content = $response->streamedContent();
        
        // Write streamedContent to a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_export_test');
        file_put_contents($tempFile, $content);

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempFile);
            $sheet = $spreadsheet->getActiveSheet();

            // Verify header values
            $this->assertEquals('Tarih', $sheet->getCell('A1')->getValue());
            $this->assertEquals('Sipariş No', $sheet->getCell('B1')->getValue());
            $this->assertEquals('Müşteri', $sheet->getCell('C1')->getValue());
            $this->assertEquals('Telefon', $sheet->getCell('D1')->getValue());
            $this->assertEquals('Domain', $sheet->getCell('E1')->getValue());
            $this->assertEquals('Tutar', $sheet->getCell('F1')->getValue());
            $this->assertEquals('Durum', $sheet->getCell('G1')->getValue());
            $this->assertEquals('Kargo', $sheet->getCell('H1')->getValue());

            // Verify first data row
            $this->assertEquals($order->order_number, $sheet->getCell('B2')->getValue());
            $this->assertEquals('Test Customer', $sheet->getCell('C2')->getValue());
            $this->assertEquals('05555555555', $sheet->getCell('D2')->getValue());
            $this->assertEquals('example.com', $sheet->getCell('E2')->getValue());
            $this->assertEquals(1250, $sheet->getCell('F2')->getValue());
            $this->assertEquals('Beklemede', $sheet->getCell('G2')->getValue());
        } finally {
            unlink($tempFile);
        }
    }
}
