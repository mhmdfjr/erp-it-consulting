# Roadmap

## Sistem ERP - Perusahaan IT Service & Consulting

Status: Draft v1.0
Terakhir diperbarui: 2026-07-30

---

## 1. Prinsip Penyusunan Urutan Milestone

Urutan milestone di bawah **bukan urutan "modul mana yang paling penting"**, tapi urutan berdasarkan dependency teknis. Ini penting dijelaskan supaya urutan tidak terlihat sembarangan.

Dari ARCHITECTURE.md, dua module (Sales & Inventory, HR & Payroll) sama-sama fire domain event yang ditangkap Finance module (`SalesOrderCompleted`, `PayrollProcessed`) untuk generate journal entry. Kalau Finance module belum punya fondasi (Chart of Accounts, Journal Entry engine) saat Sales atau HR module selesai dibangun, ada dua pilihan buruk: (a) Sales/HR selesai duluan tapi journal entry-nya belum bisa dibuat sama sekali sampai Finance menyusul, integrasi jadi utang teknis yang menumpuk, atau (b) event dan listener ditulis "buta" tanpa Finance module nyata untuk divalidasi, resiko integrasi salah baru ketahuan belakangan.

Karena itu, Finance module (minimal Chart of Accounts dan Journal Entry engine, belum termasuk AR/AP penuh) dibangun **sebelum** Sales & Inventory dan HR & Payroll, meskipun Finance secara bisnis "kelihatannya" bukan modul pertama yang dipakai user sehari-hari.

Identity & Access Management jelas harus jadi fondasi pertama, karena seluruh module lain butuh auth dan RBAC untuk berfungsi sama sekali.

Roadmap ini tidak menyertakan estimasi durasi kalender (minggu/bulan). Tim development adalah kamu sendiri, dibantu saya untuk implementasi (bukan AI agent otomatis), jadi kecepatan kerja akan sangat tergantung availability waktu kamu di luar aktivitas lain, yang tidak bisa saya perkirakan. Menambahkan angka minggu tanpa dasar itu cuma jadi angka karangan yang tidak berguna untuk planning nyata. Task konkret dengan urutan jelas ada di TASKS.md, kerjakan sesuai kecepatan kamu sendiri.

## 2. Ringkasan Urutan Milestone

| Milestone | Fokus                                        | Catatan urutan                                                                                                                                                                                                                                                        |
| --------- | -------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| M0        | Project Setup + Identity & Access Management | Wajib duluan, seluruh module lain bergantung ke sini                                                                                                                                                                                                                  |
| M1        | Finance Core (CoA + Journal Entry Engine)    | Wajib sebelum M2/M3, karena keduanya butuh consumer event ini sudah nyata                                                                                                                                                                                             |
| M2        | Sales & Inventory                            | Dikerjakan sesudah M1. Secara arsitektur M2 dan M3 tidak saling bergantung, jadi urutannya bisa ditukar (M3 dulu baru M2) tanpa masalah teknis, tapi karena kamu kerja sendirian, kerjakan satu-satu sampai selesai, jangan mondar-mandir antara dua module sekaligus |
| M3        | HR & Payroll (termasuk PPh21 TER-only)       | Sama seperti M2, tidak saling bergantung, urutan relatif terhadap M2 fleksibel                                                                                                                                                                                        |
| M4        | Hardening, Dashboard, dan UAT                | Wajib setelah M2 dan M3 selesai, butuh keduanya untuk testing lintas module yang berarti                                                                                                                                                                              |

---

## 3. M0 - Project Setup + Identity & Access Management

**Tujuan:** fondasi teknis dan akses siap, tidak ada business value yang terlihat oleh end user selain bisa login.

**Deliverables:**

- Setup project Laravel + PostgreSQL + struktur modular monolith sesuai ARCHITECTURE.md (folder `app/Modules`, service provider registration pattern).
- Instalasi dan konfigurasi `spatie/laravel-permission`.
- CRUD User, Role, Permission (UI untuk Super Admin mengelola akses).
- Company Profile dan System Settings (key-value config).
- Audit log dasar (Model Observer untuk create/update/delete pada entity kritikal, infrastructure logging disiapkan di sini meski entity kritikalnya sendiri baru ada di module berikutnya).
- Base layout aplikasi dari DESIGN.md: sidebar, top bar, komponen dasar (button, input, badge, table shell) sebagai Blade/Livewire component reusable.
- Auth flow: login, logout, password reset (session-based, sesuai keputusan no-API di ARCHITECTURE.md).

**Exit criteria:** Super Admin bisa login, membuat user baru, assign role, dan role tersebut membatasi akses menu sesuai permission. Layout dasar aplikasi sudah konsisten dipakai dan audit log tercatat otomatis tanpa kode manual tambahan di tiap Controller.

**Risk yang perlu dipantau:** kalau struktur modular monolith (folder per module, service provider registration) tidak solid dari M0, module berikutnya akan mulai dengan pattern yang salah dan sulit dirapikan belakangan. Milestone ini paling penting direview teliti meski secara fitur terlihat "kecil".

## 4. M1 - Finance Core

**Tujuan:** fondasi akuntansi siap sebagai consumer domain event dari module lain.

**Deliverables:**

- Chart of Accounts: CRUD dengan hierarki (`parent_id`), seed data struktur akun standar.
- Journal Entry engine: `JournalEntryService` yang menegakkan balance check (total debit = total credit) sebelum commit, generate `entry_number` human-readable otomatis, dan mendukung **void** (`voidEntry()`) dengan `void_reason` wajib diisi. Entry yang sudah `posted` immutable secara nilai, koreksi hanya lewat void, tidak pernah edit/delete langsung (lihat ARCHITECTURE.md Section 5b).
- UI melihat daftar journal entry dan detail per entry (read-only untuk nilai, karena kebanyakan entry akan digenerate otomatis dari event module lain, bukan diinput manual), plus aksi void dari UI.
- Vendor master data dan Vendor Bill (AP minimal) sesuai DATABASE.md. Vendor Bill men-generate journal entry otomatis secara accrual (saat bill dibuat, bukan saat dibayar), pelunasan lewat toggle status manual (tanpa payment detail tracking terpisah, beda sengaja dari AR).
- Event Class `SalesOrderCompleted` dan `PayrollProcessed` didefinisikan di module producer (`SalesInventory`, `HR`), Listener skeleton (`CreateJournalEntryFromSalesOrder`, `CreateJournalEntryFromPayroll`) di module consumer (`Finance`), keduanya `ShouldQueue` (logic detail-nya baru lengkap saat M2/M3, tapi struktur listener dan queue job sudah disiapkan di sini). Konsekuensinya, skeleton folder `Events/` di `SalesInventory` dan `HR` dibuat lebih awal dari jadwal modulnya (lihat ARCHITECTURE.md Section 5).

**Exit criteria:** Chart of Accounts bisa diisi dan dipakai, journal entry bisa dibuat manual lewat UI (untuk kasus non-otomatis) dengan balance check yang benar-benar menolak entry yang tidak balance, `entry_number` konsisten ter-generate, void journal entry berfungsi dan mewajibkan reason, vendor bill create dan pelunasan menghasilkan journal entry otomatis dengan angka yang benar.

**Risk yang perlu dipantau:** query laporan finansial di M4 (task 4.5, 4.6) wajib filter `WHERE status = 'posted'` pada `journal_entries`, kalau terlewat, entry yang di-void ikut terhitung dan laporan jadi salah tanpa tanda kesalahan yang jelas. Perlu diverifikasi eksplisit saat M4 dimulai, bukan diasumsikan otomatis benar.

## 5. M2 - Sales & Inventory

**Tujuan:** module transactional pertama yang benar-benar dipakai user sales/admin sehari-hari, sekaligus validasi ujung-ke-ujung integrasi ke Finance.

**Deliverables:**

- Product/Service Catalog: CRUD item dengan `item_type` (physical_good/service).
- Inventory: `InventoryService` dengan stock level tracking single-location, stock movement log, manual stock adjustment dengan reason code wajib.
- Sales Order: create, list, detail, linear flow tanpa approval gate sesuai PRD.
- Invoice generation otomatis saat Sales Order di-complete, terhubung ke Finance lewat event `SalesOrderCompleted` (mengisi listener skeleton dari M1 dengan logic nyata).
- Payment recording terhadap invoice (AR minimal).

**Exit criteria:** user bisa buat sales order dari awal sampai invoice terbit, stok berkurang otomatis untuk item fisik, dan journal entry pendapatan+piutang muncul otomatis di Finance module tanpa input manual.

**Risk yang perlu dipantau:** ini milestone pertama yang benar-benar menguji domain event flow lintas module secara end-to-end. Kalau ada masalah arsitektural di pattern event/listener, ini titik pertama kelihatan, jangan diabaikan dan buru-buru lanjut ke milestone berikutnya kalau masih ada kejanggalan di sini.

## 6. M3 - HR & Payroll

**Tujuan:** modul dengan kompleksitas kalkulasi tertinggi di MVP.

**Deliverables:**

- Employee master data (termasuk field `ptkp_status` wajib), Department, Position.
- Attendance tracking dasar.
- Payroll component configuration (earning/deduction) dan assignment ke employee.
- BPJS rate configuration dan kalkulasi potongan.
- PPh21 TER-only: seed data `ter_categories`, `ptkp_ter_mapping`, `ter_rates` dari Appendix A DATABASE.md, dan `PayrollService` yang lookup TER berdasarkan `ptkp_status` employee.
- Payroll run per period, generate slip gaji, event `PayrollProcessed` terhubung ke Finance (mengisi listener skeleton dari M1).

**Exit criteria:** payroll run bisa diproses untuk satu periode, hasil potongan PPh21 tervalidasi manual terhadap kalkulator resmi DJP untuk minimal 3-5 skenario penghasilan berbeda (bukan cuma dites terhadap logic internal), dan journal entry beban gaji + utang BPJS + utang PPh21 muncul otomatis di Finance.

**Risk yang perlu dipantau:** ini satu-satunya bagian sistem yang salahnya berdampak legal/compliance (SPT karyawan), bukan cuma bug teknis biasa. Validasi terhadap sumber resmi bukan langkah opsional di exit criteria, itu syarat wajib sebelum milestone ini dianggap selesai.

## 7. M4 - Hardening, Dashboard, dan UAT

**Tujuan:** memastikan seluruh modul yang sudah dibangun bekerja sebagai satu sistem koheren sebelum dipakai operasional.

**Deliverables:**

- Dashboard ringkasan lintas module (Stat Card: total revenue bulan berjalan, outstanding invoice, employee aktif, item stock rendah).
- Financial report dasar: neraca dan laba rugi periode bulanan.
- Review keamanan: audit seluruh authorization check (policy per model kritikal), review OWASP dasar (SQL injection lewat query builder yang benar, XSS lewat Blade escaping, CSRF token di semua form).
- Performance check dasar: query N+1 di tabel besar (sales order list, journal entry list), index yang sudah didefinisikan di DATABASE.md benar-benar dipakai (`EXPLAIN ANALYZE` untuk query yang sering diakses).
- User Acceptance Testing dengan data riil atau mendekati riil dari user yang akan pakai sistem sehari-hari (staff Finance, HR, Sales), bukan cuma developer testing sendiri.

**Exit criteria:** seluruh 4 module MVP bisa dipakai end-to-end oleh user asli tanpa bantuan developer, tidak ada critical bug yang tersisa dari UAT, dan laporan finansial dasar menghasilkan angka yang masuk akal terhadap data uji.

---

## 8. Fase 2 (Post-MVP Backlog)

Item ini sudah disebut sebagai out-of-scope MVP di PRD.md, dikumpulkan di sini sebagai backlog, **belum diprioritaskan urutannya** karena itu perlu keputusan bisnis terpisah tergantung mana yang paling mendesak setelah MVP live dan dipakai:

- **PPh21 annual reconciliation** (rekonsiliasi Desember dengan tarif progresif dan PTKP tahunan).
- **TER Harian** untuk pegawai tidak tetap yang dibayar harian, kalau kebutuhan itu muncul.
- **Purchasing/Procurement module formal** (PO ke vendor, goods receipt tercatat resmi, bukan manual stock adjustment).
- **CRM** untuk pipeline management pra-sales.
- **Multi-warehouse inventory** dan stock opname formal.
- **Approval workflow** untuk sales order/invoice bernilai besar.
- **Integrasi eksternal**: payment gateway (webhook receiver sesuai ARCHITECTURE.md Section 8), e-Faktur, perbankan (VA otomatis).
- **Multi-branch/multi-company support**, kalau bisnis ekspansi.

Rekomendasi saya kalau harus menebak prioritas awal fase 2 (bukan keputusan final, perlu divalidasi dengan kebutuhan bisnis aktual setelah MVP live): **Purchasing/Procurement** dan **integrasi payment gateway** kemungkinan besar punya dampak operasional tercepat, karena keduanya mengurangi kerja manual harian (restock tercatat resmi, rekonsiliasi pembayaran otomatis). **CRM** dan **multi-branch** biasanya lebih relevan kalau ada sinyal pertumbuhan bisnis konkret (tim sales membesar, ekspansi kantor), bukan kebutuhan yang mendesak di awal MVP.

---

## 9. Yang Perlu Dikonfirmasi

Kedua item ini sudah tidak lagi menghalangi mulai development (seed data sudah tersedia di DATABASE.md Appendix B dan C), tapi tetap perlu diverifikasi sebelum go-live produksi, detail lengkap ada di TASKS.md bagian "Item yang Masih Perlu Diverifikasi":

- Review struktur Chart of Accounts oleh pihak yang paham akuntansi perusahaan ini (seed data awal sudah dipakai untuk mulai coding, bukan hasil audit final).
- Konfirmasi resmi kelas risiko JKK dan batas atas upah (wage cap) untuk JP tahun 2026 ke BPJS Ketenagakerjaan.

---

Dokumen terkait: `PRD.md`, `ARCHITECTURE.md`, `DATABASE.md`, `DESIGN.md`, `TASKS.md`.
