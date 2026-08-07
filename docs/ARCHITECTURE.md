# System Architecture

## Sistem ERP - Perusahaan IT Service & Consulting

Status: Draft v1.0
Terakhir diperbarui: 2026-08-03 (revisi M2)

---

## 1. Ringkasan Keputusan Arsitektur

| Keputusan                  | Pilihan                                | Alasan                                                                                                                                                                                      |
| -------------------------- | -------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Overall architecture       | Modular monolith                       | Modul bertambah tanpa bongkar yang sudah ada, tanpa overhead operasional microservices (service discovery, distributed transaction) yang tidak dibutuhkan di skala single company saat ini. |
| Frontend                   | Server-rendered Blade + Livewire       | Tidak ada rencana SPA/mobile app. REST API penuh jadi kerja yang tidak dipakai konsumen manapun.                                                                                            |
| Database                   | Single PostgreSQL database             | Semua modul share satu database. Split per module premature untuk skala saat ini.                                                                                                           |
| Cross-module communication | Domain events (Laravel Event/Listener) | Menghindari direct coupling antar module lewat model call langsung, supaya module bisa berubah independen.                                                                                  |
| Business logic location    | Service class, bukan Controller        | Controller tetap tipis, logic bisa dites tanpa HTTP layer, dan reusable kalau nanti ada consumer lain (API, command, queue job).                                                            |
| Authorization              | `spatie/laravel-permission`            | Reuse package matang untuk RBAC daripada reinvent, sesuai prinsip menghindari over-engineering.                                                                                             |
| Audit log                  | Custom implementation (Model Observer) | Schema `audit_logs` sudah didesain custom di DATABASE.md, tidak perlu tambah package activity-log yang punya schema sendiri dan akan konflik.                                               |

## 2. Kenapa Modular Monolith, Bukan Microservices atau Monolith Tanpa Struktur

Dua pertimbangan utama menentukan pilihan ini.

**Kenapa bukan microservices**: dengan single company, single database, dan tim development yang kemungkinan kecil di awal, microservices menambah operational overhead (service discovery, network latency antar service, distributed transaction untuk kasus seperti "sales order selesai harus bikin journal entry", observability lintas service) tanpa benefit nyata. Benefit microservices baru terasa kalau ada kebutuhan scale horizontal per-domain secara independen atau tim yang cukup besar untuk kerja paralel tanpa saling blocking, dan itu bukan kondisi sistem ini sekarang.

**Kenapa bukan monolith tanpa struktur**: kalau semua kode taruh flat di `app/Models`, `app/Http/Controllers` tanpa pemisahan domain, begitu modul bertambah (sesuai requirement "modul bertambah, flow makin detail" dari PRD), akan cepat jadi kode yang saling coupling tanpa batas jelas. Modular monolith mengambil disiplin dari microservices (boundary jelas per domain, komunikasi lewat event bukan direct call) tapi tetap deploy sebagai satu aplikasi, satu database, satu proses deployment.

Trade-off yang perlu disadari: menulis domain event dan listener untuk komunikasi antar module itu lebih verbose dibanding langsung panggil method model lain. Effort tambahan ini yang dibayar di depan, supaya kalau suatu saat memang perlu extract satu module jadi service terpisah (bukan rencana sekarang, tapi tidak ditutup jalannya), migrasinya tidak perlu rewrite total karena dependency sudah loose dari awal.

## 3. Struktur Direktori

```
app/
  Modules/
    Identity/
      Http/Controllers/
      Livewire/
      Models/
      Services/
      Policies/
      Providers/IdentityServiceProvider.php
      routes/web.php
      database/migrations/
      resources/views/

    HR/
      Http/Controllers/
      Livewire/
      Models/
      Services/
      Events/
      Listeners/
      Providers/HRServiceProvider.php
      routes/web.php
      database/migrations/
      resources/views/

    Finance/
      Http/Controllers/
      Livewire/
      Models/
      Services/
      Events/
      Listeners/
      Providers/FinanceServiceProvider.php
      routes/web.php
      database/migrations/
      resources/views/

    SalesInventory/
      Http/Controllers/
      Livewire/
      Models/
      Services/
        SalesOrderService.php
        InventoryService.php
      Events/
      Listeners/
      Providers/SalesInventoryServiceProvider.php
      routes/web.php
      database/migrations/
      resources/views/

  Shared/
    Http/Middleware/
    Support/
```

Setiap module folder itu self-contained: punya routes, views, migration, dan service provider sendiri. Ini beda dari struktur default Laravel yang taruh semua migration di satu folder `database/migrations` flat, semua model di `app/Models` flat.

**Kenapa migration dipecah per module**: supaya kalau suatu saat satu module perlu di-extract (hipotetis, bukan rencana sekarang), migration historinya sudah terisolasi, tidak tercampur dengan migration module lain. Trade-off: perlu register migration path secara eksplisit di tiap `ServiceProvider` lewat `loadMigrationsFrom()`, sedikit effort tambahan dibanding default convention, tapi konsisten dengan prinsip module boundary yang sudah dipilih.

**Registrasi module**: tiap `{Module}ServiceProvider` didaftarkan di `bootstrap/providers.php` (Laravel 11+) atau `config/app.php` (Laravel 10 ke bawah), masing-masing bertanggung jawab load routes, views, migrations, dan register event listener miliknya sendiri lewat `boot()` method.

## 3a. View Namespacing per Module

Tiap `loadViewsFrom()` di ServiceProvider module WAJIB dikasih namespace unik (contoh: `loadViewsFrom(__DIR__.'/../resources/views', 'identity')`), dipanggil sebagai `view('identity::users.index')`. Tanpa namespace, view dengan nama file sama di module berbeda (misal semua module punya `index.blade.php`) akan collision di view resolver Laravel. Konvensi: namespace sama dengan nama module lowercase (`identity`, `finance`, `sales`, `hr`).

## 3b. Factory Resolution untuk Model di `app/Modules/{Module}/Models`

Gotcha yang ditemukan saat menulis unit test M2, satu kelas dengan gotcha namespace `make:model`/`make:seeder` yang sudah dicatat di SESSION_SUMMARY_M1.md poin 7 — sama-sama akibat model project ini tidak tinggal di lokasi default `App\Models` yang diasumsikan konvensi Laravel.

`Illuminate\Database\Eloquent\Factories\HasFactory::factory()` menebak nama factory class dengan mengganti prefix `App\Models` menjadi `Database\Factories` lalu menambah suffix `Factory`. Untuk model di `App\Modules\SalesInventory\Models\Item`, tebakan itu jadi `Database\Factories\Modules\SalesInventory\Models\ItemFactory` — path yang tidak pernah ada, karena `make:factory` selalu menaruh file factory flat di `database/factories/`, apapun namespace model targetnya.

**Konsekuensi wajib untuk setiap Model di module manapun yang butuh factory** (dua langkah, keduanya diperlukan, salah satu saja tidak cukup):

1. Model override `newFactory()` secara eksplisit, menunjuk ke factory class-nya:

    ```php
    protected static function newFactory()
    {
        return \Database\Factories\ItemFactory::new();
    }
    ```

2. Factory class eksplisit set property `$model`, jangan andalkan auto-guess arah sebaliknya (dari `Database\Factories\ItemFactory` factory resolver juga bisa salah tebak balik ke `App\Item`, bukan `App\Modules\SalesInventory\Models\Item`):

    ```php
    protected $model = \App\Modules\SalesInventory\Models\Item::class;
    ```

Tanpa langkah 1, `Item::factory()` throw `BadMethodCallException` (method `factory()` tidak resolve target apapun). Tanpa langkah 2 (meski langkah 1 sudah benar), factory berhasil ditemukan tapi `newModel()` di dalamnya salah tebak model target dan throw `Class "App\Item" not found`. Kedua arah tebakan sama-sama berasumsi model tinggal di `App\Models`, jadi kedua sisi wajib dibuat eksplisit.

## 4. Service Layer Convention

Pola konsisten dipakai di semua module:

- **Controller**: hanya menangani HTTP concern (validasi request lewat Form Request, panggil Service, return view/redirect). Tidak ada business logic di Controller.
- **Form Request**: validasi input, termasuk authorization check dasar (`authorize()` method) sebelum data masuk ke Service.
- **Service class**: seluruh business logic, termasuk database transaction, kalkulasi, dan pemicu domain event. Ini yang dites lewat unit test tanpa perlu HTTP layer.
- **Model**: representasi data dan relasi Eloquent saja. Hindari "fat model" yang isinya business logic, karena itu menyulitkan reuse logic yang sama dari Service lain atau dari queue job/command.

Contoh alur konkret, pembuatan Sales Order:

1. `SalesOrderController@store` menerima request tervalidasi dari `StoreSalesOrderRequest`.
2. Controller panggil `SalesOrderService::createOrder($validatedData)`.
3. `SalesOrderService` melakukan: buka database transaction, buat record `sales_orders` dan `sales_order_items`, panggil `InventoryService::reserveStock()` untuk item bertipe `physical_good`, commit transaction.
4. Setelah commit, `SalesOrderService` fire event `SalesOrderCreated`.
5. Controller redirect ke halaman detail order dengan flash message sukses.

Event `SalesOrderCreated` di atas beda dari `SalesOrderCompleted` yang disebut di DATABASE.md. Perlu didefinisikan jelas: `SalesOrderCreated` dipicu saat order dibuat (masih status `draft`), sedangkan `SalesOrderCompleted` dipicu saat status berubah jadi `completed` (biasanya lewat aksi eksplisit "Complete Order" oleh user, memicu pembuatan invoice dan journal entry). Dua event ini punya listener yang beda, jangan digabung jadi satu event dengan payload status yang di-cek manual di listener, karena itu membuat listener harus tahu detail state machine module lain.

Contoh alur konkret kedua, penyelesaian Sales Order (keputusan M2, lihat DATABASE.md ASUMSI 7):

1. `SalesOrderController@complete` menerima request, panggil `SalesOrderService::completeOrder($order)`.
2. `SalesOrderService` melakukan, dalam satu database transaction: ubah `status` jadi `completed`, panggil `InventoryService::decreaseStock()` untuk tiap item `physical_good`, **generate record `Invoice`** (nomor, tanggal, due date, amount dari `total_amount` order), commit transaction.
3. Setelah commit, `SalesOrderService` fire event `SalesOrderCompleted` dengan payload `sales_order_id` **dan** `invoice_id` (bukan cuma `sales_order_id`).
4. Listener `CreateJournalEntryFromSalesOrder` (queued, di Finance module) menerima event, membaca invoice yang sudah ada, membuat `journal_entries` terkait.
5. Controller redirect ke halaman detail order/invoice dengan flash message sukses.

**Heuristik menentukan logic masuk Service (sync) vs Listener (queued/async)**: bukan soal "semua efek lintas module harus lewat event queued". Pembeda yang dipakai di sini adalah apakah suatu efek adalah **bagian dari definisi bisnis proses itu sendiri** (fixed, deterministik, murah secara komputasi) versus **keputusan/efek finansial lintas module yang lebih berat atau punya failure mode yang perlu diisolasi dari request asal**.

- Invoice generation adalah transformasi administratif yang PRD.md Section 4.4 sendiri definisikan sebagai bagian dari "complete order" (invoice digenerate dari Sales Order yang sudah completed). Ini tetap sync di `SalesOrderService`, supaya user yang klik "Complete Order" langsung melihat invoice tanpa jeda tak terprediksi menunggu queue worker.
- Journal entry adalah efek finansial lintas module yang triggernya sudah pasti (tidak butuh keputusan tambahan), tapi prosesnya perlu diisolasi dari request asal supaya kegagalan sementara di Finance module tidak membuat "Complete Order" gagal total. Ini tetap queued di Listener, sesuai Section 5.

Prinsip yang sama berlaku untuk kasus serupa di M3: generate slip gaji breakdown (`payroll_run_items`) adalah bagian definisi `PayrollService::processPayrollRun()` itu sendiri (sync), sedangkan journal entry beban gaji dari `PayrollProcessed` tetap di listener queued (async).

## 4a. Shared Sequential Number Generator

`entry_number` (Finance, M1), `order_number` dan `invoice_number` (M2) semuanya butuh pola generation yang identik: format `{PREFIX}-{YYYY}-{6 digit sequential}`, sequence reset tiap tahun, dicari dari nomor terakhir tahun yang sama lalu di-increment. Menulis logic ini tiga kali secara terpisah berisiko divergent bug (misal salah satu implementasi lupa handle year-rollover di pergantian 31 Desember ke 1 Januari, atau race condition saat concurrent create tidak di-lock dengan cara yang sama).

Detail penting yang harus konsisten di ketiga pemakaian: tahun pada nomor mengikuti **tanggal transaksi/entitas** (`entry_date` untuk journal entry), bukan tanggal sistem saat record dibuat (`now()->year`). Ini behavior asli `JournalEntryService` M1 yang wajib dipertahankan saat extract ke trait — kalau nanti `SalesOrderService`/invoice generator juga punya field tanggal yang bisa berbeda dari tanggal input (backdated), trait harus terima `$year` sebagai parameter eksplisit dari caller, bukan menghitung sendiri `now()->year` di dalam trait.

**Keputusan (M2)**: extract jadi trait `app/Shared/Support/GeneratesSequentialNumber.php`, method `generateSequentialNumber(string $table, string $column, string $prefix, int $year)`, dipakai oleh `JournalEntryService` (refactor dari M1, tanpa mengubah format `entry_number` yang sudah berjalan), `SalesOrderService` (`order_number`, tahun dari `order_date`), dan proses invoice generation di `SalesOrderService::completeOrder()` (`invoice_number`, tahun dari `invoice_date`). Trait pakai `DB::table()` query builder (bukan Eloquent Model) supaya generik lintas tabel tanpa perlu tahu class Model target, dan tetap kompatibel dengan `lockForUpdate()` untuk mencegah race condition nomor duplikat pada concurrent request — wajib dipanggil di dalam DB transaction yang sudah terbuka oleh caller.

Refactor `JournalEntryService` ini menyentuh kode M1 yang sudah production-tested (10 unit/feature test Finance), jadi dilakukan sebagai task terpisah dengan re-run test M1 penuh sebelum lanjut ke task M2 lainnya — supaya kalau ada regresi, sumbernya jelas dari refactor ini, bukan tercampur dengan perubahan M2 yang lain.

## 5. Domain Event Catalog

Tabel ini didefinisikan sebagai kontrak antar module. Setiap event baru yang menyeberangi module boundary harus didaftarkan di sini supaya jelas siapa produce, siapa consume.

| Event                 | Dipicu oleh                      | Ditangkap oleh                       | Efek                                                                                                                                                                                                                                              |
| --------------------- | -------------------------------- | ------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `SalesOrderCompleted` | SalesInventory module            | Finance module                       | Generate `journal_entries` (pendapatan, HPP kalau ada barang fisik) mengacu ke `Invoice` yang sudah dibuat sync di `SalesOrderService::completeOrder()` (lihat Section 4, keputusan M2). Payload event membawa `sales_order_id` dan `invoice_id`. |
| `PayrollProcessed`    | HR module                        | Finance module                       | Generate `journal_entries` (beban gaji, utang BPJS, utang PPh21).                                                                                                                                                                                 |
| `InvoicePaid`         | Finance module                   | SalesInventory module (opsional)     | Update status `sales_orders` jadi reflect pelunasan, kalau UI butuh menampilkan status pembayaran di halaman order.                                                                                                                               |
| `StockAdjusted`       | SalesInventory module (internal) | Identity module (audit log listener) | Catat perubahan stok manual ke `audit_logs` dengan detail reason code.                                                                                                                                                                            |

Listener yang menangani efek finansial (`SalesOrderCompleted`, `PayrollProcessed`) sebaiknya di-queue (`ShouldQueue`), bukan synchronous, supaya kegagalan proses finansial tidak membuat request asal (misalnya klik "Complete Order") jadi lambat atau gagal total kalau ada masalah sementara di Finance module. Trade-off: butuh queue worker jalan (`php artisan queue:work` via Supervisor di production), dan perlu strategi retry serta dead-letter handling kalau listener gagal berkali-kali, supaya journal entry yang gagal dibuat tidak hilang diam-diam.

**Cancel Order tidak butuh domain event baru (keputusan M2)**: `SalesOrderService::cancelOrder()` cuma valid dipanggil dari status `draft` (lihat DATABASE.md ASUMSI 8), yaitu sebelum `SalesOrderCompleted` pernah di-fire — belum ada efek Finance apapun (invoice, journal entry) yang perlu dikoreksi/di-void. Release stock reservation (`InventoryService::releaseReservedStock()`) murni internal ke SalesInventory module, tidak menyeberangi module boundary. Ini keputusan sadar, bukan oversight: kalau di masa depan muncul kebutuhan cancel order yang **sudah** completed, itu perubahan scope (return/refund flow, lihat PRD.md Section 2.3) yang butuh event baru (`SalesOrderCancelled` yang ditangkap Finance untuk membuat jurnal reversal), bukan sekadar extend `cancelOrder()` yang ada.

**Konvensi lokasi Event dan Listener (keputusan final)**: Event class hidup di module **producer** (contoh: `SalesInventory/Events/SalesOrderCompleted.php`, `HR/Events/PayrollProcessed.php`), Listener hidup di module **consumer** (contoh: `Finance/Listeners/CreateJournalEntryFromSalesOrder.php`). Event adalah kontrak yang dimiliki module yang men-trigger-nya, module lain yang mau bereaksi cukup import class Event tersebut dan daftarkan listener-nya sendiri.

Trade-off yang disadari: pendekatan alternatif adalah menaruh Event class di folder netral (`app/Shared/Events/`) supaya module consumer tidak perlu import namespace module lain sama sekali, menghindari coupling kalau salah satu module suatu saat di-extract jadi service terpisah. Untuk skala proyek ini (solo developer, tidak ada rencana extract-to-microservice), producer-owned Event lebih konvensional dan predictable, risiko cross-namespace import diterima sebagai trade-off yang wajar.

**Konsekuensi urutan build**: karena M1 (Finance) menulis Listener yang meng-import Event class dari `SalesInventory` dan `HR`, kedua module tersebut butuh skeleton folder `Events/` dibuat lebih awal dari jadwalnya (M2/M3), meski isi module lainnya (`Http/Controllers`, `Services`, dst) tetap dibangun sesuai urutan ROADMAP.md.

## 5a. Audit Logging: Trait vs Domain Event

Ada dua mekanisme audit yang disengaja berbeda, bukan inkonsistensi:

- **Trait `Auditable`** (`app/Shared/Support/Auditable.php`): audit otomatis generic untuk create/update/delete standar via Eloquent Model Observer. Insert langsung ke `audit_logs` (Identity module) dari Model manapun yang pasang trait ini, termasuk lintas module. Ini **explicit exception** terhadap prinsip "no direct cross-module coupling", diterima karena overhead bikin domain event generik untuk sekadar audit trail tidak proporsional dan tidak ada consumer lain selain Identity.
- **Domain event** (misal `StockAdjusted`): dipakai kalau butuh payload custom yang tidak tertangkap dari `getDirty()`/`getOriginal()` biasa (misal `reason_code` dari request context).

**Keterbatasan trait `Auditable`**: hanya bisa dipasang ke Model milik aplikasi sendiri. Model dari package eksternal (`Spatie\Permission\Models\Role`, `Permission`) tidak teraudit lewat mekanisme ini karena tidak bisa ditambah trait tanpa custom extend class. Perubahan Role/Permission assignment saat ini **tidak tercatat** di `audit_logs`. Ini keputusan sadar untuk scope M0 (lihat PRD.md Section 4.1, entity kritikal yang disebut eksplisit tidak termasuk role/permission), bukan oversight. Kalau kebutuhan compliance audit role berubah, opsi: override `config/permission.php` untuk pakai custom Model class yang extends `Spatie\Permission\Models\Role` dan pasang trait `Auditable`.

## 5b. Journal Entry Immutability dan Void

`journal_entries` yang sudah berstatus `posted` tidak pernah diubah nilai debit/credit-nya, baik lewat manual entry maupun yang digenerate otomatis dari domain event. Koreksi kesalahan dilakukan lewat **void**, bukan edit atau delete: `JournalEntryService::voidEntry()` mengubah `status` jadi `void` dan mewajibkan `void_reason` diisi, baris `journal_entry_lines` terkait tetap utuh sebagai jejak audit. Ini konsisten dengan DATABASE.md Section 6 yang sudah menetapkan tabel transaksional tidak pakai soft delete/hard delete, hanya bisa di-void lewat kolom `status`.

Void functionality masuk scope M1 (bukan didefer ke M4), karena tanpa mekanisme koreksi resmi, satu-satunya cara memperbaiki entry yang salah adalah query manual ke database, yang bertentangan langsung dengan Auditability sebagai Non-Functional Requirement utama (PRD.md Section 5).

**Konsekuensi wajib untuk module lain**: setiap query laporan finansial (laba rugi, neraca, task 4.5-4.6 di TASKS.md) harus filter `WHERE status = 'posted'` pada `journal_entries`. Kalau filter ini terlewat, entry yang sudah di-void tetap ikut terhitung dan laporan finansial jadi salah tanpa ada tanda kesalahan yang jelas di level query.

## 6. Livewire untuk Interaktivitas

Karena keputusan full Blade tanpa SPA terpisah, bagian UI yang butuh interaktivitas (misalnya tambah baris item di Sales Order secara dinamis tanpa reload halaman, live search customer, kalkulasi total otomatis saat quantity berubah) memakai **Livewire**, bukan Vue/React terpisah.

Alasan: Livewire tetap dalam ekosistem Blade/PHP sepenuhnya, tidak perlu build step JS terpisah atau state management di client, dan konsisten dengan keputusan "full Laravel, tanpa API layer" karena Livewire komunikasi lewat AJAX request internal ke Laravel sendiri, bukan REST API yang perlu di-maintain sebagai kontrak terpisah.

Trade-off: Livewire tidak cocok untuk UI yang sangat kompleks secara interaktif (misalnya drag-and-drop kompleks, real-time collaborative editing). Untuk skala ERP dengan form dan tabel data seperti sekarang, ini bukan masalah karena kompleksitas UI yang dibutuhkan masih dalam batas wajar untuk Livewire.

## 6a. Shared Blade Components

Component reusable (`<x-button>`, `<x-input>`, `<x-badge>`, `<x-data-table>`, layout `<x-app-layout>`, `<x-sidebar>`, `<x-topbar>`) hidup di `resources/views/components/` (lokasi default Laravel), BUKAN di `app/Shared` atau folder module manapun. Alasan: ini genuinely shared UI layer lintas semua module, bukan business logic, dan Laravel auto-discovery bekerja langsung tanpa config tambahan di lokasi ini.

## 6b. Alpine.js untuk Toggle UI Murni Client-Side (Keputusan M2)

Livewire bundled bareng Alpine.js secara default (`package.json` sudah punya `alpinejs`), dan project ini secara sadar memakai keduanya untuk kasus yang berbeda, bukan kebetulan tercampur:

- **Livewire**: state yang butuh data dari server atau logic yang tidak boleh dipercaya sepenuhnya di client (contoh: dynamic Sales Order item rows di M2, karena tiap baris butuh validasi `item_id` terhadap database, auto-fill `unit_price` dari master data, dan cek available stock).
- **Alpine.js**: toggle visual murni yang tidak butuh round-trip server sama sekali (contoh: show/hide field `cost_price` di form Item tergantung `item_type` yang dipilih, show/hide field NPWP di form Customer tergantung `customer_type`). Pakai Livewire untuk kasus ini cuma nambah network request tanpa benefit apapun — state-nya tidak pernah perlu divalidasi ke server sebelum submit form.

Aturan pembeda: kalau toggle/interaksi butuh baca data dari database atau validasi server-side, pakai Livewire. Kalau cuma show/hide/toggle class berdasarkan pilihan yang sudah ada di client, pakai Alpine. Jangan campur keduanya dalam satu komponen kecil yang sama (misalnya jangan taruh `x-data` Alpine di dalam Livewire component blade yang sama-sama mengelola field yang sama) — pilih satu per form/komponen supaya jelas siapa yang jadi source of truth untuk state itu.

## 7. Autentikasi dan Autorisasi

- Autentikasi: Laravel default session-based auth (`Auth` facade, `web` guard). Tidak butuh Sanctum/Passport karena tidak ada API consumer eksternal.
- Autorisasi: `spatie/laravel-permission` untuk role dan permission, sesuai desain di DATABASE.md Section 1.2. Policy class per model kritikal (`SalesOrderPolicy`, `PayrollRunPolicy`, dst) untuk authorization check yang lebih granular dari sekadar permission check (misalnya "user cuma boleh lihat payroll run milik department sendiri", kalau requirement seperti itu muncul nanti).
- Super Admin: diberi permission lengkap lewat seeding, bukan bypass check di kode. Ini dijelaskan lebih detail di DATABASE.md Section 1.2, konsisten di sini karena authorization flow harus satu jalur (`$user->can(...)`) di semua Controller dan Policy.

## 7a. Policy Registration

Karena Policy hidup di `app/Modules/{Module}/Policies` (bukan `App\Policies` default), auto-discovery Laravel tidak berlaku. Setiap Policy didaftarkan manual lewat `Gate::policy()` di `boot()` method ServiceProvider module masing-masing.

## 8. Webhook Endpoint untuk Integrasi Eksternal (Fase Berikutnya)

Meskipun integrasi eksternal di luar scope MVP, perlu dicatat di sini supaya struktur routing sudah menyiapkan tempatnya, tanpa perlu bikin dari nol nanti:

- Route group terpisah `routes/webhooks.php`, di-exclude dari middleware `web` group standar (khususnya CSRF verification, karena request datang dari server eksternal, bukan browser session).
- Tiap webhook route memverifikasi signature/secret dari provider (misalnya HMAC signature dari payment gateway) sebelum memproses payload, supaya endpoint tidak bisa dipicu sembarangan oleh pihak luar yang tidak terverifikasi.
- Payload webhook diproses lewat Service class yang sama dengan yang dipakai flow internal (misalnya `InvoiceService::markAsPaid()` dipanggil baik dari UI manual maupun dari webhook payment gateway), supaya tidak ada logic duplikat antara jalur manual dan jalur otomatis.

Ini bukan bagian yang dibangun sekarang, hanya dicatat supaya keputusan "tidak ada REST API" tidak disalahartikan sebagai "tidak ada endpoint HTTP sama sekali di luar halaman web".

## 9. Testing Strategy

- **Unit test**: menyasar Service class langsung, tanpa HTTP layer. Contoh: test `PayrollService::calculatePph21()` dengan berbagai kombinasi PTKP status dan penghasilan bruto, verifikasi hasil sesuai tabel TER di DATABASE.md Appendix A.
- **Feature test**: menyasar flow lewat HTTP request (Controller sampai response), memverifikasi efek end-to-end termasuk domain event yang ter-fire (pakai `Event::fake()` untuk assert event dipicu, tanpa perlu listener benar-benar jalan di test yang sama).
- Test khusus untuk kalkulasi finansial (PPh21, BPJS, journal entry balance) wajib punya test case dengan angka konkret yang hasilnya diverifikasi manual terhadap sumber resmi (kalkulator DJP untuk PPh21), bukan cuma dites terhadap logic internal yang bisa saja salah dari awal.

## 10. Deployment Overview

- Single Laravel application, deploy sebagai satu unit (bukan per-module deployment terpisah, konsisten dengan monolith).
- Web server: Nginx + PHP-FPM.
- Queue worker: Laravel queue (`database` atau `redis` driver, pilih `redis` kalau volume job sudah cukup besar untuk butuh performa lebih baik dari polling database) dikelola lewat Supervisor supaya restart otomatis kalau proses mati.
- Scheduler: Laravel Task Scheduling (`schedule:run` lewat cron) untuk job periodik, misalnya generate payroll period baru tiap awal bulan atau reminder invoice jatuh tempo.
- Database: PostgreSQL single instance untuk fase ini, dengan backup rutin (belum dibahas detail, perlu masuk operational runbook terpisah kalau dibutuhkan).

---

Dokumen terkait: `PRD.md`, `DATABASE.md`, `DESIGN.md`, `ROADMAP.md`, `TASKS.md`.
