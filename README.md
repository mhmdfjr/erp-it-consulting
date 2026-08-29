# Kelolain ERP

Sistem ERP internal untuk perusahaan IT service & consulting di Indonesia, mencakup empat modul inti: **Identity & Access Management**, **Finance & Accounting**, **Sales & Inventory**, dan **HR & Payroll**.

Dibangun sebagai **modular monolith** di atas Laravel + PostgreSQL, seluruh interface server-rendered Blade dan Livewire.

**Demo langsung:** [erp-it-consulting-production.up.railway.app](https://erp-it-consulting-production.up.railway.app)

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Demo & Kredensial](#demo-kredensial)
- [Tech Stack](#tech-stack)
- [Arsitektur](#arsitektur)
- [Struktur Modul](#struktur-modul)
- [Skema Database](#skema-database)
- [Instalasi Lokal](#instalasi-lokal)
- [Testing](#testing)
- [Deployment](#deployment)
- [Dokumentasi Tambahan](#dokumentasi-tambahan)

---

## Fitur Utama

### Identity & Access Management

- RBAC granular berbasis permission, dibangun di atas `spatie/laravel-permission`.
- Audit log otomatis untuk seluruh perubahan pada entity kritikal (invoice, journal entry, payroll run, stock adjustment).
- Manajemen User, Role, Company Profile, dan System Settings.

### Finance & Accounting

- Chart of Accounts hierarkis.
- Journal Entry engine dengan balance check wajib (`debit = credit`) dan mekanisme **void** untuk koreksi jejak audit tetap utuh.
- Accounts Payable (Vendor Bill, accrual basis) dan Accounts Receivable (Invoice, partial payment).
- Laporan Laba Rugi dan Neraca, dengan perhitungan Laba/Rugi Berjalan otomatis.

### Sales & Inventory

- Katalog produk & jasa dengan pembedaan `physical_good` dan `service`.
- Sales Order dengan campuran item barang dan jasa dalam satu kontrak, invoice ter-generate otomatis.
- Stock reservation saat order dibuat, direalisasi saat order selesai, dilepas saat order dibatalkan.
- Integrasi ke Finance: setiap Sales Order selesai memicu journal entry pendapatan dan HPP, dengan alokasi akun terpisah untuk barang vs jasa.

### HR & Payroll

- Manajemen Employee, Department, Position, dan Attendance harian.
- Payroll run dengan **prorate otomatis berbasis kehadiran**, base salary dipotong proporsional dari `absent`.
- Perhitungan **PPh21 skema TER** (PMK 168/2023) dan **BPJS Kesehatan/Ketenagakerjaan**, tervalidasi terhadap kalkulator resmi DJP.
- Journal entry payroll agregat otomatis per periode, dengan alokasi BPJS gabungan porsi karyawan dan perusahaan.

### Dashboard

- Ringkasan lintas modul (revenue bulan berjalan, outstanding invoice, employee aktif, stock rendah).
- Visualisasi data spesifik per role, Sales melihat tren penjualan dan produk terlaris, Finance melihat arus kas dan umur piutang, HR melihat distribusi kehadiran dan biaya payroll. Visibilitas chart mengikuti permission user, bukan nama role.

---

## Demo & Kredensial

Password seluruh akun demo di bawah: **`password`**

| Role            | Email                                                  | Akses                                                      |
| --------------- | ------------------------------------------------------ | ---------------------------------------------------------- |
| Sales Staff     | `dewi.sales@test.local` / `rian.sales@test.local`      | Item, Customer, Sales Order                                |
| Finance Staff   | `fajar.finance@test.local` / `nina.finance@test.local` | CoA, Journal Entry, Vendor, Invoice (tanpa void)           |
| Finance Manager | `budi.financemanager@test.local`                       | Finance Staff + void journal entry                         |
| HR Staff        | `sari.hr@test.local` / `agus.hr@test.local`            | Employee, Attendance (tanpa proses payroll)                |
| HR Manager      | `lina.hrmanager@test.local`                            | HR Staff + proses payroll                                  |
| Admin           | `andi.admin@test.local`                                | Akses penuh Finance + Sales + HR, **tanpa** akses Identity |

Kredensial Super Admin (akses penuh termasuk User & Role Management) tersedia terpisah, hubungi pemilik project.

---

## Tech Stack

| Layer         | Teknologi                                  |
| ------------- | ------------------------------------------ |
| Backend       | Laravel 13, PHP 8.3                        |
| Database      | PostgreSQL                                 |
| Frontend      | Blade + Livewire + Alpine.js, Tailwind CSS |
| Chart         | Chart.js                                   |
| Authorization | spatie/laravel-permission                  |
| Deployment    | Docker, Railway                            |
| Web Server    | Nginx + PHP-FPM                            |
| Queue         | Database driver                            |

---

## Arsitektur

Sistem dibangun sebagai **modular monolith**: satu aplikasi, satu database, tapi tiap domain bisnis (Identity, Finance, Sales & Inventory, HR) hidup sebagai module folder mandiri dengan routes, views, migration, dan service provider sendiri.

```
app/
  Modules/
    Identity/       -> User, Role, Company Profile, System Setting, Audit Log
    Finance/         -> Chart of Accounts, Journal Entry, Vendor, Invoice, Payment
    SalesInventory/  -> Item, Customer, Sales Order, Stock Management
    HR/              -> Employee, Attendance, Payroll, PPh21/BPJS
  Shared/
    Support/         -> Trait lintas module (Auditable, GeneratesSequentialNumber)
```

**Komunikasi antar module** memakai domain event (Laravel Event/Listener) bukan pemanggilan method langsung lintas module, supaya tiap module bisa berubah independen tanpa saling coupling erat.

---

## Struktur Modul

### Identity & Access Management

Fungsi cross-cutting yang dipakai seluruh module lain. Menyediakan RBAC, master data perusahaan, dan audit log, tidak menyimpan data bisnis module lain.

### Finance & Accounting

Chart of Accounts, Journal Entry Engine (dengan validasi balance dan mekanisme void), Vendor & Vendor Bill (AP), Invoice & Payment (AR). Memegang kepemilikan tabel `invoices`/`payments` meski dipicu dari flow Sales, karena lifecycle dan invariant finansialnya adalah domain Finance.

### Sales & Inventory

`SalesOrderService` dan `InventoryService` sebagai dua service class terpisah dalam satu module folder, menjaga business logic order dan stock tetap terpisah meski erat terkait secara proses bisnis.

### HR & Payroll

`PayrollService` menangani seluruh kalkulasi: prorate attendance, gross salary, potongan BPJS, dan PPh21, masing-masing sebagai method terpisah yang bisa diuji unit secara independen.

---

## Skema Database

Precision numeric (`numeric(15,2)`, bukan `float`) dipakai di seluruh kolom uang untuk menghindari rounding error finansial. Tabel transaksional (`sales_orders`, `invoices`, `journal_entries`, `payroll_runs`) tanpa soft delete, koreksi hanya lewat mekanisme void/cancel dengan kolom `status`, menjaga jejak audit tetap utuh sesuai prinsip auditability sistem.

Ringkasan tabel utama:

| Domain            | Tabel                                                                                                                                     |
| ----------------- | ----------------------------------------------------------------------------------------------------------------------------------------- |
| Identity          | `users`, `roles`, `permissions`, `company_profile`, `system_settings`, `audit_logs`                                                       |
| Finance           | `chart_of_accounts`, `journal_entries`, `journal_entry_lines`, `vendors`, `vendor_bills`, `invoices`, `payments`                          |
| Sales & Inventory | `items`, `item_categories`, `stock_levels`, `stock_movements`, `customers`, `sales_orders`, `sales_order_items`                           |
| HR & Payroll      | `employees`, `departments`, `positions`, `attendances`, `payroll_periods`, `payroll_runs`, `payroll_run_items`, `ter_rates`, `bpjs_rates` |

---

## Instalasi Lokal

### Prasyarat

- PHP 8.3+
- PostgreSQL
- Composer
- Node.js 20+

### Langkah

```bash
git clone <repo-url>
cd erp-it-consulting

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database di `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=erp_it_consulting
DB_USERNAME=your_username
DB_PASSWORD=your_password

SUPER_ADMIN_EMAIL=admin@example.com
SUPER_ADMIN_PASSWORD=ganti_dengan_password_kuat
```

Jalankan migration dan seeder:

```bash
php artisan migrate --seed
```

Ini otomatis membuat struktur database, data referensi (Chart of Accounts, tabel TER, rate BPJS), dan satu akun Super Admin sesuai `SUPER_ADMIN_EMAIL`/`SUPER_ADMIN_PASSWORD` di `.env`.

**Opsional**: isi database dengan data simulasi (customer, item, sales order, payroll run) untuk eksplorasi:

```bash
php artisan db:seed --class="Database\Seeders\Demo\DemoDataSeeder"
```

Build asset dan jalankan server:

```bash
npm run build
php artisan serve
```

Untuk listener queued (journal entry dari Sales Order/Payroll) benar diproses, jalankan queue worker di terminal terpisah:

```bash
php artisan queue:work
```

---

## Testing

```bash
php artisan test
```

Test suite mencakup unit test untuk kalkulasi finansial (PPh21, BPJS, balance journal entry) dan feature test end-to-end untuk alur lintas module (Sales Order → Invoice → Journal Entry, Payroll Run → Journal Entry agregat). Kalkulasi PPh21 tervalidasi manual terhadap kalkulator resmi DJP untuk memastikan akurasi bukan cuma konsisten secara internal.

---

## Deployment

Aplikasi berjalan di **Railway** dengan tiga komponen:

- **web**: Nginx + PHP-FPM, melayani HTTP request.
- **worker**: proses queue menangani listener finansial asynchronous.
- **PostgreSQL**: database terkelola Railway.

Build memakai multi-stage Docker: asset frontend (Vite) di-compile di stage terpisah dari runtime PHP, menghasilkan image production yang ramping tanpa Node.js ikut terbawa.

```
Dockerfile         -> image service web (nginx + php-fpm + asset build)
Dockerfile.worker  -> image service worker (PHP CLI, tanpa web server)
docker/nginx.conf  -> konfigurasi nginx
docker/entrypoint.sh -> migration + cache config saat container start
```

---

## Dokumentasi Tambahan

| Dokumen            | Isi                                                          |
| ------------------ | ------------------------------------------------------------ |
| `PRD.md`           | Product requirements, scope, dan keputusan out-of-scope      |
| `ARCHITECTURE.md`  | Keputusan arsitektur, domain event catalog, testing strategy |
| `DATABASE.md`      | Skema lengkap, seed data referensi, relasi antar module      |
| `DESIGN.md`        | Design system, komponen UI, panduan visual                   |
| `ROADMAP.md`       | Milestone dan prioritas fase berikutnya                      |
| `TASKS.md`         | Checklist implementasi per milestone                         |
| `UAT_SCENARIOS.md` | Skenario pengujian manual per module                         |

---

## Lisensi

Project ini dibangun sebagai portofolio pribadi, bukan produk komersial yang didistribusikan publik.
