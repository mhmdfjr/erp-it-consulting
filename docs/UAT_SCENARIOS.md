# UAT Scenarios

## Sistem ERP - Perusahaan IT Service & Consulting

Status: Draft v1.0
Terakhir diperbarui: 2026-08-16
Terkait: TASKS.md task 4.14-4.16, ROADMAP.md Section 7 (M4)

---

## 1. Cara Pakai Dokumen Ini

Setiap skenario punya langkah konkret dan hasil yang diharapkan (expected result). Jalankan berurutan dalam satu module dulu sampai selesai, baru pindah ke module lain — beberapa skenario di module berikutnya mengasumsikan state dari skenario sebelumnya (misal skenario Sales harus selesai dulu sebelum skenario Finance yang membaca invoice hasilnya).

Centang `[ ]` jadi `[x]` setelah lolos. Kalau ada yang gagal, catat di kolom "Catatan" (tambahkan manual), jangan lanjut ke skenario yang bergantung padanya sampai gap-nya jelas apakah itu bug atau kesalahan langkah testing.

Skenario ini dirancang bisa dijalankan berulang kali dari state data demo yang sama (`DemoDataSeeder`), tapi beberapa skenario **mengubah data** (create, complete, cancel, void, pay) — kalau ingin mengulang UAT dari nol, jalankan ulang `php artisan migrate:fresh --seed` lalu `php artisan db:seed --class="Database\Seeders\Demo\DemoDataSeeder"` sebelum mulai.

## 2. Kredensial Testing

Password seluruh user demo: `password`

| Email | Role | Dipakai untuk skenario |
|---|---|---|
| `superadmin@test.local` | Super Admin | Semua module + Security |
| `dewi.sales@test.local` / `rian.sales@test.local` | Sales Staff | Module Sales & Inventory |
| `fajar.finance@test.local` / `nina.finance@test.local` | Finance Staff | Module Finance (tanpa void) |
| `budi.financemanager@test.local` | Finance Manager | Module Finance (termasuk void) |
| `sari.hr@test.local` / `agus.hr@test.local` | HR Staff | Module HR (tanpa proses payroll) |
| `lina.hrmanager@test.local` | HR Manager | Module HR (termasuk proses payroll) |
| `eko.resigned@test.local` | HR Staff, `is_active = false` | Security: user nonaktif tidak bisa login |

---

## 3. Module: Identity & Access Management

### UAT-ID-01 — Login dan RBAC dasar

**Role**: Super Admin

1. Login sebagai `superadmin@test.local`.
2. Buka menu User, verifikasi seluruh 22 user demo (13 employee, 9 role staff/manager, tapi employee tidak semua punya login — cek jumlah row sesuai `users` table) muncul di list.
3. Buka menu Role & Permission, verifikasi role `Sales Staff`, `Finance Staff`, `Finance Manager`, `HR Staff`, `HR Manager` muncul selain `Super Admin`.

**Expected**: Seluruh data ter-render tanpa error, tidak ada 403.

- [ ] Lolos

### UAT-ID-02 — User nonaktif tidak bisa masuk

**Role**: tidak login (mencoba sebagai `eko.resigned@test.local`)

1. Logout dari sesi manapun.
2. Coba login dengan `eko.resigned@test.local` / `password`.

**Expected**: Login ditolak atau user langsung di-redirect keluar (`is_active = false` harus dicek di flow auth). Kalau sistem **tidak** menolak, ini gap yang perlu dilaporkan — cek apakah guard `is_active` memang diimplementasikan di M0 atau cuma disimpan di kolom tanpa dipakai.

- [ ] Lolos

### UAT-ID-03 — Role terbatas tidak bisa akses menu di luar scope

**Role**: Sales Staff (`dewi.sales@test.local`)

1. Login sebagai Sales Staff.
2. Coba akses langsung `/finance/journal-entries` lewat URL bar.
3. Coba akses `/hr/employees`.

**Expected**: Kedua akses ditolak (403), menu Finance/HR juga tidak muncul di sidebar untuk role ini.

- [ ] Lolos

---

## 4. Module: Sales & Inventory

### UAT-SI-01 — Buat Sales Order campuran barang + jasa

**Role**: Sales Staff (`dewi.sales@test.local`)

1. Buka menu Sales Order, klik "+ Buat Sales Order".
2. Pilih customer **PT Maju Bersama Sejahtera** (atau customer lain yang belum punya order aktif).
3. Tambah baris item: **Laptop Business ThinkPro 14"** (`HW-LAPTOP-001`) qty 1, lalu **Jasa Konsultasi Infrastruktur IT** (`SVC-KONSUL-001`) qty 1.
4. Simpan sebagai draft.

**Expected**: Order tersimpan status `draft`, subtotal per baris dan total otomatis terhitung, tidak ada error dari `wire:key` dynamic row (SESSION_SUMMARY_M1 poin 3).

- [ ] Lolos

### UAT-SI-02 — Item stok habis tidak muncul di dropdown

**Role**: Sales Staff

1. Buka form create Sales Order baru.
2. Cari **Server Rack 1U Entry Level** (`HW-SERVER-002`, stok demo sengaja 0) di dropdown pemilihan item.

**Expected**: Item ini **tidak muncul** sebagai opsi (PRD.md Section 4.4 — item fisik stok habis tidak boleh jadi opsi).

- [ ] Lolos

### UAT-SI-03 — Complete Order, verifikasi stok dan invoice sync

**Role**: Sales Staff

1. Buka Sales Order draft hasil UAT-SI-01, klik "Complete Order".
2. Setelah redirect, cek halaman detail order: status harus `completed`, invoice harus **langsung muncul** (tanpa reload manual atau delay) dengan `invoice_number` format `INV-2026-xxxxxx`.
3. Buka menu Product & Service, cek stok **HW-LAPTOP-001** berkurang 1 dari sebelumnya.

**Expected**: Invoice muncul sync tanpa jeda (DATABASE.md ASUMSI 7). Stok fisik berkurang, bukan cuma reserved.

- [ ] Lolos

### UAT-SI-04 — Cancel Order melepas reservasi

**Role**: Sales Staff

1. Buat Sales Order baru (draft) untuk customer **Rina Kartika**, item **Monitor LED 24" IPS** (`HW-MONITOR-001`) qty 2.
2. Catat available stock item ini sebelum cancel (lihat kolom Available di halaman Item).
3. Batalkan order, isi alasan pembatalan (wajib).
4. Cek kembali available stock **HW-MONITOR-001**.

**Expected**: Order berstatus `cancelled` dengan alasan tersimpan. Available stock kembali ke angka semula (reservasi dilepas, bukan nyangkut permanen — ini regresi test untuk bug M2 yang pernah nyata terjadi).

- [ ] Lolos

### UAT-SI-05 — Cancel ditolak untuk order completed

**Role**: Sales Staff

1. Buka Sales Order yang sudah `completed` (hasil UAT-SI-03).
2. Cari tombol "Cancel Order".

**Expected**: Tombol Cancel **tidak muncul** untuk order berstatus `completed` (DATABASE.md ASUMSI 8 — cancel cuma valid dari `draft`).

- [ ] Lolos

### UAT-SI-06 — Stock adjustment manual

**Role**: Sales Staff (butuh permission `sales.inventory.adjust`, cek apakah Sales Staff demo punya ini — kalau tidak, uji dengan Super Admin)

1. Buka detail item **Router Enterprise Gigabit** (`NW-ROUTER-001`).
2. Buat stock adjustment `in` sebanyak 5 unit, coba submit **tanpa** mengisi reason code.

**Expected**: Ditolak dengan pesan reason code wajib diisi (DATABASE.md Assumption 3). Isi reason code, submit ulang — berhasil, stok bertambah 5.

- [ ] Lolos

---

## 5. Module: Finance & Accounting

### UAT-FN-01 — Manual Journal Entry harus balance

**Role**: Finance Staff (`fajar.finance@test.local`)

1. Buka menu Journal Entry, klik "+ Buat Entri Jurnal".
2. Tambah baris: debit akun 106 (Perlengkapan Kantor) Rp 500.000, kredit akun 101 (Kas) Rp 400.000 (sengaja tidak balance).
3. Submit.

**Expected**: Ditolak dengan pesan tidak balance, entry tidak tersimpan.

4. Perbaiki kredit jadi Rp 500.000, submit ulang.

**Expected**: Berhasil, `entry_number` format `JE-2026-xxxxxx` muncul.

- [ ] Lolos

### UAT-FN-02 — Void Journal Entry butuh permission lebih tinggi

**Role**: Finance Staff (`fajar.finance@test.local`), lalu Finance Manager (`budi.financemanager@test.local`)

1. Login sebagai Finance Staff, buka salah satu journal entry hasil Sales Order (status `posted`).
2. Cari tombol "Void Entry".

**Expected**: Tombol **tidak muncul** untuk Finance Staff (tidak punya `finance.journal.void`).

3. Logout, login sebagai Finance Manager, buka entry yang sama.
4. Klik "Void Entry", isi alasan, submit.

**Expected**: Berhasil, status berubah jadi `void`, `void_reason` tersimpan, nilai debit/credit baris tidak berubah (immutable, ARCHITECTURE.md Section 5b).

- [ ] Lolos

### UAT-FN-03 — Record Payment dengan validasi anti-overpayment

**Role**: Finance Staff

1. Buka invoice yang masih `unpaid` (misal invoice untuk **PT Konstruksi Baja Perkasa**).
2. Coba input payment dengan `amount` **lebih besar** dari sisa tagihan invoice.

**Expected**: Ditolak dengan pesan "melebihi sisa tagihan" (`StorePaymentRequest`).

3. Input payment dengan `amount` sesuai sisa tagihan penuh.

**Expected**: Berhasil, invoice status berubah jadi `paid`, `paid_at` terisi, journal entry pelunasan muncul (debit 101/102 sesuai `payment_method`, kredit 103).

- [ ] Lolos

### UAT-FN-04 — Vendor Bill accrual dan pelunasan

**Role**: Finance Staff

1. Buat Vendor baru atau pakai yang sudah ada (**PT Mitra Distributor Teknologi**).
2. Buat Vendor Bill: pilih akun beban (misal 514 Beban Sewa Kantor), amount Rp 5.000.000.

**Expected**: Bill tersimpan status `unpaid`, journal entry accrual otomatis muncul (debit akun beban, kredit 201 Utang Usaha).

3. Klik "Mark as Paid" pada bill tersebut.

**Expected**: Status jadi `paid`, journal entry kedua muncul (debit 201, kredit 101/102).

- [ ] Lolos

### UAT-FN-05 — Laporan Laba Rugi dan Neraca

**Role**: Finance Staff atau Manager

1. Buka Laporan Laba Rugi, pilih bulan **Juli 2026** (bulan payroll demo diproses).

**Expected**: Muncul baris beban 511/512/513, tanpa pendapatan (Sales Order demo semua `entry_date` Agustus).

2. Ganti ke **Agustus 2026**.

**Expected**: Muncul pendapatan 401/402 dan HPP 501, laba/rugi bersih terhitung otomatis.

3. Buka Laporan Neraca per hari ini.

**Expected**: **Tidak ada** peringatan "tidak balance" berwarna merah. Kalau muncul, ini bug nyata yang harus dilaporkan sebelum lanjut ke UAT lain — jangan diabaikan.

- [ ] Lolos

---

## 6. Module: HR & Payroll

### UAT_HR-01 — CRUD Employee dengan PTKP status

**Role**: HR Staff (`sari.hr@test.local`)

1. Buat Employee baru, isi seluruh field wajib termasuk `ptkp_status` (pilih salah satu dari dropdown TK0-K3).
2. Simpan.

**Expected**: Employee tersimpan, muncul di list dengan status `active`.

- [ ] Lolos

### UAT-HR-02 — Attendance completeness warning

**Role**: HR Manager (`lina.hrmanager@test.local`)

1. Buka menu Payroll Run, buat Payroll Period baru untuk bulan berjalan (bulan yang attendance-nya **belum** diisi lengkap untuk seluruh employee aktif).
2. Klik "Process Payroll".

**Expected**: Muncul warning attendance belum lengkap, menyebutkan jumlah employee yang datanya kurang, dengan opsi eksplisit untuk tetap lanjut (bukan block keras, bukan auto-lanjut diam-diam).

- [ ] Lolos

### UAT-HR-03 — Verifikasi prorate untuk employee dengan hari absent

**Role**: HR Manager

1. Buka slip gaji employee **Bella Putri** (`EMP-0002`) untuk periode Juli 2026.
2. Bandingkan `base_salary` di slip dengan `base_salary` kontraktual di data Employee (Rp 11.000.000).

**Expected**: Nilai di slip **lebih kecil** dari kontraktual, ada keterangan jumlah hari kerja dan hari absent yang jadi dasar prorate. Tunjangan (kalau ada) tetap ditampilkan flat, tidak ikut terpotong.

- [ ] Lolos

### UAT-HR-04 — Mark as Paid tidak membuat jurnal tambahan

**Role**: HR Manager

1. Catat jumlah `journal_entries` saat ini (lihat menu Journal Entry, catat total baris atau ID terakhir).
2. Buka Payroll Run periode Juli 2026, cari payroll run yang masih berstatus `finalized` (belum `paid`).
3. Klik "Mark as Paid".
4. Cek ulang menu Journal Entry.

**Expected**: Status payroll run berubah jadi `paid`, **tidak ada** journal entry baru yang muncul (ARCHITECTURE.md Section 4 — Mark as Paid tidak generate jurnal tambahan, mirror pola Vendor Bill).

- [ ] Lolos

### UAT-HR-05 — Journal entry payroll: alokasi BPJS employee+company

**Role**: Finance Staff atau Manager

1. Buka journal entry dengan `reference_type = PayrollPeriod` untuk periode Juli 2026.
2. Cek baris kredit akun 204 (Utang BPJS Kesehatan).
3. Bandingkan manual: baris ini harus **lebih besar** dari total `bpjs_kesehatan_deduction` seluruh payroll run periode itu (karena harus termasuk company portion, bukan cuma potongan employee).

**Expected**: Nilai kredit 204 = employee portion + company portion. Kalau ternyata sama persis dengan total employee portion saja, itu bug alokasi yang harus dilaporkan (kelas bug yang eksplisit ditandai ARCHITECTURE.md sebagai risiko M3).

- [ ] Lolos

---

## 7. Cross-Module: Dashboard dan Visibility

### UAT-DB-01 — Dashboard menampilkan chart sesuai role

**Role**: bergantian Sales Staff, Finance Staff, HR Staff, Super Admin

1. Login sebagai Sales Staff, buka Dashboard. Catat chart apa saja yang muncul.

**Expected**: Hanya chart Sales (tren penjualan, item terlaris, distribusi status order) yang muncul. Tidak ada chart Finance atau HR.

2. Ulangi untuk Finance Staff dan HR Staff, masing-masing harus cuma lihat chart domain sendiri.

3. Login sebagai Super Admin.

**Expected**: Seluruh chart (Sales + Finance + HR) muncul sekaligus.

- [ ] Lolos

### UAT-DB-02 — Stat Card stock rendah termasuk item stok nol

**Role**: siapapun yang punya akses Dashboard

1. Buka Dashboard, cek tabel "Detail Item Stock Rendah".
2. Pastikan **Server Rack 1U Entry Level** (`HW-SERVER-002`, stok 0) muncul di daftar dengan badge "Habis".

**Expected**: Item ini muncul (bug sebelumnya membuat item stok 0 hilang dari daftar karena tidak pernah punya row `stock_levels` — sudah di-fix, ini regresi test).

- [ ] Lolos

---

## 8. Ringkasan Hasil UAT

Isi setelah seluruh skenario dijalankan:

| Module | Total Skenario | Lolos | Gagal | Catatan |
|---|---|---|---|---|
| Identity | 3 | | | |
| Sales & Inventory | 6 | | | |
| Finance & Accounting | 5 | | | |
| HR & Payroll | 5 | | | |
| Dashboard | 2 | | | |
| **Total** | **21** | | | |

Setiap kegagalan dicatat di sini, lalu diklasifikasikan sesuai TASKS.md task 4.16: **blocking** (harus di-fix sebelum go-live) atau **fase 2** (bisa ditunda). Jangan lanjut ke Go-Live Prep (task 4.17-4.20) kalau masih ada kegagalan blocking yang belum di-fix.

---

Dokumen terkait: `TASKS.md`, `PRD.md`, `ARCHITECTURE.md`, `ROADMAP.md`.
