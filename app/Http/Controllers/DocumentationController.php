<?php
// app/Http/Controllers/DocumentationController.php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * Dokumentasi in-app untuk end-user (staff Sales/Finance/HR/Super Admin),
 * beda dari README.md yang ditujukan untuk developer. Konten difilter
 * berdasarkan permission user, pola sama persis dengan filtering sidebar
 * (app/Http/Controllers/... atau resources/views/components/sidebar.blade.php)
 * supaya section yang ditampilkan konsisten dengan menu yang bisa diakses.
 */
class DocumentationController extends Controller
{
    public function index(): View
    {
        /** @var User|null $user */
        $user = Auth::user();

        $sections = collect($this->allSections())
            ->filter(fn ($section) => is_null($section['permission']) || ($user && $user->can($section['permission'])))
            ->values();

        return view('documentation', compact('sections'));
    }

    private function allSections(): array
    {
        return [
            [
                'permission' => null,
                'icon' => 'layout-dashboard',
                'title' => 'Dashboard',
                'items' => [
                    [
                        'question' => 'Apa saja yang ditampilkan di Dashboard?',
                        'answer' => 'Dashboard menampilkan ringkasan angka penting (revenue bulan berjalan, outstanding invoice, jumlah employee aktif, item dengan stock rendah) di bagian atas, diikuti grafik yang relevan dengan peran Anda.',
                    ],
                    [
                        'question' => 'Kenapa saya tidak melihat grafik tertentu?',
                        'answer' => 'Grafik yang tampil menyesuaikan hak akses Anda. Staff Sales hanya melihat grafik penjualan, staff Finance melihat grafik keuangan, staff HR melihat grafik kepegawaian. Super Admin dan Admin melihat seluruhnya.',
                    ],
                ],
            ],
            [
                'permission' => 'sales.order.view',
                'icon' => 'shopping-cart',
                'title' => 'Sales Order',
                'items' => [
                    [
                        'question' => 'Bagaimana alur satu Sales Order dari awal sampai selesai?',
                        'answer' => 'Order dibuat dengan status Draft, bisa berisi campuran barang fisik dan jasa. Selama masih Draft, order bisa dibatalkan. Setelah diklik "Complete Order", status berubah jadi Completed, stok barang fisik berkurang otomatis, dan Invoice terbit otomatis tanpa perlu langkah tambahan.',
                    ],
                    [
                        'question' => 'Kenapa item tertentu tidak muncul di pilihan saat membuat order?',
                        'answer' => 'Item barang fisik yang stoknya sudah habis (available stock 0) tidak akan muncul sebagai pilihan, untuk mencegah order dibuat untuk barang yang jelas tidak tersedia. Item jasa selalu muncul karena tidak terikat stok.',
                    ],
                    [
                        'question' => 'Bisakah Sales Order yang sudah Completed dibatalkan?',
                        'answer' => 'Tidak. Pembatalan hanya berlaku untuk order berstatus Draft, sebelum stok direalisasi dan invoice terbit. Order yang sudah selesai dan perlu dikoreksi (misalnya barang dikembalikan) perlu ditangani lewat proses terpisah, hubungi Finance.',
                    ],
                ],
            ],
            [
                'permission' => 'sales.inventory.view',
                'icon' => 'package',
                'title' => 'Product & Inventory',
                'items' => [
                    [
                        'question' => 'Bagaimana cara menambah stok barang?',
                        'answer' => 'Melalui menu Product & Service, buka detail item, gunakan form "Stock Adjustment". Alasan penyesuaian stok wajib diisi untuk setiap perubahan, sebagai jejak audit kenapa stok berubah di luar transaksi penjualan.',
                    ],
                ],
            ],
            [
                'permission' => 'finance.journal.view',
                'icon' => 'book-open-text',
                'title' => 'Journal Entry',
                'items' => [
                    [
                        'question' => 'Apakah journal entry bisa diedit setelah dibuat?',
                        'answer' => 'Tidak. Journal entry yang sudah berstatus Posted bersifat permanen untuk menjaga integritas pembukuan. Kesalahan dikoreksi lewat aksi "Void", yang mengubah status jadi Void dan mewajibkan alasan diisi, bukan menghapus atau mengubah angkanya.',
                    ],
                    [
                        'question' => 'Siapa yang bisa melakukan void journal entry?',
                        'answer' => 'Hanya user dengan hak khusus (biasanya Finance Manager) yang bisa void. Finance Staff biasa bisa melihat dan membuat journal entry, tapi tidak bisa membatalkannya.',
                    ],
                    [
                        'question' => 'Kenapa ada journal entry yang muncul otomatis tanpa saya buat manual?',
                        'answer' => 'Sebagian besar journal entry dibuat otomatis oleh sistem: saat Sales Order selesai (jurnal pendapatan), saat payroll diproses (jurnal beban gaji), dan saat pembayaran invoice dicatat (jurnal pelunasan piutang). Jurnal manual biasanya hanya diperlukan untuk transaksi yang tidak tercakup alur otomatis ini.',
                    ],
                ],
            ],
            [
                'permission' => 'finance.invoice.view',
                'icon' => 'file-text',
                'title' => 'Invoice & Pembayaran',
                'items' => [
                    [
                        'question' => 'Bagaimana cara mencatat pembayaran dari customer?',
                        'answer' => 'Buka detail invoice yang berstatus Unpaid, isi form "Catat Penerimaan Kas" dengan tanggal, nominal, dan metode pembayaran. Jumlah pembayaran tidak boleh melebihi sisa tagihan. Satu invoice bisa menerima beberapa kali pembayaran (cicilan) sampai lunas.',
                    ],
                    [
                        'question' => 'Kapan status invoice berubah jadi Paid?',
                        'answer' => 'Otomatis begitu total seluruh pembayaran yang tercatat mencapai atau melebihi jumlah tagihan invoice. Anda tidak perlu mengubah status secara manual.',
                    ],
                ],
            ],
            [
                'permission' => 'finance.report.view',
                'icon' => 'trending-up',
                'title' => 'Laporan Keuangan',
                'items' => [
                    [
                        'question' => 'Apa perbedaan Laporan Laba Rugi dan Neraca?',
                        'answer' => 'Laba Rugi menunjukkan pendapatan dan beban untuk satu periode (misalnya satu bulan tertentu). Neraca menunjukkan posisi keuangan (aset, liabilitas, ekuitas) pada satu titik waktu tertentu, terlepas dari periode.',
                    ],
                    [
                        'question' => 'Apa arti peringatan "tidak balance" di halaman Neraca?',
                        'answer' => 'Peringatan ini muncul kalau total Aset tidak sama dengan total Liabilitas ditambah Ekuitas, yang menandakan kemungkinan ada data tidak konsisten. Kalau peringatan ini muncul, segera laporkan ke tim teknis, jangan diabaikan.',
                    ],
                ],
            ],
            [
                'permission' => 'hr.employee.view',
                'icon' => 'user-shield',
                'title' => 'Employee & Attendance',
                'items' => [
                    [
                        'question' => 'Apa itu status PTKP dan kenapa harus diisi saat tambah Employee?',
                        'answer' => 'PTKP (Penghasilan Tidak Kena Pajak) menentukan kategori tarif pajak (PPh21) yang berlaku untuk employee tersebut. Setiap employee wajib punya status ini karena langsung memengaruhi perhitungan potongan gaji bulanan.',
                    ],
                    [
                        'question' => 'Apa bedanya status kehadiran Absent, Leave, dan Sick untuk perhitungan gaji?',
                        'answer' => 'Hanya status Absent (tanpa keterangan) yang memotong gaji secara proporsional. Leave (cuti) dan Sick (sakit) dihitung penuh seperti hadir normal, tidak mengurangi gaji.',
                    ],
                ],
            ],
            [
                'permission' => 'hr.payroll.view',
                'icon' => 'circle-dollar-sign',
                'title' => 'Payroll',
                'items' => [
                    [
                        'question' => 'Apa yang terjadi kalau data kehadiran belum lengkap saat memproses payroll?',
                        'answer' => 'Sistem akan menampilkan peringatan yang menyebutkan employee mana saja yang datanya belum lengkap. Anda tetap bisa melanjutkan proses; hari yang tidak punya catatan kehadiran akan dianggap hadir penuh (tidak memotong gaji).',
                    ],
                    [
                        'question' => 'Apa fungsi tombol "Mark as Paid" pada payroll run?',
                        'answer' => 'Tombol ini menandai bahwa gaji sudah benar-benar ditransfer ke rekening employee. Ini murni penanda status administratif dan tidak membuat jurnal akuntansi baru — jurnal beban gaji sudah tercatat otomatis sejak payroll diproses.',
                    ],
                    [
                        'question' => 'Kenapa base salary di slip gaji employee tertentu lebih kecil dari kontraknya?',
                        'answer' => 'Ini terjadi kalau employee tersebut punya catatan Absent (tanpa keterangan) di periode tersebut. Slip gaji akan menampilkan rincian jumlah hari kerja dan hari absen yang jadi dasar perhitungan potongan proporsional.',
                    ],
                ],
            ],
            [
                'permission' => 'identity.user.view',
                'icon' => 'users',
                'title' => 'User & Role Management',
                'items' => [
                    [
                        'question' => 'Apa perbedaan menonaktifkan user dan menghapus user?',
                        'answer' => 'Sistem ini tidak mendukung hapus user secara permanen, hanya menonaktifkan (toggle status Aktif/Nonaktif). Ini disengaja untuk menjaga riwayat transaksi yang terkait dengan user tersebut tetap utuh. User yang dinonaktifkan tidak bisa login lagi.',
                    ],
                    [
                        'question' => 'Bagaimana cara memberi akses terbatas ke seorang staff?',
                        'answer' => 'Buat atau pilih Role dengan kombinasi permission yang sesuai kebutuhan staff tersebut lewat menu Role & Permission, lalu assign role itu ke user yang bersangkutan lewat menu User.',
                    ],
                ],
            ],
        ];
    }
}
