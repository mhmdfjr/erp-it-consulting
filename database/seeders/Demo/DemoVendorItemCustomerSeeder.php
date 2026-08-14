<?php
// database/seeders/Demo/DemoVendorItemCustomerSeeder.php

namespace Database\Seeders\Demo;

use App\Modules\Finance\Models\Vendor;
use App\Modules\SalesInventory\Models\Customer;
use App\Modules\SalesInventory\Models\Item;
use App\Modules\SalesInventory\Models\ItemCategory;
use App\Modules\SalesInventory\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

/**
 * Seeder DEMO untuk Vendor, Item (physical_good + service), Customer.
 * Stok awal item fisik dibuat lewat InventoryService::recordAdjustment()
 * (bukan raw insert ke stock_levels), konsisten dengan keputusan MVP:
 * restock/stok awal dicatat sebagai manual stock adjustment (PRD.md Section 2.3).
 *
 * CATATAN RISIKO: recordAdjustment() diasumsikan resolve created_by dari
 * Auth::id() secara internal (signature service tidak punya parameter user
 * eksplisit, lihat TASKS.md task 2.12). Karena seeder tidak punya HTTP session,
 * kita login sementara sebagai Super Admin sebelum memanggil service ini,
 * lalu logout setelahnya. Kalau ternyata recordAdjustment() menerima user ID
 * eksplisit di implementasi aktual, baris Auth::loginUsingId()/logout() di
 * bawah bisa dihapus dan diganti parameter langsung.
 */
class DemoVendorItemCustomerSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            abort(403, 'DemoVendorItemCustomerSeeder tidak boleh dijalankan di production.');
        }

        $this->seedVendors();
        $this->seedItemsWithStock();
        $this->seedCustomers();

        $this->command->info('Demo Vendor, Item, Customer selesai.');
    }

    private function seedVendors(): void
    {
        $vendors = [
            ['name' => 'PT Mitra Distributor Teknologi', 'npwp' => '01.234.567.8-901.000', 'email' => 'sales@mitradistek.co.id', 'phone' => '021-5550101'],
            ['name' => 'CV Sumber Jaya Komputer', 'npwp' => '02.345.678.9-012.000', 'email' => 'admin@sumberjayakomputer.com', 'phone' => '021-5550102'],
            ['name' => 'PT Jaringan Nusantara', 'npwp' => '03.456.789.0-123.000', 'email' => 'procurement@jaringannusantara.id', 'phone' => '021-5550103'],
            ['name' => 'Toko Alat Tulis Kantor Makmur', 'npwp' => null, 'email' => 'atkmakmur@gmail.com', 'phone' => '021-5550104'],
            ['name' => 'PT Cloud License Indonesia', 'npwp' => '04.567.890.1-234.000', 'email' => 'billing@cloudlicense.id', 'phone' => '021-5550105'],
        ];

        foreach ($vendors as $vendor) {
            Vendor::firstOrCreate(['name' => $vendor['name']], $vendor);
        }
    }

    private function seedItemsWithStock(): void
    {
        $categories = ItemCategory::pluck('id', 'name');
        $inventoryService = app(InventoryService::class);
        $superAdmin = \App\Models\User::where('email', config('app.super_admin_email'))->first();

        $physicalGoods = [
            ['sku' => 'HW-LAPTOP-001', 'name' => 'Laptop Business ThinkPro 14"', 'category' => 'Hardware Komputer', 'unit_price' => 14500000, 'cost_price' => 12000000, 'initial_stock' => 8],
            ['sku' => 'HW-SERVER-001', 'name' => 'Server Rack 2U Xeon Silver', 'category' => 'Hardware Komputer', 'unit_price' => 65000000, 'cost_price' => 55000000, 'initial_stock' => 2],
            ['sku' => 'HW-MONITOR-001', 'name' => 'Monitor LED 24" IPS', 'category' => 'Hardware Komputer', 'unit_price' => 1800000, 'cost_price' => 1400000, 'initial_stock' => 15],
            ['sku' => 'NW-ROUTER-001', 'name' => 'Router Enterprise Gigabit', 'category' => 'Peralatan Jaringan', 'unit_price' => 4500000, 'cost_price' => 3600000, 'initial_stock' => 6],
            ['sku' => 'NW-SWITCH-001', 'name' => 'Switch Managed 24-Port', 'category' => 'Peralatan Jaringan', 'unit_price' => 3200000, 'cost_price' => 2500000, 'initial_stock' => 5],
            ['sku' => 'NW-UPS-001', 'name' => 'UPS 1000VA Line Interactive', 'category' => 'Peralatan Jaringan', 'unit_price' => 1500000, 'cost_price' => 1150000, 'initial_stock' => 10],
            ['sku' => 'OF-PRINTER-001', 'name' => 'Printer Laser Monokrom A4', 'category' => 'Peralatan Kantor', 'unit_price' => 2800000, 'cost_price' => 2200000, 'initial_stock' => 4],
            // Sengaja stok rendah/habis, untuk uji Stat Card "item stock rendah" (task 4.4) dan filter dropdown M2.
            ['sku' => 'HW-SERVER-002', 'name' => 'Server Rack 1U Entry Level', 'category' => 'Hardware Komputer', 'unit_price' => 38000000, 'cost_price' => 31000000, 'initial_stock' => 0],
        ];

        $services = [
            ['sku' => 'SVC-KONSUL-001', 'name' => 'Jasa Konsultasi Infrastruktur IT', 'category' => 'Jasa Konsultasi', 'unit_price' => 25000000],
            ['sku' => 'SVC-KONSUL-002', 'name' => 'Jasa Konsultasi Keamanan Jaringan', 'category' => 'Jasa Konsultasi', 'unit_price' => 30000000],
            ['sku' => 'SVC-IMPL-001', 'name' => 'Jasa Implementasi Sistem ERP', 'category' => 'Jasa Implementasi & Support', 'unit_price' => 85000000],
            ['sku' => 'SVC-IMPL-002', 'name' => 'Jasa Migrasi Data ke Cloud', 'category' => 'Jasa Implementasi & Support', 'unit_price' => 45000000],
            ['sku' => 'SVC-SUPPORT-001', 'name' => 'Jasa Maintenance & Support Bulanan', 'category' => 'Jasa Implementasi & Support', 'unit_price' => 5000000],
            ['sku' => 'SVC-TRAIN-001', 'name' => 'Jasa Training Karyawan Penggunaan Sistem', 'category' => 'Jasa Konsultasi', 'unit_price' => 12000000],
        ];

        foreach ($physicalGoods as $data) {
            $item = Item::firstOrCreate(
                ['sku' => $data['sku']],
                [
                    'name' => $data['name'],
                    'item_type' => 'physical_good',
                    'item_category_id' => $categories[$data['category']] ?? null,
                    'unit_of_measure' => 'unit',
                    'unit_price' => $data['unit_price'],
                    'cost_price' => $data['cost_price'],
                    'is_active' => true,
                ]
            );

            if ($item->wasRecentlyCreated && $data['initial_stock'] > 0) {
                Auth::loginUsingId($superAdmin->id);
                $inventoryService->recordAdjustment(
                    $item,
                    $data['initial_stock'],
                    'in',
                    'stok_awal_demo',
                    'Stok awal seeding demo data M4'
                );
                Auth::logout();
            }
        }

        foreach ($services as $data) {
            Item::firstOrCreate(
                ['sku' => $data['sku']],
                [
                    'name' => $data['name'],
                    'item_type' => 'service',
                    'item_category_id' => $categories[$data['category']] ?? null,
                    'unit_of_measure' => 'paket',
                    'unit_price' => $data['unit_price'],
                    'cost_price' => null,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedCustomers(): void
    {
        $customers = [
            ['name' => 'PT Maju Bersama Sejahtera', 'customer_type' => 'corporate', 'npwp' => '05.111.222.3-444.000', 'email' => 'purchasing@majubersama.co.id', 'phone' => '021-6660101'],
            ['name' => 'PT Cahaya Retail Indonesia', 'customer_type' => 'corporate', 'npwp' => '06.222.333.4-555.000', 'email' => 'it@cahayaretail.co.id', 'phone' => '021-6660102'],
            ['name' => 'CV Berkah Logistik', 'customer_type' => 'corporate', 'npwp' => '07.333.444.5-666.000', 'email' => 'ops@berkahlogistik.com', 'phone' => '021-6660103'],
            ['name' => 'PT Sinergi Manufaktur', 'customer_type' => 'corporate', 'npwp' => '08.444.555.6-777.000', 'email' => 'admin@sinergimanufaktur.co.id', 'phone' => '021-6660104'],
            ['name' => 'Yayasan Pendidikan Nusantara', 'customer_type' => 'corporate', 'npwp' => null, 'email' => 'sekretariat@yayasannusantara.org', 'phone' => '021-6660105'],
            ['name' => 'PT Griya Sehat Farma', 'customer_type' => 'corporate', 'npwp' => '09.555.666.7-888.000', 'email' => 'procurement@griyasehat.co.id', 'phone' => '021-6660106'],
            ['name' => 'Budi Santoso', 'customer_type' => 'individual', 'npwp' => null, 'email' => 'budi.santoso@gmail.com', 'phone' => '0812-3450001'],
            ['name' => 'Rina Kartika', 'customer_type' => 'individual', 'npwp' => null, 'email' => 'rina.kartika@gmail.com', 'phone' => '0812-3450002'],
            ['name' => 'Ahmad Fauzi', 'customer_type' => 'individual', 'npwp' => '10.666.777.8-999.000', 'email' => 'ahmad.fauzi@gmail.com', 'phone' => '0812-3450003'],
            ['name' => 'PT Konstruksi Baja Perkasa', 'customer_type' => 'corporate', 'npwp' => '11.777.888.9-000.000', 'email' => 'it.support@bajaperkasa.co.id', 'phone' => '021-6660107'],
            ['name' => 'Siti Nurhaliza Wijaya', 'customer_type' => 'individual', 'npwp' => null, 'email' => 'siti.wijaya@gmail.com', 'phone' => '0812-3450004'],
            ['name' => 'PT Agro Teknologi Nusantara', 'customer_type' => 'corporate', 'npwp' => '12.888.999.0-111.000', 'email' => 'digital@agroteknologi.co.id', 'phone' => '021-6660108'],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(['name' => $customer['name']], $customer);
        }
    }
}
