# Session Summary - M3 Selesai, Siap Lanjut M4

Terakhir diperbarui: 2026-08-10

Dokumen ini menggantikan `SESSION_SUMMARY_M2.md` sebagai starting context. Sesi ini menutup M3 penuh (HR & Payroll). Upload dokumen ini bersama 6 dokumen project (`PRD.md`, `DATABASE.md`, `ARCHITECTURE.md`, `DESIGN.md`, `ROADMAP.md`, `TASKS.md` — versi yang sudah diupdate mencerminkan keputusan M3, lihat riwayat chat sesi ini) di chat baru untuk mulai M4.

---

## Status M3

**Selesai penuh dan terverifikasi.** Task 3.1-3.34 di TASKS.md sudah dikerjakan berurutan, termasuk:

- 83 test project pass tanpa regresi (214 assertion) — 30 test baru M3 (`PayrollServiceTest` 4, `PayrollServiceProrateTest` 4, `PayrollServicePph21ValidationTest` 6, `PayrollServiceBpjsTest` 6, `PayrollRunEndToEndTest` 3 inti + 3 tambahan overlap dengan attendance completeness, `PayrollAttendanceCompletenessTest` 5, `PayrollMarkAsPaidTest` 2) plus seluruh test M0-M2 yang tetap hijau.
- PPh21 divalidasi manual terhadap kalkulator resmi DJP (kalkulator.pajak.go.id, skema Gross, Pegawai Tetap, PPh21 Bulanan) untuk 5 skenario lintas kategori TER A/B/C — seluruhnya cocok persis (tarif dan nominal Rupiah).
- Verifikasi manual UI end-to-end lewat browser: warning attendance completeness, Cancel Draft, Process Payroll, slip gaji dengan breakdown prorate, Mark as Paid, queue worker untuk listener `CreateJournalEntryFromPayroll`.

Project: `erp-it-consulting`, lokasi lokal `~/Documents/Code/Laravel/erp-it-consulting`, environment Linux Mint.

---

## Keputusan Teknis Besar M3 (Sudah Tercermin di Dokumen)

Sembilan keputusan diambil sebelum implementasi lewat sesi tanya-jawab terstruktur (accrual bukan cash-basis untuk jurnal payroll, satu JE agregat per period bukan per employee, BPJS ditanggung bersama employee+company dengan split eksplisit di jurnal, prorate base salary berbasis attendance hanya dipicu `absent`, hari kerja weekday tanpa exclude libur nasional, tunjangan dibayar flat tidak ikut prorate, `percentage_of_base` dari base salary kontraktual bukan yang sudah prorated, warning-bukan-block untuk attendance tidak lengkap, pembulatan PPh21 round half up) — sudah diterapkan ke `DATABASE.md` (Section 2.8, 2.8a, Appendix C), `ARCHITECTURE.md` (Section 4, 5), `PRD.md`, `ROADMAP.md`, `TASKS.md` di awal sesi. Tidak diulang di sini, cek dokumen tersebut langsung untuk detail lengkap dan reasoning-nya.

---

## Bug dan Gotcha Nyata yang Ditemukan Selama M3

Empat hal signifikan, tiga di antaranya baru (bukan pengulangan pola M0-M2), penting diingat sebagai kelas kesalahan yang bisa terulang di M4:

1. **`employees.base_salary` tidak ada di draft skema awal** — ditemukan saat menulis `PayrollService::calculateProratedBaseSalary()` (task 3.14), bukan saat planning. Base salary yang jadi basis prorate dan `percentage_of_base` ternyata tidak punya kolom sama sekali di `employees`. Fix: migration terpisah `add_base_salary_to_employees_table` (bukan edit migration `create_employees_table` yang sudah dieksekusi), kolom `NOT NULL` tanpa default supaya data lama yang belum terisi gagal keras saat migrate, bukan diam-diam terisi 0. **Pelajaran untuk M4**: field yang "jelas dibutuhkan secara logis" tapi tidak eksplisit tercantum di skema awal perlu dicek ulang sebelum mulai coding Service layer, bukan diasumsikan sudah ada.

2. **Gotcha autoload seeder module HR, sama kelas dengan gotcha `make:model`/`make:seeder` M1 tapi manifestasi berbeda dan sempat berulang tiga kali** — namespace StudlyCase (`App\Modules\HR\Database\Seeders`) vs folder fisik lowercase (`database/seeders`) mismatch di Linux case-sensitive filesystem, `Target class ... does not exist`. Sempat terulang setelah fix pertama karena editor/kebiasaan menyimpan file baru kembali ke folder lowercase yang salah. **Fix permanen** (bukan sekadar pindah folder manual lagi): mapping eksplisit ditambahkan di `composer.json` — `"App\\Modules\\HR\\Database\\Seeders\\": "app/Modules/HR/database/seeders/"`. Ini menghilangkan ketergantungan pada disiplin manual menjaga kapitalisasi folder tetap benar selamanya. **Wajib dicek untuk module M4 kalau ada pola serupa** (seeder baru di module manapun) — lihat apakah sudah ada mapping serupa di `composer.json` sebelum debugging error yang sama dari nol.

3. **Bug paling signifikan dan paling lama proses debug-nya: `whereIn('date', [...])` gagal silent di test environment (SQLite `:memory:`), padahal data terbukti ada** — ditemukan lewat proses debugging panjang di task 3.31 (feature test end-to-end). Root cause: SQLite tidak punya tipe `DATE` native, representasi internal tanggal bisa menyimpang dari string `YYYY-MM-DD` murni, membuat exact-match query (`whereIn`, `where('date', '=', ...)`) gagal cocok meski secara nilai kalender sama. Operator perbandingan (`whereBetween`, `>=`, `<`) tetap "bekerja" karena SQLite casting implisit untuk itu — tapi `whereBetween` dengan `endOfMonth()` di boundary akhir bulan **juga** sempat gagal karena isu timezone/representasi titik tengah malam, ditemukan lebih dulu di task 3.18. **Fix ganda**: (a) `checkAttendanceCompleteness()`/`calculateProratedBaseSalary()` pakai half-open range eksplisit (`>= awal_periode`, `< awal_periode_berikutnya`) bukan `whereBetween` dengan `endOfMonth()`; (b) query tanggal spesifik (bukan rentang) pakai `whereDate()`, bukan `whereIn`/exact match. **Ini gotcha kelas baru yang wajib diikuti di M4 dan seterusnya** — didokumentasikan eksplisit di ARCHITECTURE.md Section 9a. Test yang jalan di SQLite tapi production di PostgreSQL adalah trade-off sadar untuk kecepatan test, konsekuensinya butuh disiplin query tanggal yang portable lintas dialek.

4. **Kesalahan skenario test PPh21 (bukan bug kode)** — test pembulatan awal menulis ekspektasi `14125` untuk gross `5650000.01`, ternyata angka itu salah baca boundary bracket TER (nilai tersebut sudah masuk bracket rate 0.50%, bukan 0.25% seperti yang dikira), jawaban benar `28250`. Ditemukan sebelum sempat jadi false-negative yang lolos — pengingat bahwa boundary bracket TER (44+40+41 baris across 3 kategori) tetap rawan salah baca manual meski datanya sudah diverifikasi transkripsi sebelumnya di task 3.10.

---

## Struktur yang Sudah Terbentuk (Referensi untuk M4)

```
app/
  Modules/
    HR/
      Http/Controllers/     (DepartmentController, PositionController, EmployeeController,
                              AttendanceController, PayrollComponentController,
                              EmployeePayrollComponentController, PayrollRunController)
      Http/Requests/         (Store/Update untuk tiap entity, StoreEmployeePayrollComponentRequest
                              dengan withValidator() untuk validasi saling-eksklusif amount/percentage)
      Models/                (Department, Position, Employee, Attendance, PayrollComponent,
                              EmployeePayrollComponent, BpjsRate, TerCategory, PtkpTerMapping,
                              TerRate, PayrollPeriod, PayrollRun, PayrollRunItem — SEMUA butuh
                              newFactory() override eksplisit kalau dipakai di factory)
      Policies/              (Department, Position, Employee, Attendance, PayrollComponent,
                              PayrollPeriod — method viewAny/create/update/process/markAsPaid/cancel)
      Services/              (PayrollService — calculateWorkingDays, calculateProratedBaseSalary,
                              calculateGrossSalary, calculateBpjsDeductions, calculatePph21,
                              checkAttendanceCompleteness, processPayrollRun)
      Events/                (PayrollProcessed — payload cuma payroll_period_id)
      Providers/             (HRServiceProvider)
      routes/web.php
      database/migrations/   (13 migration + 1 tambahan add_base_salary_to_employees_table)
      database/seeders/      (TerRateSeeder — 125 baris TER dari Appendix A, BpjsRateSeeder,
                              PayrollComponentSeeder kosong siap pakai — CATATAN: folder ini
                              lowercase tapi namespace StudlyCase, resolve via composer.json
                              mapping eksplisit, lihat gotcha #2 di atas)
      resources/views/       (departments/, positions/, employees/ + payroll-components.blade.php
                              untuk assignment, attendances/, payroll-components/, payroll-runs/
                              termasuk show.blade.php dengan warning+Cancel Draft+Process Anyway,
                              slip.blade.php breakdown earning/deduction, semua namespace 'hr::')

  Finance/
    Listeners/                CreateJournalEntryFromPayroll — logic asli terisi M3, agregat
                              per period, hitung ulang company portion BPJS dari bpjs_rates
                              (tidak tersimpan di payroll_runs), 7 baris jurnal (511/512/513
                              debit, 202/203/204/205 kredit)

database/factories/          (DepartmentFactory, PositionFactory, EmployeeFactory,
                              AttendanceFactory dengan state absent()/leave()/sick(),
                              PayrollComponentFactory, EmployeePayrollComponentFactory,
                              PayrollPeriodFactory — semua $model eksplisit)

tests/Unit/Modules/HR/Services/
                              (PayrollServiceTest 4, PayrollServiceProrateTest 4,
                              PayrollServicePph21ValidationTest 6, PayrollServiceBpjsTest 6
                              — murni Service layer, PayrollServiceTest pakai PHPUnit\TestCase
                              langsung karena calculateWorkingDays() pure function tanpa DB)
tests/Feature/Modules/HR/
                              (PayrollRunEndToEndTest 6, PayrollAttendanceCompletenessTest 5,
                              PayrollMarkAsPaidTest 2 — pakai RefreshDatabase, seed TER/BPJS/CoA
                              di setUp(), config queue.default=sync eksplisit)
```

**Pola `PayrollService` sebagai satu Service class besar** (bukan dipecah `PayrollService`+`AttendanceService` seperti opsi yang disebut di SESSION_SUMMARY_M2.md) terbukti tetap manageable untuk M3 — method-nya banyak (7 method publik) tapi masing-masing fokus dan mudah ditest terpisah. Tidak ada kebutuhan nyata untuk split lebih jauh.

---

## Permission HR Baru yang Sudah Ter-assign ke Super Admin

Pola granular yang sama diikuti seperti Finance/SalesInventory sebelumnya:

```
hr.department.manage
hr.position.manage
hr.employee.view
hr.employee.create
hr.employee.update
hr.attendance.view
hr.attendance.manage
hr.payrollcomponent.view
hr.payrollcomponent.manage
hr.payroll.view
hr.payroll.process
hr.payroll.pay
```

Tiga tingkat kewenangan payroll dipisah eksplisit (`view`/`process`/`pay`) — bukan satu `hr.payroll.manage` kasar — karena "yang boleh melihat" vs "yang boleh memproses/membayar" secara wajar beda kewenangan di banyak perusahaan (staff HR biasa vs manajer finance/HR senior).

---

## Environment Notes (Praktis, Bukan Keputusan Arsitektur)

- **`phpunit.xml` pakai `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`** — beda dari database dev (`erp_it_consulting`, PostgreSQL). Ini sumber gotcha #3 di atas (representasi tanggal SQLite vs PostgreSQL berbeda), penting diingat kalau ada bug aneh yang cuma muncul di test tapi tidak di manual testing lewat browser, atau sebaliknya — cek dulu apakah query-nya sensitif terhadap dialek SQL sebelum curiga ke logic Service.
- **Gotcha operasional queue tetap sama seperti M2**: `CreateJournalEntryFromPayroll` adalah `ShouldQueue`, journal entry payroll **tidak muncul** setelah "Process Payroll" kalau `php artisan queue:work` tidak jalan di terminal terpisah. Environment testing tetap `queue.default=sync` secara default (dikonfirmasi ulang eksplisit di `setUp()` test M3 meski redundan, untuk kejelasan).
- Kalkulator TER resmi yang dipakai validasi: `kalkulator.pajak.go.id`, PPh21 Bulanan, Kode Objek Pajak "Pegawai Tetap", Skema **Gross** (bukan Gross Up — penting, dua skema beda hasil), field "Penghasilan yang telah dipotong PPh21 pada masa pajak yang sama" diisi **Tidak Ada**.

---

## Yang Masih Terbuka (Carry Over dari M0-M2, Belum Berubah)

- VERIFIKASI 1 (review Chart of Accounts oleh akuntan) — belum dilakukan, tidak menghalangi mulai M4, tetap wajib sebelum go-live.
- VERIFIKASI 2 (kelas risiko JKK, wage cap JP BPJS Ketenagakerjaan) — **relevan sekarang, M3 sudah selesai**. TASKS.md sudah menandai ini sebagai syarat sebelum M3 dianggap "final" (bukan cuma "selesai coding") — kelas risiko JKK 0.24% dan JP tanpa wage cap masih asumsi, ditandai eksplisit di `BpjsRateSeeder` dengan komentar. Perlu ditindaklanjuti sebelum go-live produksi.
- Audit log Role/Permission masih belum tercakup (gap M0, diterima sebagai scope-out).
- Proses onboarding dokumen PTKP karyawan baru — masih terbuka, relevan kalau nanti dibutuhkan validasi dokumen pendukung saat create Employee.
- Asumsi `due_date` invoice net-30 hari (M2, task 2.14) — belum dikonfirmasi user.
- Asumsi pemilihan akun Kas (101) vs Bank (102) dari `payment_method` (M2, task 2.23) — belum dikonfirmasi user.

## Baru Muncul di M3 (Tidak Blocking M4, Tapi Perlu Diingat)

- Prorate mid-month join/resign **tidak tercakup** keputusan M3 — prorate yang ada murni berbasis attendance harian (`status='absent'`), bukan berbasis `hire_date`/`termination_date` employee relatif terhadap periode payroll. Kalau ada karyawan baru join di tengah bulan, sistem saat ini tetap menghitung `working_days` penuh sebulan sebagai basis, employee itu bisa dianggap "banyak absent" kalau attendance-nya memang belum ada dari awal bulan. Ini gap yang sengaja dicatat sebagai out-of-scope keputusan M3, bukan bug — perlu ditinjau terpisah kalau kebutuhan nyata muncul.
- `payroll_runs.status` enum `draft` jadi dead value — tidak pernah dipakai karena `processPayrollRun()` langsung set `finalized` (keputusan eksplisit sesi ini, bukan oversight). Kalau nanti butuh tahap review sebelum finalisasi, transisi status ini perlu dihidupkan dengan logic tambahan.
- `<x-data-table>` sengaja **tidak** dipakai di slip gaji (`payroll-runs/slip.blade.php`) — struktur breakdown dua tabel kecil (Earning/Deduction) dengan baris total di luar pola list-berulang yang diasumsikan `<x-data-table>` (butuh `:empty`/`emptyState` yang tidak relevan di sini). Pola ini beda dari halaman list biasa (Employee, Payroll Run, dst) yang tetap konsisten pakai `<x-data-table>`.
- Deteksi baris PPh21 di slip gaji pakai `str_contains($item->label, 'PPh21')` — rapuh terhadap perubahan format label di `PayrollService`. Technical debt kecil yang dicatat, alternatif lebih robust (`item_key`/`item_type` enum eksplisit di `payroll_run_items`) belum diimplementasikan.
- `PayrollRunEndToEndTest` punya beberapa test tambahan yang overlap dengan `PayrollAttendanceCompletenessTest` (6 test vs rencana awal 3) — hasil eksplorasi debugging, bukan duplikasi yang mengganggu, tapi kandidat konsolidasi kalau nanti ingin merapikan test suite.

---

## Cara Mulai M4 di Chat Baru

1. Upload 6 dokumen project (`PRD.md`, `ARCHITECTURE.md`, `DATABASE.md`, `DESIGN.md`, `ROADMAP.md`, `TASKS.md` — versi final hasil update M3) plus dokumen ini (`SESSION_SUMMARY_M3.md`).
2. Sebutkan mau mulai dari task 4.1 (Dashboard Stat Card) mengikuti ROADMAP.md Section 7 sebagai referensi scope.
3. M4 beda karakter dari M1-M3: bukan module baru, tapi hardening lintas seluruh module yang sudah ada (dashboard, laporan finansial, security review, performance review, UAT, go-live prep) — kerjakan berurutan sesuai TASKS.md M4, tapi fleksibel kalau ada temuan security/performance yang perlu ditindaklanjuti di luar urutan linear.
4. Ingat filter `WHERE status = 'posted'` wajib di setiap query laporan finansial (task 4.5-4.6) — ARCHITECTURE.md Section 5b sudah menandai eksplisit risiko ini sejak M1, sekarang saatnya diverifikasi nyata.
5. Ingat gotcha query tanggal (ARCHITECTURE.md Section 9a) kalau task 4.11-4.12 (performance review, EXPLAIN ANALYZE) menyentuh query dengan filter tanggal — pastikan konsisten pakai `whereDate()`/half-open range, bukan pola yang rawan seperti di M3 awal.
6. VERIFIKASI 1 dan VERIFIKASI 2 (CoA review akuntan, BPJS JKK/JP) perlu ditindaklanjuti sebelum go-live prep (task 4.17-4.20) dianggap benar-benar siap produksi — M4 exit criteria tidak eksplisit mensyaratkan ini selesai, tapi ROADMAP.md Section 9 menandainya sebagai prasyarat go-live.
