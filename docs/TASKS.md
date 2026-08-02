# Tasks

## Sistem ERP - Perusahaan IT Service & Consulting

Status: Draft v1.0
Terakhir diperbarui: 2026-07-30

---

## Cara Pakai Dokumen Ini

Ini checklist kerja, bukan dokumen naratif. Kerjakan dari atas ke bawah secara berurutan dalam satu milestone, jangan lompat ke task berikutnya sebelum task sebelumnya selesai, kecuali ditandai eksplisit "bisa paralel". Urutan task di dalam satu milestone mengikuti urutan pembangunan yang wajar: migration dulu, baru model, baru service (business logic), baru UI (Controller/Livewire/view), baru test.

Checklist pakai format `- [ ]`. Centang manual jadi `- [x]` di editor kamu setelah selesai, atau minta saya update kalau kerja bareng saya di sesi berikutnya.

Setiap task ditulis cukup spesifik untuk langsung dikerjakan, dengan referensi ke file/tabel/section dokumen lain (DATABASE.md, ARCHITECTURE.md, DESIGN.md) kalau butuh detail lebih lanjut. Kalau ada task yang buat kamu bingung harus mulai dari mana, itu sinyal untuk tanya saya dulu sebelum coding, bukan tanya di tengah-tengah nulis kode dan berharap benar.

Dua item sebelumnya ditandai BLOCKER dan sekarang sudah terisi seed data-nya, tinggal dua verifikasi kecil yang tersisa (lihat section "Item yang Masih Perlu Diverifikasi" di bawah), tidak lagi menghalangi mulai coding dari M0.

---

Dua item sebelumnya ditandai **BLOCKER**, sekarang sudah terisi seed data-nya (lihat DATABASE.md Appendix B dan C), jadi tidak lagi menghalangi mulai coding. Yang tersisa cuma dua angka kecil yang masih perlu diverifikasi resmi, ditandai di bawah, bukan lagi blocker penuh.

---

## Item yang Masih Perlu Diverifikasi (Bukan Lagi Blocker Penuh)

- [ ] **VERIFIKASI 1**: Struktur Chart of Accounts di DATABASE.md Appendix C adalah seed data awal yang wajar untuk mulai development (diadaptasi dari referensi CoA perusahaan jasa), tapi bukan hasil audit akuntan. Review dengan pihak yang paham akuntansi perusahaan ini sebelum go-live produksi, tidak menghalangi mulai coding sekarang.
- [ ] **VERIFIKASI 2**: Rate BPJS Kesehatan, JHT, JP, JKM sudah terisi di DATABASE.md Appendix B dari sumber yang kamu kasih. Dua angka spesifik masih perlu dikonfirmasi resmi ke BPJS Ketenagakerjaan sebelum M3 dianggap selesai: (a) kelas risiko JKK resmi untuk perusahaan ini (dipakai asumsi kelas terendah 0.24% sementara), (b) batas atas upah (wage cap) untuk JP tahun 2026 kalau memang ada.

---

## M0 - Project Setup + Identity & Access Management

### Setup Awal

- [x] 0.1 Buat project baru: `composer create-project laravel/laravel erp-app`, inisialisasi git repository.
- [x] 0.2 Konfigurasi `.env` untuk koneksi PostgreSQL (`DB_CONNECTION=pgsql`), buat database lokal, jalankan `php artisan migrate` default untuk pastikan koneksi jalan.
- [x] 0.3 Install starter kit auth berbasis Blade/session, bukan API (Laravel Breeze varian Blade, `php artisan breeze:install blade`), sesuai keputusan no-API di ARCHITECTURE.md Section 7.
- [x] 0.4 Install Livewire (`composer require livewire/livewire`), dipakai untuk komponen interaktif sesuai DESIGN.md dan ARCHITECTURE.md Section 6.
- [x] 0.5 Install `spatie/laravel-permission`, publish migration dan config-nya (`php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`).

### Struktur Modular Monolith

- [x] 0.6 Buat struktur folder dasar sesuai ARCHITECTURE.md Section 3: `app/Modules/Identity/{Http/Controllers,Livewire,Models,Services,Policies,Providers,routes,database/migrations,resources/views}`.
- [x] 0.7 Buat `IdentityServiceProvider`, register di `bootstrap/providers.php`, isi `boot()` untuk load routes (`loadRoutesFrom`), views (`loadViewsFrom`), dan migrations (`loadMigrationsFrom`) dari folder module Identity.
- [x] 0.8 Verifikasi struktur ini jalan: buat satu migration dummy di folder module, pastikan `php artisan migrate` mendeteksinya dari path module, bukan dari `database/migrations` default.

### Migration dan Model - Master Data

- [x] 0.9 Migration `company_profile` sesuai DATABASE.md Section 1.3.
- [x] 0.10 Migration `system_settings` sesuai DATABASE.md Section 1.4.
- [x] 0.11 Migration `audit_logs` sesuai DATABASE.md Section 1.5, termasuk composite index `(auditable_type, auditable_id)`.
- [x] 0.12 Model `CompanyProfile`, `SystemSetting`, `AuditLog` di `app/Modules/Identity/Models`.
- [x] 0.13 Jalankan migration, verifikasi tabel terbentuk dengan benar di PostgreSQL (`\d table_name` lewat `psql`).

### RBAC

- [x] 0.14 Tambahkan trait `HasRoles` dari Spatie ke Model `User`.
- [x] 0.15 Buat Seeder `RolePermissionSeeder`: definisikan permission dasar per module yang akan dibangun (`identity.manage`, `hr.manage`, `finance.manage`, `sales.manage`, dst, granularitas bisa disesuaikan nanti per fitur konkret saat module-nya dibangun).
- [x] 0.16 Buat role `Super Admin` di seeder yang sama, assign seluruh permission yang ada lewat `syncPermissions()`.
- [x] 0.17 Jalankan seeder, buat satu user test, assign role Super Admin, verifikasi lewat tinker (`$user->can('identity.manage')`) return `true`.

### Audit Log Infrastructure

- [x] 0.18 Buat trait `Auditable` (di `app/Modules/Identity/Support` atau lokasi shared) yang di-attach ke Model manapun yang butuh audit trail.
- [x] 0.19 Implementasi trait ini pakai Eloquent Model Observer (`created`, `updated`, `deleted` events) yang otomatis insert ke `audit_logs` dengan `old_values`/`new_values` dari `getDirty()`/`getOriginal()`.
- [x] 0.20 Test manual: attach trait ke Model `User` sementara, update satu field, verifikasi row baru muncul di `audit_logs`.

### UI Foundation

- [x] 0.21 Setup Tailwind config atau CSS custom properties sesuai DESIGN.md Quick Start (token warna, spacing, radius, shadow).
- [x] 0.22 Buat Blade component layout dasar: `<x-app-layout>` dengan sidebar (240px, sesuai DESIGN.md Sidebar Navigation) dan top bar (56px, sesuai DESIGN.md Top Bar).
- [x] 0.23 Buat komponen dasar reusable sesuai DESIGN.md Components: `<x-button variant="primary|secondary|danger">`, `<x-input>`, `<x-badge status="success|warning|danger|info">`, `<x-data-table>` shell (header, empty state, pagination slot).
- [x] 0.24 Terapkan layout dan komponen dasar ke halaman login bawaan Breeze, sesuaikan visualnya dengan DESIGN.md.

### CRUD Identity Module

- [x] 0.25 `UserController` + view: list user (Data Table), create/edit form (nama, email, role assignment).
- [x] 0.26 `RoleController` + view: list role, create/edit role dengan checkbox permission assignment.
- [x] 0.27 Policy `UserPolicy`, `RolePolicy`: hanya user dengan permission `identity.manage` yang bisa akses CRUD ini.
- [x] 0.28 `CompanyProfileController` + view: form edit single-row company profile.
- [x] 0.29 `SystemSettingController` + view: list dan edit key-value settings.

### Test M0

- [x] 0.30 Feature test: user tanpa role tidak bisa akses halaman manage user (redirect/403).
- [x] 0.31 Feature test: Super Admin bisa create user baru dan assign role lewat UI.
- [x] 0.32 Feature test: audit log tercatat otomatis saat User di-update.

### Review Exit Criteria M0

- [x] 0.33 Verifikasi ulang terhadap exit criteria ROADMAP.md M0: Super Admin login, CRUD user/role jalan, layout konsisten, audit log otomatis. Kalau ada yang belum terpenuhi, jangan lanjut ke M1.

---

## M1 - Finance Core

**Catatan**: seed data CoA sudah tersedia di DATABASE.md Appendix C, tidak lagi menunggu konfirmasi eksternal untuk mulai. VERIFIKASI 1 tetap perlu dilakukan sebelum go-live, tapi tidak menghalangi task di bawah.

### Struktur Module

- [ ] 1.1 Buat struktur folder `app/Modules/Finance` sama seperti pattern Identity di M0 (Http/Controllers, Livewire, Models, Services, Events, Listeners, Providers, routes, database/migrations, resources/views).
- [ ] 1.2 Buat `FinanceServiceProvider`, register, load routes/views/migrations dari module ini.

### Migration dan Model

- [ ] 1.3 Migration `chart_of_accounts` sesuai DATABASE.md Section 3.1, termasuk `parent_id` self-referencing FK.
- [ ] 1.4 Seeder `ChartOfAccountsSeeder`: masukkan seluruh data dari DATABASE.md Appendix C (kode, nama, account_type, parent_code, is_postable). Buat header/group dulu (is_postable=false), baru leaf account yang reference parent_id-nya.
- [ ] 1.5 Migration `journal_entries` dan `journal_entry_lines` sesuai DATABASE.md Section 3.2-3.3.
- [ ] 1.6 Migration `vendors` dan `vendor_bills` sesuai DATABASE.md Section 3.4-3.5.
- [ ] 1.7 Model `ChartOfAccount` (dengan relasi `parent()`/`children()`), `JournalEntry`, `JournalEntryLine`, `Vendor`, `VendorBill`.

### Service Layer

- [ ] 1.8 `JournalEntryService::createEntry(array $data)`: terima array lines (account_id, debit, credit), buka DB transaction, validasi `sum(debit) === sum(credit)` sebelum insert, validasi setiap `account_id` yang dipakai punya `is_postable = true` (tolak kalau ada line yang posting ke akun header/group), kalau tidak balance atau ada akun non-postable lempar custom exception (`UnbalancedJournalEntryException` / `NonPostableAccountException`), commit kalau valid.
- [ ] 1.9 Unit test untuk `JournalEntryService`: test entry balance berhasil, test entry tidak balance ditolak dengan exception yang benar.

### UI

- [ ] 1.10 `ChartOfAccountController` + view: list CoA dalam bentuk indented tree/list sesuai hierarki `parent_id`.
- [ ] 1.11 `JournalEntryController` + view: list journal entry (read-only), detail per entry menampilkan semua line item.
- [ ] 1.12 Form manual journal entry (Livewire component untuk dynamic line rows, tambah/hapus baris debit-credit sebelum submit).
- [ ] 1.13 `VendorController` + `VendorBillController` + view: CRUD dasar.

### Event Skeleton (Consumer Side)

- [ ] 1.14 Definisikan Event class `SalesOrderCompleted` dan `PayrollProcessed` (bisa ditaruh sementara di `app/Modules/Finance/Events` atau di module masing-masing sesuai keputusan final struktur, konsisten dengan ARCHITECTURE.md Section 5).
- [ ] 1.15 Buat Listener `CreateJournalEntryFromSalesOrder` dan `CreateJournalEntryFromPayroll`, keduanya `implements ShouldQueue` sesuai ARCHITECTURE.md Section 5. Isi `handle()` masih kosong/placeholder (`// TODO: implemented in M2/M3`), yang penting struktur dan registrasi listener-nya sudah jalan.
- [ ] 1.16 Register listener di `EventServiceProvider` atau lewat `Event::listen()` di `FinanceServiceProvider::boot()`.

### Review Exit Criteria M1

- [ ] 1.17 Verifikasi terhadap ROADMAP.md M1: CoA terisi dan bisa dipakai, journal entry manual bisa dibuat dengan balance check yang benar-benar menolak entry tidak seimbang.

---

## M2 - Sales & Inventory

### Struktur Module

- [ ] 2.1 Buat struktur folder `app/Modules/SalesInventory` (catatan: satu module folder, tapi dua service class terpisah sesuai keputusan di ARCHITECTURE.md Section 2 dan percakapan sebelumnya).
- [ ] 2.2 Buat `SalesInventoryServiceProvider`, register, load routes/views/migrations.

### Migration dan Model

- [ ] 2.3 Migration `item_categories`, `items` sesuai DATABASE.md Section 4.2-4.3.
- [ ] 2.4 Migration `stock_levels`, `stock_movements` sesuai DATABASE.md Section 4.4-4.5.
- [ ] 2.5 Migration `customers` sesuai DATABASE.md Section 4.1.
- [ ] 2.6 Migration `sales_orders`, `sales_order_items` sesuai DATABASE.md Section 4.6-4.7.
- [ ] 2.7 Migration `invoices`, `payments` sesuai DATABASE.md Section 3.6-3.7 (catatan: tabel ini didefinisikan di dokumen Finance karena secara data itu bagian dari Finance, tapi migration-nya bisa ditempatkan di module Finance meski dipicu dari flow Sales, sesuaikan lokasi migration dengan keputusan final kamu soal pemilik tabel).
- [ ] 2.8 Model untuk seluruh tabel di atas dengan relasi Eloquent yang sesuai (`Item::stockLevel()`, `SalesOrder::items()`, `SalesOrder::invoice()`, dst).

### Service Layer

- [ ] 2.9 `InventoryService::increaseStock(Item $item, float $qty, array $referenceData)`.
- [ ] 2.10 `InventoryService::decreaseStock(Item $item, float $qty, array $referenceData)`, tolak kalau `quantity_on_hand` tidak cukup.
- [ ] 2.11 `InventoryService::reserveStock(Item $item, float $qty)` untuk sales order yang belum completed.
- [ ] 2.12 `InventoryService::recordAdjustment(Item $item, float $qty, string $direction, string $reasonCode, ?string $note)`, wajibkan `$reasonCode` tidak kosong sesuai DATABASE.md Assumption 3.
- [ ] 2.13 `SalesOrderService::createOrder(array $data)`: buat `sales_orders` + `sales_order_items` dalam satu DB transaction, panggil `InventoryService::reserveStock()` untuk tiap item `physical_good`.
- [ ] 2.14 `SalesOrderService::completeOrder(SalesOrder $order)`: ubah status jadi `completed`, panggil `InventoryService::decreaseStock()` untuk realisasi stok keluar, fire event `SalesOrderCompleted`.

### UI

- [ ] 2.15 `ItemController` + view: CRUD Product/Service Catalog, form dengan toggle `item_type` (physical_good/service) yang menyembunyikan field stock-related kalau `service`.
- [ ] 2.16 `CustomerController` + view: CRUD dasar.
- [ ] 2.17 `SalesOrderController` + Livewire component: form create order dengan dynamic item rows (tambah/hapus baris, kalkulasi subtotal otomatis saat quantity/harga berubah).
- [ ] 2.18 View detail Sales Order: tampilkan status (Status Badge sesuai DESIGN.md), item list, tombol "Complete Order" yang memanggil `completeOrder()`.
- [ ] 2.19 View stock adjustment: form manual dengan field reason code wajib diisi.
- [ ] 2.20 View list stock movement per item (riwayat in/out/adjustment).

### Integrasi ke Finance

- [ ] 2.21 Isi logic nyata di `CreateJournalEntryFromSalesOrder::handle()` (listener dari M1 task 1.15): generate `invoices` record, generate `journal_entries` + `journal_entry_lines` (debit piutang, kredit pendapatan) lewat `JournalEntryService` yang sudah dibuat di M1.
- [ ] 2.22 `InvoiceController` + view: detail invoice, tombol record payment.
- [ ] 2.23 Form record payment terhadap invoice, update status invoice jadi `paid` setelah full payment tercatat.

### Test M2

- [ ] 2.24 Feature test end-to-end: buat sales order dengan item fisik → complete order → assert stok berkurang, invoice terbuat, journal entry pendapatan+piutang otomatis muncul dengan angka yang benar.
- [ ] 2.25 Unit test `InventoryService`: assert `decreaseStock` menolak kalau stok tidak cukup, assert `recordAdjustment` menolak kalau `reasonCode` kosong.

### Review Exit Criteria M2

- [ ] 2.26 Verifikasi terhadap ROADMAP.md M2: alur sales order sampai invoice jalan penuh, integrasi ke Finance otomatis tanpa input manual.

---

## M3 - HR & Payroll

**Catatan**: rate BPJS Kesehatan, JHT, JP, JKM sudah tersedia di DATABASE.md Appendix B. VERIFIKASI 2 (kelas risiko JKK, wage cap JP) tetap perlu diselesaikan sebelum M3 dianggap final, tapi tidak menghalangi mulai task di bawah.

### Struktur Module

- [ ] 3.1 Buat struktur folder `app/Modules/HR` sama seperti pattern module sebelumnya.
- [ ] 3.2 Buat `HRServiceProvider`, register, load routes/views/migrations.

### Migration dan Model

- [ ] 3.3 Migration `departments`, `positions`, `employees` sesuai DATABASE.md Section 2.1-2.3, pastikan `ptkp_status` NOT NULL sesuai keputusan.
- [ ] 3.4 Migration `attendances` sesuai DATABASE.md Section 2.4.
- [ ] 3.5 Migration `payroll_components`, `employee_payroll_components` sesuai DATABASE.md Section 2.5.
- [ ] 3.6 Migration `bpjs_rates` sesuai DATABASE.md Section 2.6.
- [ ] 3.7 Migration `ter_categories`, `ptkp_ter_mapping`, `ter_rates` sesuai DATABASE.md Section 2.7.
- [ ] 3.8 Migration `payroll_periods`, `payroll_runs`, `payroll_run_items` sesuai DATABASE.md Section 2.8.
- [ ] 3.9 Model untuk seluruh tabel di atas dengan relasi yang sesuai.

### Seed Data

- [ ] 3.10 Seeder `TerRateSeeder`: masukkan seluruh data dari DATABASE.md Appendix A (3 kategori, mapping PTKP, seluruh baris rate kategori A/B/C). Ini data yang sudah kamu konfirmasi resmi, tinggal dipindah jadi seeder PHP array, bukan ditulis ulang manual satu-satu supaya tidak ada typo, pertimbangkan generate dari CSV/array terstruktur.
- [ ] 3.11 Seeder `BpjsRateSeeder`: masukkan seluruh data dari DATABASE.md Appendix B, termasuk `max_wage_base` untuk BPJS Kesehatan. Rate JKK pakai asumsi 0.24% (kelas risiko terendah) sampai VERIFIKASI 2 selesai, tandai dengan komentar di kode supaya gampang ditemukan kalau perlu diganti nanti.
- [ ] 3.12 Seeder `PayrollComponentSeeder`: komponen dasar yang berlaku umum (kalau ada tunjangan standar perusahaan, masukkan di sini, kalau belum ada bisa dikosongkan dan diisi manual lewat UI nanti).

### Service Layer

- [ ] 3.13 `PayrollService::calculateGrossSalary(Employee $employee)`: jumlahkan base salary + seluruh `employee_payroll_components` bertipe earning yang aktif.
- [ ] 3.14 `PayrollService::calculateBpjsDeductions(Employee $employee, float $grossSalary)`: hitung potongan berdasarkan `bpjs_rates` yang berlaku (`effective_date` terbaru yang belum `end_date`). Terapkan `max_wage_base` sebagai cap: kalau `grossSalary > max_wage_base` (berlaku untuk BPJS Kesehatan), pakai `max_wage_base` sebagai dasar perhitungan, bukan `grossSalary` aktual.
- [ ] 3.15 `PayrollService::calculatePph21(Employee $employee, float $grossSalary)`: lookup `ptkp_ter_mapping` berdasarkan `employee->ptkp_status`, cari `ter_category_id`, query `ter_rates` untuk bracket yang sesuai `income_lower_bound <= $grossSalary <= income_upper_bound` (atau `income_upper_bound IS NULL` untuk bracket teratas), kalikan rate dengan gross salary.
- [ ] 3.16 `PayrollService::processPayrollRun(PayrollPeriod $period)`: loop seluruh employee `employment_status = active`, hitung gross, BPJS, PPh21, net salary, simpan ke `payroll_runs` + `payroll_run_items` (breakdown per komponen), dalam satu DB transaction per employee.
- [ ] 3.17 Setelah seluruh employee diproses, fire event `PayrollProcessed` dengan reference ke `payroll_period_id`.

### UI

- [ ] 3.18 `DepartmentController`, `PositionController` + view: CRUD dasar.
- [ ] 3.19 `EmployeeController` + view: CRUD dengan form termasuk field `ptkp_status` (dropdown TK0/TK1/K0/TK2/TK3/K1/K2/K3).
- [ ] 3.20 `AttendanceController` + view: input kehadiran harian per employee.
- [ ] 3.21 `PayrollComponentController` + view: CRUD komponen gaji, assignment ke employee.
- [ ] 3.22 `PayrollRunController` + view: pilih/buat `payroll_periods`, tombol "Process Payroll" yang memanggil `PayrollService::processPayrollRun()`.
- [ ] 3.23 View slip gaji per employee: breakdown earning, BPJS deduction, PPh21 deduction, net salary, menampilkan `ter_category_used` untuk audit trail.

### Integrasi ke Finance

- [ ] 3.24 Isi logic nyata di `CreateJournalEntryFromPayroll::handle()` (listener dari M1 task 1.15): generate `journal_entries` (debit beban gaji, kredit utang BPJS, kredit utang PPh21, kredit kas/bank untuk net pay) lewat `JournalEntryService`.

### Test M3 (Wajib, Bukan Opsional)

- [ ] 3.25 Unit test `PayrollService::calculatePph21()` dengan minimal 3-5 skenario penghasilan berbeda per kategori TER (A/B/C), hasil dibandingkan manual terhadap kalkulator resmi DJP. **Jangan lanjut ke task berikutnya kalau ada selisih angka yang tidak bisa dijelaskan.**
- [ ] 3.26 Unit test `PayrollService::calculateBpjsDeductions()` dengan skenario gross salary berbeda (termasuk skenario di atas `max_wage_base` untuk memastikan cap diterapkan benar), verifikasi terhadap rate resmi di DATABASE.md Appendix B.
- [ ] 3.27 Feature test end-to-end: process payroll run untuk beberapa employee sekaligus, assert `payroll_runs` dan `journal_entries` terbentuk dengan angka yang konsisten (total debit = total credit).

### Review Exit Criteria M3

- [ ] 3.28 Verifikasi terhadap ROADMAP.md M3: payroll run bisa diproses, PPh21 tervalidasi terhadap sumber resmi, integrasi ke Finance otomatis.

---

## M4 - Hardening, Dashboard, dan UAT

### Dashboard

- [ ] 4.1 `DashboardController` + view: Stat Card total revenue bulan berjalan (query dari `journal_entries`/`invoices` periode berjalan).
- [ ] 4.2 Stat Card outstanding invoice (`invoices` dengan status `unpaid`, total amount).
- [ ] 4.3 Stat Card employee aktif (`employees` dengan `employment_status = active`).
- [ ] 4.4 Stat Card item stock rendah (definisikan threshold "rendah" dulu, bisa hardcode sementara atau tambah field `minimum_stock_level` di `items` kalau belum ada, cek dulu apakah field ini perlu ditambah lewat migration baru).

### Laporan Finansial

- [ ] 4.5 Query dan view laba rugi sederhana per periode bulanan: total pendapatan dikurangi total beban dari `journal_entries` yang sudah diklasifikasi lewat `chart_of_accounts.account_type`.
- [ ] 4.6 Query dan view neraca sederhana: total asset, liability, equity per titik waktu tertentu dari saldo akun.

### Security Review

- [ ] 4.7 Audit seluruh Controller: pastikan setiap action yang mengubah data punya Policy check atau permission check eksplisit, tidak ada yang lolos tanpa authorization.
- [ ] 4.8 Cek seluruh form: pastikan CSRF token ada di semua form (default Blade `@csrf` biasanya otomatis, tapi cek form yang dibuat manual/custom).
- [ ] 4.9 Cek Blade view: pastikan tidak ada penggunaan `{!! !!}` (unescaped output) untuk data yang berasal dari input user, hanya untuk konten yang memang sudah divalidasi aman.
- [ ] 4.10 Cek Model: pastikan `$fillable` atau `$guarded` didefinisikan eksplisit di semua Model untuk mencegah mass assignment vulnerability.

### Performance Review

- [ ] 4.11 Cek query list Sales Order dan Journal Entry (kemungkinan tabel besar): pastikan pakai eager loading (`with()`) untuk relasi yang ditampilkan di tabel, hindari N+1 query.
- [ ] 4.12 Jalankan `EXPLAIN ANALYZE` di PostgreSQL untuk query utama (list sales order, list journal entry, payroll run lookup), verifikasi index yang didefinisikan di DATABASE.md benar-benar dipakai oleh query planner.

### UAT

- [ ] 4.13 Siapkan data demo/seed yang mendekati kondisi riil (customer, item, beberapa sales order, beberapa employee dengan variasi PTKP status berbeda).
- [ ] 4.14 Tulis skenario UAT sederhana per module (misal: "buat sales order baru sampai invoice terbit", "proses payroll bulan berjalan", "tambah user baru dengan role terbatas").
- [ ] 4.15 Jalankan UAT dengan user asli (staff Finance/HR/Sales kalau memungkinkan, atau simulasikan sendiri dengan skenario yang ditulis di atas kalau belum ada akses ke user asli).
- [ ] 4.16 Kumpulkan bug/feedback dari UAT, prioritaskan mana yang blocking untuk go-live vs bisa masuk fase 2.

### Go-Live Prep

- [ ] 4.17 Setup environment production: `.env` production, `APP_DEBUG=false`, konfigurasi PostgreSQL production.
- [ ] 4.18 Setup queue worker dengan Supervisor (untuk listener queued dari M1-M3) sesuai ARCHITECTURE.md Section 10.
- [ ] 4.19 Setup scheduler (`schedule:run` lewat cron) kalau ada job periodik yang dibutuhkan (misalnya reminder invoice jatuh tempo).
- [ ] 4.20 Setup backup database rutin (belum dibahas detail di dokumen manapun, minimal pastikan ada strategi backup sebelum go-live, bukan ditunda sampai setelah live).

### Review Exit Criteria M4

- [ ] 4.21 Verifikasi terhadap ROADMAP.md M4: seluruh module bisa dipakai end-to-end oleh user asli, tidak ada critical bug tersisa, laporan finansial menghasilkan angka masuk akal.

---

Dokumen terkait: `PRD.md`, `ARCHITECTURE.md`, `DATABASE.md`, `DESIGN.md`, `ROADMAP.md`.
