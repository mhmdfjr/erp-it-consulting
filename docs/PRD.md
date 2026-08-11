# Product Requirements Document (PRD)

## Sistem ERP - Perusahaan IT Service & Consulting

Status: Draft v1.0
Terakhir diperbarui: 2026-08-09 (revisi M3 planning)

---

## 1. Latar Belakang dan Tujuan

Perusahaan bergerak di bidang IT service and consulting, menyediakan barang dan jasa teknologi bagi pelaku bisnis dan korporat di Indonesia. Saat ini perusahaan membutuhkan sistem ERP internal untuk mengelola operasional inti: identity/user management, sumber daya manusia, keuangan, dan penjualan-inventori, dalam satu platform yang terintegrasi.

Tujuan utama pembangunan sistem ini:

- Menggantikan proses manual/spreadsheet dengan sistem tersentralisasi yang punya single source of truth untuk master data (user, employee, customer, produk/jasa, chart of accounts).
- Menyediakan fondasi arsitektur yang bisa bertumbuh: modul baru bisa ditambahkan tanpa merombak modul yang sudah ada.
- Mendukung kebutuhan integrasi eksternal di masa depan (payment gateway, e-Faktur, perbankan) tanpa perlu redesign arsitektur.

## 2. Scope

### 2.1 Business Context

- Single company, single entity legal, tidak ada multi-branch/multi-tenant di scope saat ini.
- Model bisnis: menjual barang teknologi (hardware/software, dengan atau tanpa stok, tergantung skema pass-through vs stok sendiri) dan jasa konsultasi.
- Jasa konsultasi dijual dengan skema **fixed price per kontrak**, dibayar **sekaligus** (bukan termin/milestone). Konsekuensinya: tidak dibutuhkan modul timesheet atau contract-milestone billing di MVP ini.

### 2.2 In Scope (MVP)

Empat modul berikut masuk MVP, dengan detail scope per modul di Section 4.

1. Identity & Access Management + Master Data (Super Admin)
2. Human Resources (termasuk Payroll dengan monhtly PPh21)
3. Finance & Accounting
4. Sales & Inventory (digabung sebagai satu module folder dengan service boundary terpisah)

### 2.3 Out of Scope (MVP ini, kandidat fase berikutnya)

- **PPh21 annual reconciliation** (rekonsiliasi tahunan Desember dengan tarif progresif 5/15/25/30/35% dan PTKP tahunan). Monthly withholding pakai TER **masuk MVP** (lihat Section 4.2), tapi proses rekonsiliasi akhir tahun didefer ke fase berikutnya. Untuk sementara, adjustment gaji Desember dihitung manual di luar sistem.
- **TER Harian untuk pegawai tidak tetap** (pegawai yang dibayar per hari kerja, bukan bulanan). MVP hanya mendukung payroll untuk pegawai tetap bulanan dengan skema TER Bulanan. Kalau di kemudian hari ada kebutuhan pegawai harian/lepas, ini masuk fase 2.
- **Purchasing/Procurement module** sebagai modul formal (PO ke vendor, goods receipt tercatat resmi). Untuk MVP, restock inventory dicatat manual sebagai stock adjustment.
- **CRM** (lead pipeline, follow-up management). Sales module MVP fokus ke transactional order, bukan pre-sales pipeline.
- **Manufacturing/Production** (BOM, work order). Tidak relevan, perusahaan tidak melakukan manufaktur.
- **Approval workflow / multi-level approval** pada Sales Order, Purchase, atau Invoice. Flow MVP bersifat linear tanpa approval gate.
- **Multi-branch / multi-company** support.
- **Timesheet & time tracking**. Tidak dibutuhkan karena billing fixed price per kontrak, bukan time & material.
- **Integrasi eksternal** (payment gateway, e-Faktur, perbankan/VA otomatis). Dijadwalkan setelah MVP empat modul stabil.
- **Return/refund flow untuk Sales Order yang sudah `completed`** (keputusan M2). Cancel Order di MVP cuma berlaku untuk status `draft`, sebelum stok direalisasi keluar dan invoice terbit. Order yang sudah completed (stok sudah keluar gudang, invoice sudah terbit) tidak bisa dibatalkan lewat sistem — kalau ada kebutuhan pembatalan pasca-completed (barang dikembalikan, kontrak jasa dibatalkan setelah invoice terbit), itu perlu return/refund flow dengan jurnal reversal yang eksplisit, didefer ke fase 2, bukan dianggap gap yang lupa dikerjakan di M2.

Keputusan out-of-scope ini eksplisit supaya tidak ada scope creep saat development jalan. Kalau ada kebutuhan yang muncul dari salah satu poin di atas selama development, itu sinyal untuk update PRD ini dulu, bukan langsung diimplementasikan diam-diam.

## 3. Assumptions dan Constraints

- Tech stack: Laravel + PostgreSQL, di environment Linux.
- Arsitektur: **modular monolith**, bukan microservices. Tiap domain adalah module folder terpisah (`app/Modules/{Domain}`) dengan model, migration, service class, dan route sendiri.
- **Tidak ada REST/JSON API layer terpisah.** Aplikasi full server-rendered lewat Blade (dengan Livewire untuk bagian yang butuh interaktivitas), karena tidak ada rencana SPA terpisah atau mobile app. Satu-satunya endpoint HTTP di luar halaman web adalah webhook receiver untuk integrasi eksternal (payment gateway callback, dsb), yang scope-nya sempit dan didefer ke fase integrasi eksternal, bukan REST API penuh dengan CRUD semua resource.
- Komunikasi antar module memakai **domain events**, bukan direct model call lintas module. Contoh: Sales Order selesai memicu event `SalesOrderCompleted` yang di-listen oleh Finance module untuk membuat journal entry, bukan Sales module langsung memanggil `JournalEntry::create()`.
- Single database untuk semua module di fase ini. Split database per module bukan kebutuhan saat ini dan tidak diantisipasi lebih awal dari yang dibutuhkan.
- Barang yang dijual: kombinasi stok sendiri dan pass-through vendor, direpresentasikan lewat item type pada Product/Service Catalog (`physical_good` vs `service`), bukan lewat dua sistem terpisah.
- Payroll di-desain dengan komponen (base salary, tunjangan tetap, potongan tetap termasuk BPJS dan PPh21) yang **dikonfigurasi**, bukan hardcode, supaya perubahan rate BPJS, tabel TER, atau struktur gaji tidak butuh perubahan kode.
- PPh21 monthly withholding memakai skema **TER (Tarif Efektif Rata-rata)** sesuai aturan berlaku sejak 2024, bukan hitung bracket progresif langsung tiap bulan. Tabel TER dan mapping PTKP status disimpan sebagai reference data yang bisa diperbarui tanpa mengubah kode aplikasi.
- Setiap employee punya PTKP status individual (TK/0, TK/1, K/0, K/1, K/2, K/3, dst.), bukan status seragam untuk semua karyawan. Status ini menentukan baris TER yang dipakai saat kalkulasi withholding bulanan.

## 4. Module List dan Functional Requirements

### 4.1 Identity & Access Management + Master Data (Super Admin)

Fungsi cross-cutting yang dipakai seluruh module lain, bukan module bisnis yang berdiri sejajar dengan HR/Finance/Sales.

**Functional requirements:**

- User management: create, update, deactivate user.
- Role-Based Access Control (RBAC): role dan permission dapat dikonfigurasi, bukan hardcode di kode aplikasi.
- Super Admin role memiliki akses melihat dan mengubah seluruh data lintas module, termasuk data yang secara default dibatasi untuk role lain.
- Master data management: company profile, system configuration/settings.
- Audit log: mencatat siapa mengubah apa dan kapan, minimal untuk create/update/delete pada entity kritikal (invoice, journal entry, payroll run, stock adjustment).

**Non-goals:** module ini tidak menyimpan business data milik module lain (misalnya tidak menyimpan data invoice), ia hanya menyediakan layer akses dan konfigurasi.

### 4.2 Human Resources (termasuk Payroll)

**Functional requirements:**

- Employee master data: data pribadi, jabatan, tanggal bergabung, status kepegawaian.
- Attendance tracking: pencatatan kehadiran harian (manual input atau check-in/out sederhana), status `present`/`absent`/`leave`/`sick`.
- Payroll processing:
    - Komponen gaji dikonfigurasi: base salary, tunjangan tetap, potongan tetap.
    - **Prorate berbasis attendance (keputusan M3)**: base salary dipotong proporsional kalau ada hari `absent` tanpa keterangan dalam periode berjalan (rumus: `base_salary × (hari_kerja - hari_absent) / hari_kerja`, hari kerja = weekday, libur nasional diabaikan di MVP). `leave` dan `sick` tidak memotong. Tunjangan (earning component) dibayar flat, tidak ikut prorate. Detail formula di DATABASE.md Section 2.8a. Kalau data attendance periode berjalan belum lengkap saat payroll diproses, sistem beri warning (tidak block), default hari kosong dianggap `present` kalau user memaksa lanjut.
    - BPJS Kesehatan dan BPJS Ketenagakerjaan dihitung dengan rate yang dikonfigurasi (bukan hardcode), dari gross salary yang basisnya sudah termasuk prorate di atas.
    - PPh21 monthly withholding dihitung otomatis memakai skema **TER**, berdasarkan penghasilan bruto bulanan (setelah prorate) dan PTKP status employee yang bersangkutan. Tabel TER dan mapping PTKP-ke-kategori TER disimpan sebagai reference data yang dapat diperbarui tanpa deploy ulang kode. Hasil kalkulasi dibulatkan round half up ke rupiah penuh.
    - **Annual reconciliation PPh21 (rekonsiliasi Desember dengan tarif progresif) tidak termasuk MVP.** Sistem hanya menghasilkan potongan bulanan sesuai TER, tanpa penyesuaian akhir tahun.
    - Generate slip gaji bulanan per employee, menampilkan breakdown base salary (termasuk info prorate kalau ada), tunjangan, potongan BPJS, dan potongan PPh21.
    - Payroll run terhubung ke Finance module lewat domain event (`PayrollProcessed`, dipicu sekali per period, bukan per employee) untuk membuat **satu** journal entry agregat: beban gaji, beban BPJS perusahaan, dan liability (utang gaji, utang PPh21, utang BPJS gabungan employee+company). Jurnal ini accrual — pelunasan net pay ke karyawan adalah aksi UI terpisah ("Mark as Paid") yang tidak menghasilkan jurnal tambahan di MVP, mirror pola Vendor Bill.

**Data requirements:**

- Employee master data harus menyimpan PTKP status individual (status kawin, jumlah tanggungan) sebagai field wajib, bukan default seragam.

**Assumptions:**

- Tidak ada timesheet karena revenue fixed price per kontrak.
- Adjustment PPh21 akhir tahun (annual reconciliation) dihitung manual di luar sistem untuk saat ini, akan masuk sebagai fase berikutnya.

### 4.3 Finance & Accounting

**Functional requirements:**

- Chart of Accounts (CoA): struktur akun dapat dikonfigurasi sesuai kebutuhan perusahaan IT service & consulting yang juga menjual barang fisik. Struktur numerik 3 digit: digit pertama menunjukkan grup utama (1xx aset, 2xx liabilitas, 3xx ekuitas, 4xx pendapatan, 5xx beban).
- General Ledger: pencatatan jurnal, baik manual maupun otomatis dari event module lain (Sales, Payroll).
- Accounts Receivable: tracking invoice dari Sales module dan status pembayarannya.
- Accounts Payable: tracking kewajiban ke vendor/supplier (untuk kebutuhan dasar, tanpa Purchasing module formal di MVP).
- Financial reporting dasar: neraca (balance sheet) dan laporan laba rugi (income statement), minimal versi sederhana untuk periode bulanan.

**Integration point:**

- Menerima event `SalesOrderCompleted` atau `InvoicePaid` dari Sales & Inventory module untuk generate journal entry pendapatan.
- Menerima event `PayrollProcessed` dari HR module untuk generate journal entry beban gaji.

### 4.4 Sales & Inventory

Digabung dalam satu module folder karena proses bisnis erat terkait, tapi dengan service class terpisah untuk menjaga boundary:

- `SalesOrderService`: menangani logic sales order, quotation, invoice generation, cancel order.
- `InventoryService`: menangani stock movement, dengan API eksplisit (`increaseStock()`, `decreaseStock()`, `reserveStock()`, `releaseReservedStock()`, `fulfillReservedStock()`) supaya bisa dipakai module lain (misalnya Purchasing di fase berikutnya) tanpa menembus Sales logic. `releaseReservedStock()` dan `fulfillReservedStock()` ditambahkan sebagai kebutuhan konkret M2 (lihat Cancel Order dan Complete Order di bawah), bukan API yang tercantum sejak awal planning. Perbedaan keduanya: `releaseReservedStock()` melepas reservasi kembali ke pool tanpa mengurangi stok fisik (dipakai saat cancel), `fulfillReservedStock()` mengurangi stok fisik **dan** reservasi sekaligus (dipakai saat complete — reservasi dikonsumsi, bukan dilepas).

**Functional requirements:**

- Product/Service Catalog: master data item dengan flag `physical_good` (butuh stock tracking) atau `service` (tidak butuh stock, misal jasa konsultasi).
- Sales Order: dibuat langsung dari quotation atau langsung sebagai order, merepresentasikan satu kontrak yang dibayar sekaligus (bukan termin). Satu order boleh berisi campuran item `physical_good` dan `service` sekaligus. **Form input item (keputusan M2)**: dropdown pemilihan item cuma menampilkan `service` (tanpa stock tracking) atau `physical_good` dengan available stock (`quantity_on_hand - quantity_reserved`) lebih dari 0 — item fisik yang stoknya habis tidak muncul sebagai opsi. Input quantity dibatasi maksimal sebesar available stock item yang dipilih. Ini mencegah user membuat order untuk stok yang jelas-jelas tidak cukup di tahap input, meski `InventoryService::reserveStock()` tetap jadi penegak final saat submit (untuk menangani race condition antar user).
- Invoice: digenerate **secara sync** saat Sales Order di-complete (bagian dari `SalesOrderService::completeOrder()`, bukan efek asynchronous dari domain event), sesuai ARCHITECTURE.md Section 4.
- Inventory: stock quantity tracking untuk item bertipe `physical_good`. Tidak termasuk multi-warehouse atau stock opname formal di MVP ini, cukup single location stock level.
- **Cancel Order**: user bisa membatalkan Sales Order selama masih berstatus `draft`, dengan alasan pembatalan wajib diisi. Cancel melepas stock reservation yang sudah dibuat saat order dibuat, supaya stok tersedia kembali untuk order lain. Tidak berlaku untuk order yang sudah `completed` (lihat Section 2.3, return/refund flow di luar scope MVP).
- Flow linear tanpa approval gate: sales order dibuat, invoice digenerate, tidak ada tahap approval manager sebelum invoice terbit.

**Non-goals (MVP):** tidak ada multi-warehouse, tidak ada Purchasing module formal (restock dicatat sebagai manual stock adjustment), tidak ada CRM/lead pipeline.

## 5. Non-Functional Requirements

- **Maintainability**: setiap module punya boundary jelas dan tidak saling coupling langsung lewat model call. Prioritas ini lebih tinggi dari kecepatan development jangka pendek.
- **Auditability**: seluruh perubahan pada data finansial (journal entry, invoice, payroll) harus tercatat di audit log dengan actor dan timestamp.
- **Configurability**: parameter yang berpotensi berubah karena regulasi atau kebijakan internal (rate BPJS, struktur CoA) harus dikonfigurasi lewat data, bukan hardcode di kode aplikasi.
- **Data integrity**: transaksi finansial (invoice, journal entry, payroll run) harus atomic, memakai database transaction, tidak boleh partial commit.

## 6. Future Roadmap (Kandidat Fase Berikutnya)

Tidak dijadwalkan detail di PRD ini, hanya dicatat sebagai arah supaya keputusan arsitektur MVP tidak menutup jalan ke sini:

- PPh21 annual reconciliation (rekonsiliasi Desember dengan tarif progresif dan PTKP tahunan).
- TER Harian untuk pegawai tidak tetap (pegawai dibayar harian), termasuk perubahan schema `employees.employment_type` dan tabel rate terpisah, kalau kebutuhan ini muncul.
- Purchasing/Procurement module formal (PO ke vendor, goods receipt).
- CRM untuk pipeline management pra-sales.
- Multi-warehouse inventory dan stock opname.
- Approval workflow untuk sales order/invoice bernilai besar.
- Integrasi eksternal: payment gateway, e-Faktur, perbankan (VA otomatis).
- Multi-branch/multi-company support kalau bisnis ekspansi.

## 7. Open Questions / Risiko yang Perlu Dipantau

Status per item, sudah banyak yang terjawab sejak draft awal:

- ~~Struktur CoA final belum ditentukan~~ **Terjawab**: seed data awal sudah ada di DATABASE.md Appendix C, diadaptasi dari referensi CoA perusahaan jasa. Bukan hasil audit akuntan, tetap perlu direview sebelum go-live produksi.
- ~~Definisi "stock adjustment manual"~~ **Terjawab**: `reason_code` wajib diisi untuk stock adjustment, lihat DATABASE.md Assumption 3 dan Section 4.5.
- ~~Payroll prorate~~ **Terjawab (M3 planning)**: bukan full month tanpa syarat. Base salary di-prorate proporsional terhadap kehadiran, tapi cuma dipicu `absent` tanpa keterangan (`leave`/`sick` dihitung penuh). Tunjangan (`employee_payroll_components` earning) dibayar flat, tidak ikut prorate. Hari kerja pakai weekday standar, libur nasional diabaikan di MVP. Detail formula di DATABASE.md Section 2.8a. Ini tidak menjawab kasus karyawan baru join/resign di tengah bulan secara eksplisit (prorate di sini murni berbasis attendance harian, bukan berbasis tanggal `hire_date`/`termination_date` terhadap awal/akhir periode) — kalau ada kebutuhan prorate khusus untuk mid-month join/resign, itu perlu ditinjau terpisah, belum tercakup keputusan ini.
- ~~Sumber tabel TER~~ **Terjawab**: data resmi PMK 168/2023 sudah diseed di DATABASE.md Appendix A.
- **Masih terbuka**: proses onboarding PTKP status karyawan baru, apakah diinput manual oleh HR admin saat create employee atau perlu dokumen pendukung (NPWP, kartu keluarga) yang di-attach sebagai bukti. Field requirement di employee master data saat ini cuma menyimpan status-nya (`ptkp_status`), belum ada mekanisme dokumen pendukung.
- **Baru muncul, masih terbuka**: dua angka rate BPJS spesifik untuk perusahaan ini belum dikonfirmasi resmi ke BPJS Ketenagakerjaan: kelas risiko JKK (dipakai asumsi kelas terendah 0.24% sementara) dan batas atas upah (wage cap) untuk JP tahun 2026 kalau memang ada. Detail di DATABASE.md Appendix B, tracking task ada di TASKS.md bagian "Item yang Masih Perlu Diverifikasi".

---

Dokumen terkait: `DATABASE.md`, `ARCHITECTURE.md`, `DESIGN.md`, `ROADMAP.md`, `TASKS.md`.
