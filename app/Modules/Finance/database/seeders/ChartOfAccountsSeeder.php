<?php

namespace App\Modules\Finance\database\seeders;

use App\Modules\Finance\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Seed data dari DATABASE.md Appendix C.
     * Struktur: code, name, account_type, parent_code, is_postable.
     */
    protected array $accounts = [
        // Aset
        ['code' => '100', 'name' => 'Aktiva Lancar', 'account_type' => 'asset', 'parent_code' => null, 'is_postable' => false],
        ['code' => '101', 'name' => 'Kas', 'account_type' => 'asset', 'parent_code' => '100', 'is_postable' => true],
        ['code' => '102', 'name' => 'Bank', 'account_type' => 'asset', 'parent_code' => '100', 'is_postable' => true],
        ['code' => '103', 'name' => 'Piutang Usaha', 'account_type' => 'asset', 'parent_code' => '100', 'is_postable' => true],
        ['code' => '104', 'name' => 'Penyisihan Piutang Tak Tertagih', 'account_type' => 'asset', 'parent_code' => '100', 'is_postable' => true],
        ['code' => '105', 'name' => 'Persediaan Barang Dagang', 'account_type' => 'asset', 'parent_code' => '100', 'is_postable' => true],
        ['code' => '106', 'name' => 'Perlengkapan Kantor', 'account_type' => 'asset', 'parent_code' => '100', 'is_postable' => true],
        ['code' => '107', 'name' => 'Sewa Dibayar Dimuka', 'account_type' => 'asset', 'parent_code' => '100', 'is_postable' => true],
        ['code' => '108', 'name' => 'Asuransi Dibayar Dimuka', 'account_type' => 'asset', 'parent_code' => '100', 'is_postable' => true],
        ['code' => '109', 'name' => 'PPN Masukan', 'account_type' => 'asset', 'parent_code' => '100', 'is_postable' => true],
        ['code' => '110', 'name' => 'Aktiva Tetap', 'account_type' => 'asset', 'parent_code' => null, 'is_postable' => false],
        ['code' => '111', 'name' => 'Peralatan Kantor', 'account_type' => 'asset', 'parent_code' => '110', 'is_postable' => true],
        ['code' => '112', 'name' => 'Akumulasi Penyusutan Peralatan Kantor', 'account_type' => 'asset', 'parent_code' => '110', 'is_postable' => true],
        ['code' => '113', 'name' => 'Kendaraan', 'account_type' => 'asset', 'parent_code' => '110', 'is_postable' => true],
        ['code' => '114', 'name' => 'Akumulasi Penyusutan Kendaraan', 'account_type' => 'asset', 'parent_code' => '110', 'is_postable' => true],
        ['code' => '115', 'name' => 'Peralatan Komputer & Server', 'account_type' => 'asset', 'parent_code' => '110', 'is_postable' => true],
        ['code' => '116', 'name' => 'Akumulasi Penyusutan Peralatan Komputer & Server', 'account_type' => 'asset', 'parent_code' => '110', 'is_postable' => true],
        ['code' => '120', 'name' => 'Aktiva Tidak Berwujud', 'account_type' => 'asset', 'parent_code' => null, 'is_postable' => false],
        ['code' => '121', 'name' => 'Lisensi Software', 'account_type' => 'asset', 'parent_code' => '120', 'is_postable' => true],
        ['code' => '122', 'name' => 'Akumulasi Amortisasi Lisensi Software', 'account_type' => 'asset', 'parent_code' => '120', 'is_postable' => true],

        // Liabilitas
        ['code' => '200', 'name' => 'Kewajiban Lancar', 'account_type' => 'liability', 'parent_code' => null, 'is_postable' => false],
        ['code' => '201', 'name' => 'Utang Usaha', 'account_type' => 'liability', 'parent_code' => '200', 'is_postable' => true],
        ['code' => '202', 'name' => 'Utang Gaji', 'account_type' => 'liability', 'parent_code' => '200', 'is_postable' => true],
        ['code' => '203', 'name' => 'Utang PPh21', 'account_type' => 'liability', 'parent_code' => '200', 'is_postable' => true],
        ['code' => '204', 'name' => 'Utang BPJS Kesehatan', 'account_type' => 'liability', 'parent_code' => '200', 'is_postable' => true],
        ['code' => '205', 'name' => 'Utang BPJS Ketenagakerjaan', 'account_type' => 'liability', 'parent_code' => '200', 'is_postable' => true],
        ['code' => '206', 'name' => 'PPN Keluaran', 'account_type' => 'liability', 'parent_code' => '200', 'is_postable' => true],
        ['code' => '207', 'name' => 'Pendapatan Diterima Dimuka', 'account_type' => 'liability', 'parent_code' => '200', 'is_postable' => true],
        ['code' => '210', 'name' => 'Kewajiban Jangka Panjang', 'account_type' => 'liability', 'parent_code' => null, 'is_postable' => false],
        ['code' => '211', 'name' => 'Utang Bank Jangka Panjang', 'account_type' => 'liability', 'parent_code' => '210', 'is_postable' => true],

        // Ekuitas
        ['code' => '300', 'name' => 'Ekuitas', 'account_type' => 'equity', 'parent_code' => null, 'is_postable' => false],
        ['code' => '301', 'name' => 'Modal Pemilik', 'account_type' => 'equity', 'parent_code' => '300', 'is_postable' => true],
        ['code' => '302', 'name' => 'Prive', 'account_type' => 'equity', 'parent_code' => '300', 'is_postable' => true],
        ['code' => '303', 'name' => 'Laba Ditahan', 'account_type' => 'equity', 'parent_code' => '300', 'is_postable' => true],

        // Pendapatan
        ['code' => '400', 'name' => 'Pendapatan', 'account_type' => 'revenue', 'parent_code' => null, 'is_postable' => false],
        ['code' => '401', 'name' => 'Pendapatan Jasa Konsultasi IT', 'account_type' => 'revenue', 'parent_code' => '400', 'is_postable' => true],
        ['code' => '402', 'name' => 'Pendapatan Penjualan Barang Teknologi', 'account_type' => 'revenue', 'parent_code' => '400', 'is_postable' => true],
        ['code' => '403', 'name' => 'Pendapatan Lain-lain', 'account_type' => 'revenue', 'parent_code' => '400', 'is_postable' => true],

        // Beban
        ['code' => '500', 'name' => 'Beban Pokok Penjualan', 'account_type' => 'expense', 'parent_code' => null, 'is_postable' => false],
        ['code' => '501', 'name' => 'Harga Pokok Penjualan Barang', 'account_type' => 'expense', 'parent_code' => '500', 'is_postable' => true],
        ['code' => '510', 'name' => 'Beban Operasional', 'account_type' => 'expense', 'parent_code' => null, 'is_postable' => false],
        ['code' => '511', 'name' => 'Beban Gaji dan Tunjangan', 'account_type' => 'expense', 'parent_code' => '510', 'is_postable' => true],
        ['code' => '512', 'name' => 'Beban BPJS Kesehatan (Perusahaan)', 'account_type' => 'expense', 'parent_code' => '510', 'is_postable' => true],
        ['code' => '513', 'name' => 'Beban BPJS Ketenagakerjaan (Perusahaan)', 'account_type' => 'expense', 'parent_code' => '510', 'is_postable' => true],
        ['code' => '514', 'name' => 'Beban Sewa Kantor', 'account_type' => 'expense', 'parent_code' => '510', 'is_postable' => true],
        ['code' => '515', 'name' => 'Beban Listrik, Air, dan Internet', 'account_type' => 'expense', 'parent_code' => '510', 'is_postable' => true],
        ['code' => '516', 'name' => 'Beban Perlengkapan Kantor', 'account_type' => 'expense', 'parent_code' => '510', 'is_postable' => true],
        ['code' => '517', 'name' => 'Beban Penyusutan Peralatan Kantor', 'account_type' => 'expense', 'parent_code' => '510', 'is_postable' => true],
        ['code' => '518', 'name' => 'Beban Penyusutan Kendaraan', 'account_type' => 'expense', 'parent_code' => '510', 'is_postable' => true],
        ['code' => '519', 'name' => 'Beban Penyusutan Peralatan Komputer & Server', 'account_type' => 'expense', 'parent_code' => '510', 'is_postable' => true],
        ['code' => '520', 'name' => 'Beban Pemasaran', 'account_type' => 'expense', 'parent_code' => '510', 'is_postable' => true],
        ['code' => '521', 'name' => 'Beban Perjalanan Dinas', 'account_type' => 'expense', 'parent_code' => '510', 'is_postable' => true],
        ['code' => '522', 'name' => 'Beban Lain-lain Operasional', 'account_type' => 'expense', 'parent_code' => '510', 'is_postable' => true],
        ['code' => '530', 'name' => 'Beban Non-Operasional', 'account_type' => 'expense', 'parent_code' => null, 'is_postable' => false],
        ['code' => '531', 'name' => 'Beban Bunga', 'account_type' => 'expense', 'parent_code' => '530', 'is_postable' => true],
        ['code' => '532', 'name' => 'Beban Administrasi Bank', 'account_type' => 'expense', 'parent_code' => '530', 'is_postable' => true],
    ];

    public function run(): void
    {
        // Pass 1: header/group account dulu (parent_code null, is_postable false)
        $codeToId = [];

        foreach ($this->accounts as $account) {
            if ($account['parent_code'] === null) {
                $created = ChartOfAccount::create([
                    'code' => $account['code'],
                    'name' => $account['name'],
                    'account_type' => $account['account_type'],
                    'parent_id' => null,
                    'is_postable' => $account['is_postable'],
                    'is_active' => true,
                ]);

                $codeToId[$account['code']] = $created->id;
            }
        }

        // Pass 2: leaf account, resolve parent_id dari code
        foreach ($this->accounts as $account) {
            if ($account['parent_code'] !== null) {
                ChartOfAccount::create([
                    'code' => $account['code'],
                    'name' => $account['name'],
                    'account_type' => $account['account_type'],
                    'parent_id' => $codeToId[$account['parent_code']] ?? null,
                    'is_postable' => $account['is_postable'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
