# Database Schema

## Sistem ERP - Perusahaan IT Service & Consulting

Status: Draft v1.0
Database: PostgreSQL
Terakhir diperbarui: 2026-08-03 (revisi M2)

---

## 0. Assumptions yang Berpengaruh ke Schema Ini

Dua open question dari PRD.md belum terjawab final. Schema di bawah dibuat dengan asumsi berikut, ditandai supaya mudah direvisi:

- **ASUMSI 1 - Payroll prorate**: payroll run diasumsikan **full month**, tidak ada prorate otomatis untuk karyawan yang baru join atau resign di tengah bulan. Kalau nanti butuh prorate, perubahan terjadi di level business logic (service layer), bukan di schema, karena `payroll_runs` sudah menyimpan `period_start`/`period_end` yang bisa dipakai untuk hitung prorate kapan saja.
- **ASUMSI 2 - Tabel TER**: **sudah tidak lagi asumsi**, data resmi TER Bulanan (kategori A/B/C) berdasarkan PMK 168/2023 sudah tersedia dan dimasukkan sebagai seed data di Appendix A. TER Harian untuk pegawai tidak tetap **resmi di luar scope MVP**, didefer ke fase 2 (lihat Section 2.9). Schema saat ini hanya mendukung employee dengan skema penggajian bulanan (TER Bulanan).
- **ASUMSI 3 - Stock adjustment**: `stock_movements` mewajibkan `reason_code` untuk movement bertipe `adjustment`, supaya ada jejak kenapa stok berubah di luar transaksi sales. Kalau ternyata tidak perlu reason code wajib, tinggal ubah constraint `NOT NULL` jadi nullable, tidak perlu migrasi struktural.
- **ASUMSI 4 - Chart of Accounts**: seed data awal sudah tersedia di Appendix C, diadaptasi dari referensi CoA perusahaan jasa dan disesuaikan untuk bisnis campuran jasa+barang teknologi. Ini bukan hasil audit akuntan, tetap perlu direview sebelum go-live produksi (lihat catatan di Appendix C).
- **ASUMSI 6 - Ownership tabel `invoices`/`payments`**: **sudah diputuskan** (M2), migration dan Model kedua tabel ini tinggal di module **Finance**, bukan `SalesInventory`, meski trigger pembuatannya berasal dari flow Sales Order. Alasan: ownership data ditentukan oleh siapa yang mendefinisikan lifecycle dan invariant-nya (status unpaid/paid/void, relasi ke `journal_entries`), bukan oleh siapa yang memicunya — sama seperti `journal_entries` sendiri tidak ditaruh di SalesInventory meski dipicu event dari sana. Lihat Section 3.6/3.7 untuk detail.
- **ASUMSI 7 - Invoice generation timing**: **sudah diputuskan** (M2), `Invoice` digenerate **sync** di dalam `SalesOrderService::completeOrder()`, dalam DB transaction yang sama dengan `InventoryService::decreaseStock()`. Event `SalesOrderCompleted` baru di-fire setelah commit, membawa payload `sales_order_id` **dan** `invoice_id` yang sudah ada. Listener `CreateJournalEntryFromSalesOrder` (queued) tidak lagi bertanggung jawab membuat invoice, cuma membuat `journal_entries` dari invoice yang sudah ada. Alasan: invoice generation adalah transformasi administratif yang jadi bagian definisi bisnis "complete order" itu sendiri (PRD Section 4.4), bukan efek finansial lintas module yang butuh proteksi queue seperti journal entry. Menaruhnya di listener queued berarti user bisa selesai "Complete Order" tanpa invoice muncul sampai queue worker jalan, ambigu dan membingungkan secara UX.
- **ASUMSI 8 - Sales Order cancellation**: **sudah diputuskan** (M2), `sales_orders.status` bisa bertransisi ke `cancelled` **hanya** dari `draft` (enum `confirmed` dihapus dari status flow M2 — tidak ada kode manapun yang pernah men-set order ke status itu, `createOrder()` selalu mulai dari `draft` dan langsung bisa menuju `completed`/`cancelled`). Tidak bisa cancel dari `completed`, karena itu butuh return/refund flow yang di luar scope MVP (lihat PRD.md Section 2.3). Cancel melepas stock reservation lewat `InventoryService::releaseReservedStock()`, yang mengurangi `stock_levels.quantity_reserved` langsung tanpa insert row baru di `stock_movements` (bukan physical movement barang, barang tidak pernah keluar gudang).
- **ASUMSI 5 - HPP/COGS timing**: **sudah diputuskan**, pakai metode **perpetual** (real-time per transaksi), bukan periodic. Setiap `SalesOrderCompleted` untuk item `physical_good` menghasilkan dua pasang jurnal sekaligus: pendapatan (debit Piutang, kredit Pendapatan) dan HPP (debit 501 HPP, kredit 105 Persediaan). Nilai HPP diambil dari `items.cost_price` **tunggal** (last-cost), bukan FIFO atau weighted average — kalau harga modal barang berubah antar batch pembelian, sistem tidak melacak cost per-lot, cuma pakai `cost_price` yang tersimpan di `items` saat transaksi terjadi. Ini simplifikasi sadar untuk MVP: cocok kalau harga modal barang relatif stabil, kurang akurat kalau modal sering fluktuatif (misal karena kurs). Kalau kebutuhan akurasi costing meningkat di fase 2, ini butuh tabel tambahan (`stock_lots` atau sejenis) untuk valuation method yang lebih tepat, bukan sekadar perubahan konfigurasi.

---

## 1. Identity & Access Management + Master Data

### 1.1 `users`

| Column        | Type         | Constraint             | Keterangan  |
| ------------- | ------------ | ---------------------- | ----------- |
| id            | bigserial    | PK                     |             |
| name          | varchar(255) | NOT NULL               |             |
| email         | varchar(255) | UNIQUE, NOT NULL       |             |
| password      | varchar(255) | NOT NULL               | hashed      |
| is_active     | boolean      | NOT NULL, DEFAULT true |             |
| last_login_at | timestamptz  | NULL                   |             |
| created_at    | timestamptz  | NOT NULL               |             |
| updated_at    | timestamptz  | NOT NULL               |             |
| deleted_at    | timestamptz  | NULL                   | soft delete |

Index: `email` (unique, sudah otomatis dari constraint).
**Catatan implementasi**: migration dasar `users` (name, email, password, dst) datang dari Laravel default scaffold. Kolom ERP-specific (`is_active`, `last_login_at`, `deleted_at`) ditambahkan lewat migration terpisah di `app/Modules/Identity/database/migrations`, dijalankan setelah base migration. Ini bukan penyimpangan schema, cuma soal split migration file.

### 1.2 RBAC (`roles`, `permissions`, pivot tables)

Direkomendasikan pakai package `spatie/laravel-permission` daripada reinvent RBAC dari nol. Package ini generate struktur berikut, jadi tidak perlu ditulis migration manual:

| Table                   | Fungsi                                                                            |
| ----------------------- | --------------------------------------------------------------------------------- |
| `roles`                 | id, name, guard_name                                                              |
| `permissions`           | id, name, guard_name                                                              |
| `model_has_roles`       | pivot: model_type, model_id, role_id                                              |
| `model_has_permissions` | pivot: model_type, model_id, permission_id (untuk direct permission di luar role) |
| `role_has_permissions`  | pivot: role_id, permission_id                                                     |

Role `Super Admin` diberi seluruh permission yang ada di sistem lewat seeding, bukan lewat bypass check di kode (`if ($user->isSuperAdmin())`). Ini penting supaya semua authorization check tetap konsisten lewat satu jalur (`$user->can(...)`), tidak ada jalur pintas yang gampang lupa di-maintain saat permission baru ditambah.

### 1.3 `company_profile`

Single row table (constraint aplikasi, bukan constraint database, karena single company).

| Column     | Type         | Constraint | Keterangan |
| ---------- | ------------ | ---------- | ---------- |
| id         | bigserial    | PK         |            |
| name       | varchar(255) | NOT NULL   |            |
| npwp       | varchar(20)  | NULL       |            |
| address    | text         | NULL       |            |
| phone      | varchar(50)  | NULL       |            |
| email      | varchar(255) | NULL       |            |
| logo_path  | varchar(255) | NULL       |            |
| created_at | timestamptz  | NOT NULL   |            |
| updated_at | timestamptz  | NOT NULL   |            |

### 1.4 `system_settings`

Key-value store untuk konfigurasi yang bisa berubah tanpa deploy kode.

| Column      | Type         | Constraint       | Keterangan |
| ----------- | ------------ | ---------------- | ---------- |
| id          | bigserial    | PK               |            |
| key         | varchar(255) | UNIQUE, NOT NULL |            |
| value       | jsonb        | NOT NULL         |            |
| description | text         | NULL             |            |
| updated_at  | timestamptz  | NOT NULL         |            |

### 1.5 `audit_logs`

| Column         | Type         | Constraint           | Keterangan                                           |
| -------------- | ------------ | -------------------- | ---------------------------------------------------- |
| id             | bigserial    | PK                   |                                                      |
| user_id        | bigint       | FK -> users.id, NULL | null kalau system action                             |
| action         | varchar(50)  | NOT NULL             | create/update/delete                                 |
| auditable_type | varchar(255) | NOT NULL             | nama model, misal `App\Modules\Finance\JournalEntry` |
| auditable_id   | bigint       | NOT NULL             |                                                      |
| old_values     | jsonb        | NULL                 |                                                      |
| new_values     | jsonb        | NULL                 |                                                      |
| created_at     | timestamptz  | NOT NULL             |                                                      |

Index: composite `(auditable_type, auditable_id)` untuk query riwayat perubahan satu entity.

---

## 2. Human Resources (termasuk Payroll dengan PPh21 TER-only)

### 2.1 `departments`

| Column     | Type         | Constraint | Keterangan |
| ---------- | ------------ | ---------- | ---------- |
| id         | bigserial    | PK         |            |
| name       | varchar(255) | NOT NULL   |            |
| created_at | timestamptz  | NOT NULL   |            |
| updated_at | timestamptz  | NOT NULL   |            |

### 2.2 `positions`

| Column        | Type         | Constraint                     | Keterangan |
| ------------- | ------------ | ------------------------------ | ---------- |
| id            | bigserial    | PK                             |            |
| department_id | bigint       | FK -> departments.id, NOT NULL |            |
| title         | varchar(255) | NOT NULL                       |            |
| created_at    | timestamptz  | NOT NULL                       |            |
| updated_at    | timestamptz  | NOT NULL                       |            |

### 2.3 `employees`

| Column              | Type         | Constraint                   | Keterangan                                                                                  |
| ------------------- | ------------ | ---------------------------- | ------------------------------------------------------------------------------------------- |
| id                  | bigserial    | PK                           |                                                                                             |
| user_id             | bigint       | FK -> users.id, NULL         | nullable, tidak semua employee punya login                                                  |
| employee_code       | varchar(50)  | UNIQUE, NOT NULL             |                                                                                             |
| full_name           | varchar(255) | NOT NULL                     |                                                                                             |
| nik                 | varchar(20)  | NULL                         | KTP number                                                                                  |
| npwp                | varchar(20)  | NULL                         |                                                                                             |
| gender              | varchar(10)  | NOT NULL                     |                                                                                             |
| birth_date          | date         | NOT NULL                     |                                                                                             |
| ptkp_status         | varchar(10)  | NOT NULL                     | enum: TK0, TK1, TK2, TK3, K0, K1, K2, K3                                                    |
| position_id         | bigint       | FK -> positions.id, NOT NULL |                                                                                             |
| hire_date           | date         | NOT NULL                     |                                                                                             |
| termination_date    | date         | NULL                         |                                                                                             |
| employment_status   | varchar(20)  | NOT NULL, DEFAULT 'active'   | enum: active, resigned, terminated                                                          |
| bank_name           | varchar(100) | NULL                         |                                                                                             |
| bank_account_number | varchar(50)  | NULL                         |                                                                                             |
| address             | text         | NULL                         |                                                                                             |
| phone               | varchar(50)  | NULL                         |                                                                                             |
| email               | varchar(255) | NULL                         |                                                                                             |
| created_at          | timestamptz  | NOT NULL                     |                                                                                             |
| updated_at          | timestamptz  | NOT NULL                     |                                                                                             |
| deleted_at          | timestamptz  | NULL                         | soft delete, employee resign tidak boleh hard delete karena riwayat payroll harus tetap ada |

Index: `employee_code` (unique), `position_id`, `employment_status` (untuk filter employee aktif saat generate payroll run).

### 2.4 `attendances`

| Column      | Type        | Constraint                   | Keterangan                         |
| ----------- | ----------- | ---------------------------- | ---------------------------------- |
| id          | bigserial   | PK                           |                                    |
| employee_id | bigint      | FK -> employees.id, NOT NULL |                                    |
| date        | date        | NOT NULL                     |                                    |
| check_in    | time        | NULL                         |                                    |
| check_out   | time        | NULL                         |                                    |
| status      | varchar(20) | NOT NULL                     | enum: present, absent, leave, sick |
| note        | text        | NULL                         |                                    |
| created_at  | timestamptz | NOT NULL                     |                                    |

Index: composite unique `(employee_id, date)`, satu employee cuma boleh punya satu record attendance per tanggal.

### 2.5 Payroll Component Configuration

**`payroll_components`** - master data komponen gaji yang dikonfigurasi.

| Column           | Type         | Constraint             | Keterangan                             |
| ---------------- | ------------ | ---------------------- | -------------------------------------- |
| id               | bigserial    | PK                     |                                        |
| name             | varchar(255) | NOT NULL               | misal "Tunjangan Transport"            |
| type             | varchar(20)  | NOT NULL               | enum: earning, deduction               |
| calculation_type | varchar(20)  | NOT NULL               | enum: fixed_amount, percentage_of_base |
| is_active        | boolean      | NOT NULL, DEFAULT true |                                        |
| created_at       | timestamptz  | NOT NULL               |                                        |
| updated_at       | timestamptz  | NOT NULL               |                                        |

**`employee_payroll_components`** - assignment komponen ke employee tertentu dengan nilai spesifik.

| Column               | Type          | Constraint                            | Keterangan                                          |
| -------------------- | ------------- | ------------------------------------- | --------------------------------------------------- |
| id                   | bigserial     | PK                                    |                                                     |
| employee_id          | bigint        | FK -> employees.id, NOT NULL          |                                                     |
| payroll_component_id | bigint        | FK -> payroll_components.id, NOT NULL |                                                     |
| amount               | numeric(15,2) | NULL                                  | dipakai kalau calculation_type = fixed_amount       |
| percentage           | numeric(5,2)  | NULL                                  | dipakai kalau calculation_type = percentage_of_base |
| effective_date       | date          | NOT NULL                              |                                                     |
| end_date             | date          | NULL                                  |                                                     |

Index: composite `(employee_id, payroll_component_id)`.

### 2.6 BPJS Rate Configuration

**`bpjs_rates`**

| Column                   | Type          | Constraint | Keterangan                                                                                                                             |
| ------------------------ | ------------- | ---------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| id                       | bigserial     | PK         |                                                                                                                                        |
| bpjs_type                | varchar(30)   | NOT NULL   | enum: kesehatan, jht, jkk, jkm, jp                                                                                                     |
| rate_employee_percentage | numeric(5,2)  | NOT NULL   | potongan dari gaji employee                                                                                                            |
| rate_company_percentage  | numeric(5,2)  | NOT NULL   | beban perusahaan, dicatat di journal tapi tidak potong gaji employee                                                                   |
| max_wage_base            | numeric(15,2) | NULL       | batas atas gaji yang dihitung untuk iuran (contoh: BPJS Kesehatan dibatasi Rp12.000.000/bulan per 2026). Null berarti tidak ada batas. |
| effective_date           | date          | NOT NULL   |                                                                                                                                        |
| end_date                 | date          | NULL       |                                                                                                                                        |

Rate disimpan dengan `effective_date`/`end_date`, bukan single row yang di-overwrite, supaya histori payroll lama tetap bisa direkonstruksi dengan rate yang berlaku saat itu meskipun rate berubah di kemudian hari.

**Catatan implementasi**: rate resmi BPJS Kesehatan dan BPJS Ketenagakerjaan (JHT, JP, JKM) sudah tersedia, lihat Appendix B untuk seed data. **Rate JKK** tergantung kelas risiko pekerjaan yang terdaftar resmi untuk perusahaan ini di BPJS Ketenagakerjaan, dan **belum dikonfirmasi eksplisit untuk perusahaan ini** meskipun asumsi default (kelas risiko rendah, sesuai profil perusahaan IT service) sudah dicatat di Appendix B. Ini bukan placeholder sembarangan, tapi tetap butuh konfirmasi resmi ke BPJS Ketenagakerjaan sebelum go-live, karena kelas risiko ditentukan lewat proses pendaftaran resmi, bukan tebakan berdasarkan jenis industri semata.

### 2.7 PPh21 TER Configuration

**`ter_categories`**

| Column      | Type         | Constraint       | Keterangan   |
| ----------- | ------------ | ---------------- | ------------ |
| id          | bigserial    | PK               |              |
| code        | varchar(5)   | UNIQUE, NOT NULL | A, B, atau C |
| description | varchar(255) | NULL             |              |

**`ptkp_ter_mapping`**

| Column          | Type        | Constraint                        | Keterangan     |
| --------------- | ----------- | --------------------------------- | -------------- |
| id              | bigserial   | PK                                |                |
| ptkp_status     | varchar(10) | UNIQUE, NOT NULL                  | TK0, TK1, dst. |
| ter_category_id | bigint      | FK -> ter_categories.id, NOT NULL |                |

**`ter_rates`**

| Column             | Type          | Constraint                        | Keterangan                                    |
| ------------------ | ------------- | --------------------------------- | --------------------------------------------- |
| id                 | bigserial     | PK                                |                                               |
| ter_category_id    | bigint        | FK -> ter_categories.id, NOT NULL |                                               |
| income_lower_bound | numeric(15,2) | NOT NULL                          | batas bawah penghasilan bruto bulanan         |
| income_upper_bound | numeric(15,2) | NULL                              | null berarti tidak terbatas (bracket teratas) |
| rate_percentage    | numeric(5,2)  | NOT NULL                          |                                               |
| effective_date     | date          | NOT NULL                          |                                               |
| end_date           | date          | NULL                              |                                               |

Index: composite `(ter_category_id, income_lower_bound)` untuk lookup cepat saat kalkulasi payroll.

**Catatan implementasi**: data `ter_rates` dan `ptkp_ter_mapping` untuk TER Bulanan sudah tersedia resmi berdasarkan PMK 168/2023, lihat Appendix A untuk seed data lengkap.

### 2.9 TER Harian (Pegawai Tidak Tetap) - Out of Scope MVP

**Keputusan**: TER Harian tidak masuk MVP, didefer ke fase 2. Schema `employees` di MVP ini diasumsikan seluruh employee berstatus pegawai tetap bulanan, tidak ada kolom `employment_type` untuk membedakan pegawai tetap vs harian.

Dicatat di sini sebagai referensi untuk fase 2, supaya tidak perlu riset ulang skema kalau nanti dibutuhkan:

PMK 168/2023 Pasal 13(2)b menetapkan skema **TER Harian** yang terpisah dari TER Bulanan, berlaku untuk pegawai tidak tetap yang dibayar harian:

- Penghasilan bruto harian sampai dengan Rp 2.500.000: rate 0.5%.
- Penghasilan bruto harian di atas Rp 2.500.000: memakai tarif Pasal 17 ayat (1) huruf a UU PPh (bracket progresif 5/15/25/30/35%) atas 50% dari penghasilan bruto harian, bukan TER.

Ini skema kalkulasi yang **berbeda secara struktural** dari TER Bulanan, bukan sekadar variasi rate. Kalau nanti diimplementasikan di fase 2, schema butuh tambahan:

- `employees.employment_type` (enum: `permanent`, `daily_worker`) untuk membedakan jalur kalkulasi payroll mana yang dipakai.
- Tabel terpisah untuk rate TER Harian (`ter_daily_rates` atau sejenis), karena strukturnya beda (threshold tunggal + fallback ke bracket progresif atas 50% bruto, bukan lookup table multi-bracket seperti TER Bulanan).
- Logic tambahan di `PayrollService` untuk cabang kalkulasi berdasarkan `employment_type`.

### 2.8 Payroll Execution

**`payroll_periods`**

| Column       | Type        | Constraint                | Keterangan                   |
| ------------ | ----------- | ------------------------- | ---------------------------- |
| id           | bigserial   | PK                        |                              |
| period_month | smallint    | NOT NULL                  | 1-12                         |
| period_year  | smallint    | NOT NULL                  |                              |
| status       | varchar(20) | NOT NULL, DEFAULT 'draft' | enum: draft, processed, paid |
| processed_at | timestamptz | NULL                      |                              |
| created_at   | timestamptz | NOT NULL                  |                              |

Index: composite unique `(period_month, period_year)`.

**`payroll_runs`** - satu row per employee per period.

| Column                   | Type          | Constraint                         | Keterangan                                     |
| ------------------------ | ------------- | ---------------------------------- | ---------------------------------------------- |
| id                       | bigserial     | PK                                 |                                                |
| payroll_period_id        | bigint        | FK -> payroll_periods.id, NOT NULL |                                                |
| employee_id              | bigint        | FK -> employees.id, NOT NULL       |                                                |
| base_salary              | numeric(15,2) | NOT NULL                           |                                                |
| gross_salary             | numeric(15,2) | NOT NULL                           | base + seluruh earning component               |
| bpjs_kesehatan_deduction | numeric(15,2) | NOT NULL, DEFAULT 0                |                                                |
| bpjs_jht_deduction       | numeric(15,2) | NOT NULL, DEFAULT 0                |                                                |
| bpjs_jp_deduction        | numeric(15,2) | NOT NULL, DEFAULT 0                |                                                |
| pph21_deduction          | numeric(15,2) | NOT NULL, DEFAULT 0                | hasil lookup TER                               |
| ter_category_used        | varchar(5)    | NULL                               | audit trail kategori TER yang dipakai saat itu |
| total_deduction          | numeric(15,2) | NOT NULL                           |                                                |
| net_salary               | numeric(15,2) | NOT NULL                           |                                                |
| status                   | varchar(20)   | NOT NULL, DEFAULT 'draft'          | enum: draft, finalized, paid                   |
| created_at               | timestamptz   | NOT NULL                           |                                                |

Index: composite unique `(payroll_period_id, employee_id)`.

**`payroll_run_items`** - breakdown detail per komponen, dipakai untuk render slip gaji.

| Column               | Type          | Constraint                        | Keterangan                                                |
| -------------------- | ------------- | --------------------------------- | --------------------------------------------------------- |
| id                   | bigserial     | PK                                |                                                           |
| payroll_run_id       | bigint        | FK -> payroll_runs.id, NOT NULL   |                                                           |
| payroll_component_id | bigint        | FK -> payroll_components.id, NULL | null kalau item BPJS/PPh21 (bukan configurable component) |
| label                | varchar(255)  | NOT NULL                          |                                                           |
| amount               | numeric(15,2) | NOT NULL                          |                                                           |
| type                 | varchar(20)   | NOT NULL                          | enum: earning, deduction                                  |

---

## 3. Finance & Accounting

### 3.1 `chart_of_accounts`

| Column       | Type         | Constraint                       | Keterangan                                                                                                                                                             |
| ------------ | ------------ | -------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| id           | bigserial    | PK                               |                                                                                                                                                                        |
| code         | varchar(20)  | UNIQUE, NOT NULL                 | misal "101"                                                                                                                                                            |
| name         | varchar(255) | NOT NULL                         |                                                                                                                                                                        |
| account_type | varchar(20)  | NOT NULL                         | enum: asset, liability, equity, revenue, expense                                                                                                                       |
| parent_id    | bigint       | FK -> chart_of_accounts.id, NULL | untuk hierarki akun                                                                                                                                                    |
| is_postable  | boolean      | NOT NULL, DEFAULT true           | `false` untuk akun header/group (misal "100 - Aktiva Lancar") yang cuma pembungkus, `true` untuk akun leaf yang benar-benar dipakai posting jurnal (misal "101 - Kas") |
| is_active    | boolean      | NOT NULL, DEFAULT true           |                                                                                                                                                                        |
| created_at   | timestamptz  | NOT NULL                         |                                                                                                                                                                        |
| updated_at   | timestamptz  | NOT NULL                         |                                                                                                                                                                        |

Index: `code` (unique), `parent_id`.

**Kenapa `is_postable` ditambah**: begitu CoA final punya struktur header/group (contoh: "100 - Aktiva Lancar" sebagai pembungkus, bukan akun yang pernah dipakai transaksi langsung), `JournalEntryService` perlu cara menolak baris jurnal yang salah diposting ke akun header. Tanpa flag ini, tidak ada cara membedakan akun header dari akun leaf selain menebak dari kode atau `parent_id IS NULL`, yang rapuh kalau struktur CoA berubah.

### 3.2 `journal_entries`

| Column         | Type         | Constraint                         | Keterangan                                                                                                                                                              |
| -------------- | ------------ | ---------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| id             | bigserial    | PK                                 |                                                                                                                                                                         |
| entry_number   | varchar(30)  | UNIQUE, NOT NULL                   | human-readable reference, format `JE-{YYYY}-{6 digit sequential}` misal `JE-2026-000001`, digenerate di `JournalEntryService::createEntry()`, sequence reset tiap tahun |
| entry_date     | date         | NOT NULL                           |                                                                                                                                                                         |
| reference_type | varchar(255) | NULL                               | misal `SalesOrder`, `PayrollRun`                                                                                                                                        |
| reference_id   | bigint       | NULL                               |                                                                                                                                                                         |
| description    | text         | NULL                               |                                                                                                                                                                         |
| created_by     | bigint       | FK -> users.id, NULL               | null kalau digenerate otomatis dari domain event                                                                                                                        |
| status         | varchar(20)  | NOT NULL, DEFAULT 'posted'         | enum: posted, void                                                                                                                                                      |
| void_reason    | text         | NULL, NOT NULL kalau status = void | alasan wajib diisi saat void, dicek di level aplikasi bukan DB constraint (butuh conditional logic, sama seperti `reason_code` di `stock_movements`)                    |
| created_at     | timestamptz  | NOT NULL                           |                                                                                                                                                                         |

Index: composite `(reference_type, reference_id)` untuk trace jurnal dari transaksi sumbernya. Index unique `entry_number`.

**Immutability setelah posted**: baris `journal_entries` yang sudah `posted` tidak pernah diubah nilai debit/credit-nya (dan baris `journal_entry_lines` terkait juga tidak). Void cuma flip `status` jadi `void` dan isi `void_reason`, tidak pernah hapus atau edit angka. Ini konsekuensi langsung dari prinsip "tidak ada delete untuk data finansial" (lihat Section 6). **Setiap query laporan finansial (laba rugi, neraca) wajib filter `WHERE status = 'posted'`**, kalau tidak, entry yang di-void tetap ikut terhitung dan laporan jadi salah — lihat catatan yang sama di ARCHITECTURE.md.

### 3.3 `journal_entry_lines`

| Column           | Type          | Constraint                           | Keterangan |
| ---------------- | ------------- | ------------------------------------ | ---------- |
| id               | bigserial     | PK                                   |            |
| journal_entry_id | bigint        | FK -> journal_entries.id, NOT NULL   |            |
| account_id       | bigint        | FK -> chart_of_accounts.id, NOT NULL |            |
| debit            | numeric(15,2) | NOT NULL, DEFAULT 0                  |            |
| credit           | numeric(15,2) | NOT NULL, DEFAULT 0                  |            |
| description      | varchar(255)  | NULL                                 |            |

Constraint di level aplikasi (bukan database constraint, karena butuh agregasi lintas rows): total debit harus sama dengan total credit dalam satu `journal_entry_id`. Ini di-enforce di `JournalEntryService`, bukan dibiarkan longgar.

Index: `journal_entry_id`, `account_id`.

### 3.4 `vendors`

| Column     | Type         | Constraint | Keterangan |
| ---------- | ------------ | ---------- | ---------- |
| id         | bigserial    | PK         |            |
| name       | varchar(255) | NOT NULL   |            |
| npwp       | varchar(20)  | NULL       |            |
| address    | text         | NULL       |            |
| phone      | varchar(50)  | NULL       |            |
| email      | varchar(255) | NULL       |            |
| created_at | timestamptz  | NOT NULL   |            |
| updated_at | timestamptz  | NOT NULL   |            |

### 3.5 `vendor_bills`

Representasi minimal AP tanpa Purchasing module formal (sesuai PRD, PO ke vendor didefer ke fase berikutnya).

| Column      | Type          | Constraint                           | Keterangan                                                                                                                                                          |
| ----------- | ------------- | ------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| id          | bigserial     | PK                                   |                                                                                                                                                                     |
| vendor_id   | bigint        | FK -> vendors.id, NOT NULL           |                                                                                                                                                                     |
| account_id  | bigint        | FK -> chart_of_accounts.id, NOT NULL | akun yang di-debit saat bill dibuat (beban atau aset), harus `is_postable = true`, divalidasi di `VendorBillService` sama seperti validasi di `JournalEntryService` |
| bill_number | varchar(100)  | NOT NULL                             |                                                                                                                                                                     |
| bill_date   | date          | NOT NULL                             |                                                                                                                                                                     |
| due_date    | date          | NOT NULL                             |                                                                                                                                                                     |
| amount      | numeric(15,2) | NOT NULL                             |                                                                                                                                                                     |
| status      | varchar(20)   | NOT NULL, DEFAULT 'unpaid'           | enum: unpaid, paid, void                                                                                                                                            |
| created_at  | timestamptz   | NOT NULL                             |                                                                                                                                                                     |
| updated_at  | timestamptz   | NOT NULL                             |                                                                                                                                                                     |

**Keputusan integrasi ke Finance (M1)**:

- **Jurnal dibuat otomatis saat bill dibuat** (accrual basis), bukan saat dibayar. Jurnal: debit `account_id` (dari bill), kredit 201 Utang Usaha, sebesar `amount`. Ini konsisten dengan Sales Order yang juga accrual di sisi piutang (piutang dicatat saat sales completed, bukan saat cash diterima).
- Satu bill = satu akun (`account_id` tunggal). Kalau ada kebutuhan split satu bill ke beberapa akun beban, bill tersebut dipecah jadi beberapa bill terpisah, **bukan** ditambah tabel `vendor_bill_lines`. Ini keputusan sadar untuk menjaga schema tetap sederhana di MVP.
- **Tidak ada tabel `vendor_bill_payments`**. Berbeda sengaja dari `invoices`/`payments` — pelunasan vendor bill cuma toggle `status` jadi `paid` secara manual lewat UI, tanpa payment detail (tanggal, metode, reference number) tersimpan terpisah. Alasan: risiko AP lebih rendah daripada AR untuk MVP ini (perusahaan sendiri yang mengontrol kapan dan berapa dibayar ke vendor, tidak butuh audit trail sedetail rekonsiliasi pembayaran customer). Saat status berubah jadi `paid`, generate jurnal kedua: debit 201 Utang Usaha, kredit 101/102 Kas/Bank. Kalau kebutuhan partial payment tracking untuk vendor muncul di fase berikutnya, ini upgrade schema terpisah (tambah tabel `vendor_bill_payments` mirror struktur `payments`).

### 3.6 `invoices`

Invoice ke customer, digenerate dari Sales Order.

**Kepemilikan module (keputusan M2, ASUMSI 6)**: migration dan Model `Invoice` tinggal di `app/Modules/Finance`, **bukan** `SalesInventory`, meski trigger pembuatannya berasal dari flow Sales Order. Ownership data ditentukan oleh siapa yang mendefinisikan lifecycle dan invariant-nya (status unpaid/paid/void, relasi ke `journal_entries`), bukan oleh siapa yang memicunya. Konsekuensi teknis: migration file `invoices` harus punya timestamp **setelah** migration `sales_orders` (TASKS.md task 2.6, folder `SalesInventory`), supaya FK constraint `sales_order_id` tidak gagal saat `php artisan migrate` — Laravel menjalankan migration berurutan berdasarkan timestamp filename, bukan berdasarkan module mana yang me-load-nya lewat `loadMigrationsFrom()`.

**Timing generation (keputusan M2, ASUMSI 7)**: `Invoice` digenerate **sync** di `SalesOrderService::completeOrder()`, dalam DB transaction yang sama dengan `InventoryService::decreaseStock()`. Event `SalesOrderCompleted` baru di-fire setelah commit dengan payload `sales_order_id` dan `invoice_id`. Listener `CreateJournalEntryFromSalesOrder` (queued) tidak membuat invoice, cuma membaca invoice yang sudah ada untuk membuat `journal_entries`.

| Column         | Type          | Constraint                      | Keterangan                                                                                                                                  |
| -------------- | ------------- | ------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| id             | bigserial     | PK                              |                                                                                                                                             |
| sales_order_id | bigint        | FK -> sales_orders.id, NOT NULL |                                                                                                                                             |
| invoice_number | varchar(100)  | UNIQUE, NOT NULL                | format `INV-{YYYY}-{6 digit sequential}` misal `INV-2026-000001`, sequence reset tiap tahun, konsisten dengan `entry_number` di Section 3.2 |
| invoice_date   | date          | NOT NULL                        |                                                                                                                                             |
| due_date       | date          | NOT NULL                        |                                                                                                                                             |
| amount         | numeric(15,2) | NOT NULL                        |                                                                                                                                             |
| status         | varchar(20)   | NOT NULL, DEFAULT 'unpaid'      | enum: unpaid, paid, void                                                                                                                    |
| paid_at        | timestamptz   | NULL                            |                                                                                                                                             |
| created_at     | timestamptz   | NOT NULL                        |                                                                                                                                             |
| updated_at     | timestamptz   | NOT NULL                        |                                                                                                                                             |

Index: `invoice_number` (unique), `sales_order_id`, `status` (untuk filter unpaid invoice).

### 3.7 `payments`

Pembayaran masuk dari customer terhadap invoice. Karena kontrak dibayar sekaligus (bukan termin), satu invoice idealnya satu payment, tapi tabel tetap dirancang one-to-many untuk menampung kasus partial payment atau pembayaran lebih dari sekali secara administratif.

**Kepemilikan module**: migration dan Model `Payment` tinggal di `app/Modules/Finance`, sama seperti `Invoice` (lihat catatan Section 3.6, ASUMSI 6).

| Column           | Type          | Constraint                  | Keterangan          |
| ---------------- | ------------- | --------------------------- | ------------------- |
| id               | bigserial     | PK                          |                     |
| invoice_id       | bigint        | FK -> invoices.id, NOT NULL |                     |
| payment_date     | date          | NOT NULL                    |                     |
| amount           | numeric(15,2) | NOT NULL                    |                     |
| payment_method   | varchar(50)   | NULL                        | transfer, cash, dst |
| reference_number | varchar(100)  | NULL                        |                     |
| created_at       | timestamptz   | NOT NULL                    |                     |

---

## 4. Sales & Inventory

### 4.1 `customers`

| Column        | Type         | Constraint | Keterangan                  |
| ------------- | ------------ | ---------- | --------------------------- |
| id            | bigserial    | PK         |                             |
| name          | varchar(255) | NOT NULL   |                             |
| customer_type | varchar(20)  | NOT NULL   | enum: individual, corporate |
| npwp          | varchar(20)  | NULL       |                             |
| address       | text         | NULL       |                             |
| phone         | varchar(50)  | NULL       |                             |
| email         | varchar(255) | NULL       |                             |
| created_at    | timestamptz  | NOT NULL   |                             |
| updated_at    | timestamptz  | NOT NULL   |                             |

### 4.2 `item_categories`

| Column | Type         | Constraint | Keterangan |
| ------ | ------------ | ---------- | ---------- |
| id     | bigserial    | PK         |            |
| name   | varchar(255) | NOT NULL   |            |

### 4.3 `items`

Master data produk/jasa. Field `item_type` menentukan apakah stock tracking berlaku.

| Column           | Type          | Constraint                     | Keterangan                      |
| ---------------- | ------------- | ------------------------------ | ------------------------------- |
| id               | bigserial     | PK                             |                                 |
| sku              | varchar(50)   | UNIQUE, NOT NULL               |                                 |
| name             | varchar(255)  | NOT NULL                       |                                 |
| item_type        | varchar(20)   | NOT NULL                       | enum: physical_good, service    |
| item_category_id | bigint        | FK -> item_categories.id, NULL |                                 |
| unit_of_measure  | varchar(20)   | NOT NULL                       | pcs, unit, package, dst         |
| unit_price       | numeric(15,2) | NOT NULL                       | harga jual                      |
| cost_price       | numeric(15,2) | NULL                           | harga modal, null untuk service |
| is_active        | boolean       | NOT NULL, DEFAULT true         |                                 |
| created_at       | timestamptz   | NOT NULL                       |                                 |
| updated_at       | timestamptz   | NOT NULL                       |                                 |

Index: `sku` (unique), `item_type` (untuk filter saat load item yang butuh stock check).

### 4.4 `stock_levels`

Single location stock, sesuai scope MVP (bukan multi-warehouse).

| Column            | Type          | Constraint                       | Keterangan                             |
| ----------------- | ------------- | -------------------------------- | -------------------------------------- |
| id                | bigserial     | PK                               |                                        |
| item_id           | bigint        | FK -> items.id, UNIQUE, NOT NULL |                                        |
| quantity_on_hand  | numeric(15,2) | NOT NULL, DEFAULT 0              |                                        |
| quantity_reserved | numeric(15,2) | NOT NULL, DEFAULT 0              | dipesan sales order tapi belum shipped |
| updated_at        | timestamptz   | NOT NULL                         |                                        |

Constraint aplikasi: row ini hanya boleh ada untuk item dengan `item_type = physical_good`. Di-enforce di `InventoryService`, bukan database constraint, karena PostgreSQL tidak bisa cross-table CHECK constraint langsung.

**Release reservation (keputusan M2, ASUMSI 8)**: saat Sales Order di-cancel, `InventoryService::releaseReservedStock()` mengurangi `quantity_reserved` langsung di row ini. Ini **bukan** physical movement (barang tidak pernah keluar gudang), jadi **tidak** menghasilkan row baru di `stock_movements` — cukup update kolom `quantity_reserved` saja. Lihat catatan Section 4.5 untuk kenapa batas ini penting dijaga.

**Fulfill reservation saat completeOrder (keputusan M2, bug ditemukan lewat test 2.24)**: saat Sales Order di-complete, `InventoryService::fulfillReservedStock()` mengurangi `quantity_on_hand` **dan** `quantity_reserved` sekaligus dalam satu transaction — berbeda dari `releaseReservedStock()`, di sini reservasi **dikonsumsi** (stok benar-benar keluar), bukan dilepas kembali ke pool. Draf awal `completeOrder()` sempat cuma panggil `decreaseStock()` (yang tidak menyentuh `quantity_reserved` sama sekali), menyebabkan `quantity_reserved` nyangkut permanen setiap kali order completed — ditangkap lewat feature test sebelum sempat masuk production, bukan lewat verifikasi manual.

### 4.5 `stock_movements`

Log seluruh perubahan stok **fisik**, baik dari sales maupun manual adjustment. Tabel ini murni jejak pergerakan barang yang benar-benar keluar/masuk gudang, **bukan** jejak perubahan reservasi. Release reservation dari Sales Order yang di-cancel (lihat Section 4.4) sengaja **tidak** dicatat di sini, karena stok fisik tidak pernah berpindah — kalau nanti ada kebutuhan audit trail untuk reservation lifecycle itu sendiri (dipesan/dilepas), itu domain terpisah dari `stock_movements`, jangan dipaksakan lewat `movement_type` baru semacam `reservation_release` yang mencampur physical movement dengan reservation bookkeeping.

| Column         | Type          | Constraint               | Keterangan                                                                         |
| -------------- | ------------- | ------------------------ | ---------------------------------------------------------------------------------- |
| id             | bigserial     | PK                       |                                                                                    |
| item_id        | bigint        | FK -> items.id, NOT NULL |                                                                                    |
| movement_type  | varchar(20)   | NOT NULL                 | enum: sale_out, adjustment_in, adjustment_out                                      |
| quantity       | numeric(15,2) | NOT NULL                 | selalu positif, arah ditentukan `movement_type`                                    |
| reference_type | varchar(255)  | NULL                     | misal `SalesOrder`                                                                 |
| reference_id   | bigint        | NULL                     |                                                                                    |
| reason_code    | varchar(50)   | NULL                     | **NOT NULL kalau movement_type adjustment_in/adjustment_out** (lihat Assumption 3) |
| note           | text          | NULL                     |                                                                                    |
| created_by     | bigint        | FK -> users.id, NOT NULL |                                                                                    |
| created_at     | timestamptz   | NOT NULL                 |                                                                                    |

Constraint aplikasi: `reason_code` wajib diisi kalau `movement_type` termasuk adjustment, dicek di `InventoryService`, bukan database CHECK constraint biasa (butuh conditional logic).

Index: `item_id`, composite `(reference_type, reference_id)`.

### 4.6 `sales_orders`

Satu row merepresentasikan satu kontrak yang dibayar sekaligus.

**Format `order_number` (keputusan M2)**: `SO-{YYYY}-{6 digit sequential}` misal `SO-2026-000001`, sequence reset tiap tahun, digenerate lewat trait shared `GeneratesSequentialNumber` (lihat ARCHITECTURE.md Section 4a), konsisten dengan `entry_number` (Section 3.2) dan `invoice_number` (Section 3.6).

**Aturan transisi status ke `cancelled` (keputusan M2, ASUMSI 8)**: hanya valid dari `draft`. **Tidak** bisa cancel dari `completed` — itu butuh return/refund flow yang di luar scope MVP (lihat PRD.md Section 2.3). `SalesOrderService::cancelOrder()` men-guard transisi ini di level aplikasi, melepas stock reservation lewat `InventoryService::releaseReservedStock()` untuk tiap item `physical_good` di order tersebut.

| Column        | Type          | Constraint                              | Keterangan                                                                                                                             |
| ------------- | ------------- | --------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| id            | bigserial     | PK                                      |                                                                                                                                        |
| order_number  | varchar(100)  | UNIQUE, NOT NULL                        | format `SO-{YYYY}-{6 digit sequential}`                                                                                                |
| customer_id   | bigint        | FK -> customers.id, NOT NULL            |                                                                                                                                        |
| order_date    | date          | NOT NULL                                |                                                                                                                                        |
| status        | varchar(20)   | NOT NULL, DEFAULT 'draft'               | enum: draft, invoiced, completed, cancelled (`confirmed` dihapus dari flow M2, lihat ASUMSI 8 — tidak pernah di-set oleh kode manapun) |
| total_amount  | numeric(15,2) | NOT NULL                                |                                                                                                                                        |
| cancel_reason | text          | NULL, NOT NULL kalau status = cancelled | alasan wajib diisi saat cancel, dicek di level aplikasi seperti `void_reason` di `journal_entries`                                     |
| created_by    | bigint        | FK -> users.id, NOT NULL                |                                                                                                                                        |
| created_at    | timestamptz   | NOT NULL                                |                                                                                                                                        |
| updated_at    | timestamptz   | NOT NULL                                |                                                                                                                                        |

Index: `order_number` (unique), `customer_id`, `status`.

### 4.7 `sales_order_items`

| Column         | Type          | Constraint                      | Keterangan                                                                                      |
| -------------- | ------------- | ------------------------------- | ----------------------------------------------------------------------------------------------- |
| id             | bigserial     | PK                              |                                                                                                 |
| sales_order_id | bigint        | FK -> sales_orders.id, NOT NULL |                                                                                                 |
| item_id        | bigint        | FK -> items.id, NOT NULL        |                                                                                                 |
| quantity       | numeric(15,2) | NOT NULL                        |                                                                                                 |
| unit_price     | numeric(15,2) | NOT NULL                        | snapshot harga saat order dibuat, tidak mengikuti perubahan `items.unit_price` di kemudian hari |
| subtotal       | numeric(15,2) | NOT NULL                        |                                                                                                 |

Index: `sales_order_id`.

---

## 5. Relasi Antar Module (Ringkasan)

Relasi lintas module **tidak** direpresentasikan sebagai foreign key langsung antar tabel di module berbeda kalau bisa dihindari, kecuali kasus yang memang butuh referential integrity ketat (misalnya `invoices.sales_order_id`, karena invoice memang secara definisi selalu berasal dari satu sales order).

Ringkasan dependency:

- `employees.user_id` -> `users.id` (HR bergantung ke Identity untuk employee yang punya akses login).
- `payroll_runs` men-trigger domain event `PayrollProcessed`, ditangkap Finance module untuk membuat `journal_entries` (beban gaji, utang BPJS, utang PPh21). Tidak ada FK langsung dari `payroll_runs` ke `journal_entries`, keterkaitan hanya lewat `journal_entries.reference_type/reference_id`.
- `sales_orders` selesai men-trigger domain event `SalesOrderCompleted`, ditangkap Finance module untuk membuat `invoices` dan `journal_entries` terkait pendapatan.
- `invoices.sales_order_id` -> `sales_orders.id` adalah FK langsung karena relasi ini inheren 1-to-1 by definition, bukan cross-module coupling yang perlu dihindari.
- `stock_movements.reference_id` menyimpan `sales_order_id` saat movement asalnya dari sales, tapi lewat polymorphic reference (`reference_type` + `reference_id`), bukan FK langsung, supaya `InventoryService` tetap reusable untuk sumber movement lain (Purchasing di fase berikutnya) tanpa perlu tambah kolom FK baru tiap kali ada module baru yang menyentuh stock.

## 6. Soft Delete Policy

Soft delete (`deleted_at`) dipakai untuk entity yang punya riwayat transaksi terkait dan tidak boleh hilang jejaknya: `users`, `employees`, `customers`, `items`. Tabel transaksional (`sales_orders`, `invoices`, `journal_entries`, `payroll_runs`) tidak pakai soft delete karena secara bisnis tidak boleh dihapus sama sekali, hanya bisa di-void lewat kolom `status`.

## 7. Precision Numeric

Seluruh kolom uang pakai `numeric(15,2)`, bukan `float`/`double`, untuk menghindari rounding error pada operasi aritmatika finansial. Ini non-negotiable untuk data finansial.

---

---

## Appendix A: Seed Data TER Bulanan (PMK 168/2023)

Data berikut adalah seed data resmi untuk `ter_categories`, `ptkp_ter_mapping`, dan `ter_rates`. Berlaku sejak `effective_date = 2024-01-01`, `end_date = NULL`. Ini bukan placeholder, langsung dipakai untuk seeder migration.

### A.1 `ter_categories`

| code | description                                   |
| ---- | --------------------------------------------- |
| A    | TER Bulanan Kategori A (TK/0, TK/1, K/0)      |
| B    | TER Bulanan Kategori B (TK/2, TK/3, K/1, K/2) |
| C    | TER Bulanan Kategori C (K/3)                  |

### A.2 `ptkp_ter_mapping`

| ptkp_status | ter_category_code |
| ----------- | ----------------- |
| TK0         | A                 |
| TK1         | A                 |
| K0          | A                 |
| TK2         | B                 |
| TK3         | B                 |
| K1          | B                 |
| K2          | B                 |
| K3          | C                 |

### A.3 `ter_rates` - Kategori A (TK/0, TK/1, K/0)

| income_lower_bound | income_upper_bound | rate_percentage |
| ------------------ | ------------------ | --------------- |
| 0.00               | 5400000.00         | 0.00            |
| 5400000.01         | 5650000.00         | 0.25            |
| 5650000.01         | 5950000.00         | 0.50            |
| 5950000.01         | 6300000.00         | 0.75            |
| 6300000.01         | 6750000.00         | 1.00            |
| 6750000.01         | 7500000.00         | 1.25            |
| 7500000.01         | 8550000.00         | 1.50            |
| 8550000.01         | 9650000.00         | 1.75            |
| 9650000.01         | 10050000.00        | 2.00            |
| 10050000.01        | 10350000.00        | 2.25            |
| 10350000.01        | 10700000.00        | 2.50            |
| 10700000.01        | 11050000.00        | 3.00            |
| 11050000.01        | 11600000.00        | 3.50            |
| 11600000.01        | 12500000.00        | 4.00            |
| 12500000.01        | 13750000.00        | 5.00            |
| 13750000.01        | 15100000.00        | 6.00            |
| 15100000.01        | 16950000.00        | 7.00            |
| 16950000.01        | 19750000.00        | 8.00            |
| 19750000.01        | 24150000.00        | 9.00            |
| 24150000.01        | 26450000.00        | 10.00           |
| 26450000.01        | 28000000.00        | 11.00           |
| 28000000.01        | 30050000.00        | 12.00           |
| 30050000.01        | 32400000.00        | 13.00           |
| 32400000.01        | 35400000.00        | 14.00           |
| 35400000.01        | 39100000.00        | 15.00           |
| 39100000.01        | 43850000.00        | 16.00           |
| 43850000.01        | 47800000.00        | 17.00           |
| 47800000.01        | 51400000.00        | 18.00           |
| 51400000.01        | 56300000.00        | 19.00           |
| 56300000.01        | 62200000.00        | 20.00           |
| 62200000.01        | 68600000.00        | 21.00           |
| 68600000.01        | 77500000.00        | 22.00           |
| 77500000.01        | 89000000.00        | 23.00           |
| 89000000.01        | 103000000.00       | 24.00           |
| 103000000.01       | 125000000.00       | 25.00           |
| 125000000.01       | 157000000.00       | 26.00           |
| 157000000.01       | 206000000.00       | 27.00           |
| 206000000.01       | 337000000.00       | 28.00           |
| 337000000.01       | 454000000.00       | 29.00           |
| 454000000.01       | 550000000.00       | 30.00           |
| 550000000.01       | 695000000.00       | 31.00           |
| 695000000.01       | 910000000.00       | 32.00           |
| 910000000.01       | 1400000000.00      | 33.00           |
| 1400000000.01      | NULL               | 34.00           |

### A.4 `ter_rates` - Kategori B (TK/2, TK/3, K/1, K/2)

| income_lower_bound | income_upper_bound | rate_percentage |
| ------------------ | ------------------ | --------------- |
| 0.00               | 6200000.00         | 0.00            |
| 6200000.01         | 6500000.00         | 0.25            |
| 6500000.01         | 6850000.00         | 0.50            |
| 6850000.01         | 7300000.00         | 0.75            |
| 7300000.01         | 9200000.00         | 1.00            |
| 9200000.01         | 10750000.00        | 1.50            |
| 10750000.01        | 11250000.00        | 2.00            |
| 11250000.01        | 11600000.00        | 2.50            |
| 11600000.01        | 12600000.00        | 3.00            |
| 12600000.01        | 13600000.00        | 4.00            |
| 13600000.01        | 14950000.00        | 5.00            |
| 14950000.01        | 16400000.00        | 6.00            |
| 16400000.01        | 18450000.00        | 7.00            |
| 18450000.01        | 21850000.00        | 8.00            |
| 21850000.01        | 26000000.00        | 9.00            |
| 26000000.01        | 27700000.00        | 10.00           |
| 27700000.01        | 29350000.00        | 11.00           |
| 29350000.01        | 31450000.00        | 12.00           |
| 31450000.01        | 33950000.00        | 13.00           |
| 33950000.01        | 37100000.00        | 14.00           |
| 37100000.01        | 41100000.00        | 15.00           |
| 41100000.01        | 45800000.00        | 16.00           |
| 45800000.01        | 49500000.00        | 17.00           |
| 49500000.01        | 53800000.00        | 18.00           |
| 53800000.01        | 58500000.00        | 19.00           |
| 58500000.01        | 64000000.00        | 20.00           |
| 64000000.01        | 71000000.00        | 21.00           |
| 71000000.01        | 80000000.00        | 22.00           |
| 80000000.01        | 93000000.00        | 23.00           |
| 93000000.01        | 109000000.00       | 24.00           |
| 109000000.01       | 129000000.00       | 25.00           |
| 129000000.01       | 163000000.00       | 26.00           |
| 163000000.01       | 211000000.00       | 27.00           |
| 211000000.01       | 344000000.00       | 28.00           |
| 344000000.01       | 463000000.00       | 29.00           |
| 463000000.01       | 561000000.00       | 30.00           |
| 561000000.01       | 709000000.00       | 31.00           |
| 709000000.01       | 965000000.00       | 32.00           |
| 965000000.01       | 1419000000.00      | 33.00           |
| 1419000000.01      | NULL               | 34.00           |

### A.5 `ter_rates` - Kategori C (K/3)

| income_lower_bound | income_upper_bound | rate_percentage |
| ------------------ | ------------------ | --------------- |
| 0.00               | 6600000.00         | 0.00            |
| 6600000.01         | 6950000.00         | 0.25            |
| 6950000.01         | 7350000.00         | 0.50            |
| 7350000.01         | 7800000.00         | 0.75            |
| 7800000.01         | 8850000.00         | 1.00            |
| 8850000.01         | 9800000.00         | 1.25            |
| 9800000.01         | 10950000.00        | 1.50            |
| 10950000.01        | 11200000.00        | 1.75            |
| 11200000.01        | 12050000.00        | 2.00            |
| 12050000.01        | 12950000.00        | 3.00            |
| 12950000.01        | 14150000.00        | 4.00            |
| 14150000.01        | 15550000.00        | 5.00            |
| 15550000.01        | 17050000.00        | 6.00            |
| 17050000.01        | 19500000.00        | 7.00            |
| 19500000.01        | 22700000.00        | 8.00            |
| 22700000.01        | 26600000.00        | 9.00            |
| 26600000.01        | 28100000.00        | 10.00           |
| 28100000.01        | 30100000.00        | 11.00           |
| 30100000.01        | 32200000.00        | 12.00           |
| 32200000.01        | 34800000.00        | 13.00           |
| 34800000.01        | 38000000.00        | 14.00           |
| 38000000.01        | 42050000.00        | 15.00           |
| 42050000.01        | 46700000.00        | 16.00           |
| 46700000.01        | 50700000.00        | 17.00           |
| 50700000.01        | 54800000.00        | 18.00           |
| 54800000.01        | 59700000.00        | 19.00           |
| 59700000.01        | 65200000.00        | 20.00           |
| 65200000.01        | 72200000.00        | 21.00           |
| 72200000.01        | 81400000.00        | 22.00           |
| 81400000.01        | 94600000.00        | 23.00           |
| 94600000.01        | 110200000.00       | 24.00           |
| 110200000.01       | 130100000.00       | 25.00           |
| 130100000.01       | 164400000.00       | 26.00           |
| 164400000.01       | 211800000.00       | 27.00           |
| 211800000.01       | 345200000.00       | 28.00           |
| 345200000.01       | 464700000.00       | 29.00           |
| 464700000.01       | 561800000.00       | 30.00           |
| 561800000.01       | 709700000.00       | 31.00           |
| 709700000.01       | 965600000.00       | 32.00           |
| 965600000.01       | 1419400000.00      | 33.00           |
| 1419400000.01      | NULL               | 34.00           |

### A.6 TER Harian (Out of Scope MVP, referensi untuk Fase 2, lihat Section 2.9)

Berlaku untuk pegawai tidak tetap yang dibayar harian, skema berbeda struktur dari TER Bulanan di atas:

| Kondisi                                       | Perlakuan                                                                                |
| --------------------------------------------- | ---------------------------------------------------------------------------------------- |
| Penghasilan bruto harian s.d. Rp 2.500.000    | Rate 0.5%                                                                                |
| Penghasilan bruto harian di atas Rp 2.500.000 | Tarif Pasal 17 ayat (1) huruf a UU PPh, dikenakan atas 50% dari penghasilan bruto harian |

Referensi: PMK 168/2023 Pasal 13(2)b.

---

## Appendix B: Seed Data BPJS Rates (2026)

Sumber: rincian iuran BPJS Kesehatan dan BPJS Ketenagakerjaan 2026 yang kamu berikan. `effective_date = 2026-01-01` untuk seluruh baris di bawah kecuali disebutkan lain.

| bpjs_type | rate_employee_percentage | rate_company_percentage                 | max_wage_base          | Catatan                                                                                                                                                                                                                                                                                                                                                                                                                    |
| --------- | ------------------------ | --------------------------------------- | ---------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| kesehatan | 1.00                     | 4.00                                    | 12000000.00            | Total iuran 5% dari upah bulanan, dibatasi maksimal dihitung dari Rp12.000.000/bulan meskipun gaji aktual lebih tinggi.                                                                                                                                                                                                                                                                                                    |
| jht       | 2.00                     | 3.70                                    | NULL                   | Jaminan Hari Tua, total 5.7%.                                                                                                                                                                                                                                                                                                                                                                                              |
| jp        | 1.00                     | 2.00                                    | **belum dikonfirmasi** | Jaminan Pensiun, total 3%. Sumber yang kamu berikan tidak menyebutkan batas atas upah untuk JP. Program JP historisnya punya batas atas upah yang disesuaikan berkala oleh pemerintah, **wajib dicek ke sumber resmi BPJS Ketenagakerjaan saat implementasi**, jangan diasumsikan tanpa batas begitu saja.                                                                                                                 |
| jkm       | 0.00                     | 0.30                                    | NULL                   | Jaminan Kematian, sepenuhnya ditanggung perusahaan.                                                                                                                                                                                                                                                                                                                                                                        |
| jkk       | 0.00                     | **0.24 (asumsi kelas risiko terendah)** | NULL                   | Jaminan Kecelakaan Kerja, sepenuhnya ditanggung perusahaan. Rate resmi berkisar 0.24%-1.74% tergantung kelas risiko pekerjaan yang terdaftar. **0.24% dipakai sebagai default asumsi** karena profil perusahaan IT service/office-based biasanya masuk kelas risiko sangat rendah, tapi ini **wajib dikonfirmasi resmi** ke BPJS Ketenagakerjaan saat pendaftaran perusahaan, bukan diasumsikan permanen dari dokumen ini. |

**Dua item yang masih perlu dikonfirmasi sebelum M3 dianggap benar-benar selesai** (bukan lagi blocker penuh karena sebagian besar data sudah ada, tapi dua angka spesifik ini tetap perlu diverifikasi):

1. Kelas risiko JKK resmi yang terdaftar untuk perusahaan ini (kalau bukan kelas terendah, rate 0.24% di atas perlu diganti sesuai kelas risiko aktual).
2. Batas atas upah (wage cap) untuk perhitungan JP di tahun 2026, kalau memang ada.

## Appendix C: Seed Data Chart of Accounts

Diadaptasi dari struktur CoA perusahaan jasa (referensi: accurate.id), disesuaikan untuk perusahaan IT service & consulting yang juga menjual barang fisik (bukan jasa murni), sehingga ditambah akun persediaan dan HPP yang tidak ada di template jasa standar. Struktur numerik 3 digit: digit pertama menunjukkan grup utama (1xx aset, 2xx liabilitas, 3xx ekuitas, 4xx pendapatan, 5xx beban), kode header (grup) memakai kelipatan puluhan/ratusan dengan `is_postable = false`, akun leaf di bawahnya `is_postable = true`.

Ini seed data awal yang wajar untuk mulai development, bukan hasil audit dari akuntan. Sebelum go-live produksi, tetap direview oleh pihak yang paham akuntansi perusahaan ini, terutama urutan dan penamaan yang mungkin perlu disesuaikan kebiasaan internal.

### Aset (asset)

| code | name                                             | parent_code | is_postable |
| ---- | ------------------------------------------------ | ----------- | ----------- |
| 100  | Aktiva Lancar                                    | -           | false       |
| 101  | Kas                                              | 100         | true        |
| 102  | Bank                                             | 100         | true        |
| 103  | Piutang Usaha                                    | 100         | true        |
| 104  | Penyisihan Piutang Tak Tertagih                  | 100         | true        |
| 105  | Persediaan Barang Dagang                         | 100         | true        |
| 106  | Perlengkapan Kantor                              | 100         | true        |
| 107  | Sewa Dibayar Dimuka                              | 100         | true        |
| 108  | Asuransi Dibayar Dimuka                          | 100         | true        |
| 109  | PPN Masukan                                      | 100         | true        |
| 110  | Aktiva Tetap                                     | -           | false       |
| 111  | Peralatan Kantor                                 | 110         | true        |
| 112  | Akumulasi Penyusutan Peralatan Kantor            | 110         | true        |
| 113  | Kendaraan                                        | 110         | true        |
| 114  | Akumulasi Penyusutan Kendaraan                   | 110         | true        |
| 115  | Peralatan Komputer & Server                      | 110         | true        |
| 116  | Akumulasi Penyusutan Peralatan Komputer & Server | 110         | true        |
| 120  | Aktiva Tidak Berwujud                            | -           | false       |
| 121  | Lisensi Software                                 | 120         | true        |
| 122  | Akumulasi Amortisasi Lisensi Software            | 120         | true        |

### Liabilitas (liability)

| code | name                       | parent_code | is_postable |
| ---- | -------------------------- | ----------- | ----------- |
| 200  | Kewajiban Lancar           | -           | false       |
| 201  | Utang Usaha                | 200         | true        |
| 202  | Utang Gaji                 | 200         | true        |
| 203  | Utang PPh21                | 200         | true        |
| 204  | Utang BPJS Kesehatan       | 200         | true        |
| 205  | Utang BPJS Ketenagakerjaan | 200         | true        |
| 206  | PPN Keluaran               | 200         | true        |
| 207  | Pendapatan Diterima Dimuka | 200         | true        |
| 210  | Kewajiban Jangka Panjang   | -           | false       |
| 211  | Utang Bank Jangka Panjang  | 210         | true        |

### Ekuitas (equity)

| code | name          | parent_code | is_postable |
| ---- | ------------- | ----------- | ----------- |
| 300  | Ekuitas       | -           | false       |
| 301  | Modal Pemilik | 300         | true        |
| 302  | Prive         | 300         | true        |
| 303  | Laba Ditahan  | 300         | true        |

### Pendapatan (revenue)

| code | name                                  | parent_code | is_postable |
| ---- | ------------------------------------- | ----------- | ----------- |
| 400  | Pendapatan                            | -           | false       |
| 401  | Pendapatan Jasa Konsultasi IT         | 400         | true        |
| 402  | Pendapatan Penjualan Barang Teknologi | 400         | true        |
| 403  | Pendapatan Lain-lain                  | 400         | true        |

### Beban (expense)

| code | name                                         | parent_code | is_postable |
| ---- | -------------------------------------------- | ----------- | ----------- |
| 500  | Beban Pokok Penjualan                        | -           | false       |
| 501  | Harga Pokok Penjualan Barang                 | 500         | true        |
| 510  | Beban Operasional                            | -           | false       |
| 511  | Beban Gaji dan Tunjangan                     | 510         | true        |
| 512  | Beban BPJS Kesehatan (Perusahaan)            | 510         | true        |
| 513  | Beban BPJS Ketenagakerjaan (Perusahaan)      | 510         | true        |
| 514  | Beban Sewa Kantor                            | 510         | true        |
| 515  | Beban Listrik, Air, dan Internet             | 510         | true        |
| 516  | Beban Perlengkapan Kantor                    | 510         | true        |
| 517  | Beban Penyusutan Peralatan Kantor            | 510         | true        |
| 518  | Beban Penyusutan Kendaraan                   | 510         | true        |
| 519  | Beban Penyusutan Peralatan Komputer & Server | 510         | true        |
| 520  | Beban Pemasaran                              | 510         | true        |
| 521  | Beban Perjalanan Dinas                       | 510         | true        |
| 522  | Beban Lain-lain Operasional                  | 510         | true        |
| 530  | Beban Non-Operasional                        | -           | false       |
| 531  | Beban Bunga                                  | 530         | true        |
| 532  | Beban Administrasi Bank                      | 530         | true        |

**Catatan pemetaan ke transaksi otomatis** (dipakai saat menulis listener `CreateJournalEntryFromSalesOrder` dan `CreateJournalEntryFromPayroll` di M2/M3, dan `VendorBillService` di M1):

- **Sales Order completed, termasuk order campuran barang+jasa (keputusan M2)**: satu `sales_order` bisa berisi campuran `sales_order_items` dengan `item_type` berbeda (barang fisik dan jasa dalam satu order/kontrak yang sama), schema tidak melarang ini. Listener **wajib** melakukan grouping per `item_type` dulu sebelum membangun baris jurnal, bukan asumsi satu order = satu jenis item:
    1. Jumlahkan `subtotal` seluruh `sales_order_items` dengan `item_type = physical_good` → kredit 402 (Pendapatan Penjualan Barang Teknologi) sebesar total ini, kalau > 0.
    2. Jumlahkan `subtotal` seluruh `sales_order_items` dengan `item_type = service` → kredit 401 (Pendapatan Jasa Konsultasi IT) sebesar total ini, kalau > 0.
    3. Satu baris debit 103 (Piutang Usaha) sebesar jumlah kedua total di atas (piutang digabung, tidak dipecah per item_type).
    4. Kalau ada item `physical_good` dalam order (`total HPP > 0`): tambah pasangan baris debit 501 (Harga Pokok Penjualan Barang), kredit 105 (Persediaan Barang Dagang) sebesar `cost_price` dikali quantity, dijumlahkan lintas seluruh item fisik di order tersebut (perpetual, lihat ASUMSI 5 di Section 0).

    Tanpa grouping ini, jurnal tetap balance secara total debit=credit (`JournalEntryService::createEntry()` tidak menolaknya), tapi alokasi ke akun pendapatan 401 vs 402 bisa salah untuk order campuran — bug ini tidak terdeteksi otomatis dan baru kelihatan saat laporan laba rugi per akun sudah salah, jauh setelah entry di-posting (dan sudah immutable, koreksi cuma lewat void manual).

- Payroll processed: debit 511 (Beban Gaji), debit 512/513 (Beban BPJS Perusahaan), kredit 202 (Utang Gaji) atau langsung kredit 101/102 kalau net pay langsung dibayar, kredit 203 (Utang PPh21), kredit 204/205 (Utang BPJS).
- Vendor bill dibuat (M1, accrual basis): debit `vendor_bills.account_id` (akun beban/aset sesuai bill), kredit 201 (Utang Usaha) sebesar `amount`.
- Vendor bill dibayar (status → paid): debit 201 (Utang Usaha), kredit 101/102 (Kas/Bank) sebesar `amount`.
- **Invoice dibayar (keputusan M2, ditambal karena tidak eksplisit ada di spesifikasi awal task 2.23)**: debit 101/102 (Kas/Bank, dipilih dari `payment_method` — `cash` ke 101, selain itu default 102), kredit 103 (Piutang Usaha) sebesar `payment.amount`. Jurnal dibuat **per payment**, bukan cuma saat invoice full paid — beda dari vendor bill (AP) yang cuma toggle status tanpa payment detail terpisah (DATABASE.md Section 3.5), karena `payments` di sisi AR memang dirancang mendukung partial payment (Section 3.7), jadi tiap penerimaan kas perlu tercatat di tanggal transaksinya masing-masing untuk akurasi buku besar. **Asumsi belum dikonfirmasi**: pemilihan akun 101 vs 102 berdasarkan `payment_method`, perlu direview kalau perusahaan punya konvensi berbeda.

---

Dokumen terkait: `PRD.md`, `ARCHITECTURE.md`, `DESIGN.md`, `ROADMAP.md`, `TASKS.md`.
