# Session Summary - Planning Sistem ERP

Terakhir diperbarui: 2026-07-31

Dokumen ini ringkasan sesi planning, dipakai sebagai starting context di chat session baru saat mulai development M0. Detail lengkap ada di 6 dokumen project (`PRD.md`, `DATABASE.md`, `ARCHITECTURE.md`, `DESIGN.md`, `ROADMAP.md`, `TASKS.md`), upload semuanya di chat baru supaya konteks penuh tersedia.

---

## Konteks Bisnis

Perusahaan IT service & consulting di Indonesia, single company (bukan multi-branch), menjual barang teknologi (kombinasi stok sendiri dan pass-through vendor) dan jasa konsultasi (fixed price per kontrak, dibayar sekaligus, bukan termin).

## Tim dan Cara Kerja

Solo developer (kamu) dibantu Claude untuk implementasi. **Bukan** AI agent otomatis/vibe coding, jadi task dikerjakan satu-satu secara terarah lewat TASKS.md, bukan digenerate sekaligus oleh AI.

## Tech Stack

Laravel + PostgreSQL, Linux environment. **Tidak ada REST/JSON API**, full server-rendered Blade + Livewire (tidak ada rencana SPA/mobile app). Arsitektur **modular monolith**: tiap domain jadi folder module terpisah (`app/Modules/{Domain}`), komunikasi antar module lewat domain event, bukan direct model call.

## Scope MVP (4 Module)

1. **Identity & Access Management + Master Data** (disebut "Super Admin" oleh user) — RBAC pakai `spatie/laravel-permission`, audit log custom.
2. **Finance & Accounting** — Chart of Accounts, Journal Entry engine dengan balance check wajib, AR/AP minimal.
3. **Sales & Inventory** — digabung satu module folder, tapi `SalesOrderService` dan `InventoryService` tetap dua service class terpisah. Single-location stock, tanpa multi-warehouse.
4. **HR & Payroll** — termasuk PPh21 **TER-only** (monthly withholding, TANPA annual reconciliation Desember, itu fase 2), termasuk BPJS.

## Keputusan Penting yang Sudah Difinalkan

- **Urutan build**: M0 (Identity) → M1 (Finance Core) → M2 (Sales & Inventory) → M3 (HR & Payroll) → M4 (Hardening/UAT). Finance dibangun sebelum Sales/HR karena keduanya fire domain event yang ditangkap Finance.
- **Domain event**: `SalesOrderCompleted` dan `PayrollProcessed` ditangkap Finance module untuk generate journal entry otomatis, listener queued (`ShouldQueue`).
- **PPh21**: skema TER (PMK 168/2023) untuk monthly withholding, data resmi tabel TER (kategori A/B/C) sudah diseed di DATABASE.md Appendix A. Annual reconciliation Desember out of scope MVP.
- **TER Harian** (pegawai tidak tetap harian): out of scope MVP, fase 2. Semua employee di MVP diasumsikan pegawai tetap bulanan.
- **BPJS rates**: sudah diisi di DATABASE.md Appendix B (Kesehatan 1%/4% cap Rp12jt, JHT 2%/3.7%, JP 1%/2%, JKM 0.3%). **JKK masih asumsi 0.24% (kelas risiko terendah), wage cap JP belum dikonfirmasi** — keduanya perlu verifikasi resmi ke BPJS Ketenagakerjaan sebelum M3 selesai, tapi tidak menghalangi mulai coding.
- **Chart of Accounts**: seed data awal sudah ada di DATABASE.md Appendix C, diadaptasi dari referensi CoA perusahaan jasa + ditambah akun persediaan/HPP karena jual barang fisik juga. Bukan hasil audit akuntan, perlu direview sebelum go-live.
- **Approval workflow**: tidak ada, flow linear tanpa approval gate untuk sales order/invoice.
- **Payroll prorate**: asumsi default full month, tidak ada prorate otomatis untuk karyawan baru/resign di tengah bulan.
- **Design system**: diadaptasi dari referensi editorial "Steep", tapi disesuaikan untuk ERP data-dense (tipografi diperkecil drastis, ditambah warna semantik status success/warning/danger/info, button tidak pill-shape).

## Yang Masih Terbuka (Tidak Blocking, Tapi Perlu Diingat)

- Keputusan soal mempertahankan typeface serif dari referensi design asli (saya rekomendasikan drop, belum dikonfirmasi user).
- Proses onboarding dokumen PTKP karyawan baru (perlu attach dokumen pendukung atau tidak), relevan saat kerjakan task Employee form di M3.
- Verifikasi resmi kelas risiko JKK dan wage cap JP ke BPJS Ketenagakerjaan.
- Review Chart of Accounts oleh pihak yang paham akuntansi perusahaan, sebelum go-live produksi.

## Status Saat Ini

Seluruh 6 dokumen (`PRD.md`, `DATABASE.md`, `ARCHITECTURE.md`, `DESIGN.md`, `ROADMAP.md`, `TASKS.md`) sudah dibuat, saling konsisten, dan siap dipakai. **Siap mulai development dari task 0.1 di TASKS.md.**

## Cara Mulai di Chat Session Baru

1. Upload keenam file dokumen di atas.
2. Sebutkan task mana yang mau dikerjakan (misal "bantu saya kerjakan task 0.1 sampai 0.5").
3. Kerjakan berurutan sesuai TASKS.md, jangan lompat kecuali ditandai bisa paralel.
4. Setelah satu task/group task selesai, update checklist di TASKS.md secara manual atau minta saya bantu update.
