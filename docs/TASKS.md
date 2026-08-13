# Tasks

## Sistem ERP - Perusahaan IT Service & Consulting

Status: Draft v1.0
Terakhir diperbarui: 2026-08-10 (revisi M3 selesai)

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

- [x] 1.1 Buat struktur folder `app/Modules/Finance` sama seperti pattern Identity di M0 (Http/Controllers, Livewire, Models, Services, Events, Listeners, Providers, routes, database/migrations, resources/views).
- [x] 1.2 Buat `FinanceServiceProvider`, register, load routes/views/migrations dari module ini.
- [x] 1.2a Buat skeleton folder `Events/` (kosong dulu, isinya di task 1.14a) di `app/Modules/SalesInventory` dan `app/Modules/HR`, tanpa membuat isi module lainnya (Http/Controllers, Services, dst tetap dibangun sesuai jadwal M2/M3). Ini dibutuhkan lebih awal karena Event class hidup di module producer sesuai ARCHITECTURE.md Section 5, sementara Listener-nya (M1) butuh mengimpor Event class tersebut.

### Migration dan Model

- [x] 1.3 Migration `chart_of_accounts` sesuai DATABASE.md Section 3.1, termasuk `parent_id` self-referencing FK.
- [x] 1.4 Seeder `ChartOfAccountsSeeder`: masukkan seluruh data dari DATABASE.md Appendix C (kode, nama, account_type, parent_code, is_postable). Buat header/group dulu (is_postable=false), baru leaf account yang reference parent_id-nya.
- [x] 1.5 Migration `journal_entries` (termasuk kolom `entry_number` dan `void_reason`, lihat DATABASE.md Section 3.2) dan `journal_entry_lines` sesuai DATABASE.md Section 3.2-3.3.
- [x] 1.6 Migration `vendors` dan `vendor_bills` (termasuk kolom `account_id`, lihat DATABASE.md Section 3.5) sesuai DATABASE.md Section 3.4-3.5.
- [x] 1.7 Model `ChartOfAccount` (dengan relasi `parent()`/`children()`), `JournalEntry` (dengan relasi `lines()`), `JournalEntryLine`, `Vendor`, `VendorBill` (dengan relasi `account()`).

### Service Layer

- [x] 1.8 `JournalEntryService::createEntry(array $data)`: terima array lines (account_id, debit, credit), buka DB transaction, generate `entry_number` otomatis (format `JE-{YYYY}-{6 digit sequential}`, sequence reset tiap tahun), validasi `sum(debit) === sum(credit)` sebelum insert, validasi setiap `account_id` yang dipakai punya `is_postable = true` (tolak kalau ada line yang posting ke akun header/group), kalau tidak balance atau ada akun non-postable lempar custom exception (`UnbalancedJournalEntryException` / `NonPostableAccountException`), commit kalau valid.
- [x] 1.8a `JournalEntryService::voidEntry(JournalEntry $entry, string $reason)`: validasi `$reason` tidak kosong (lempar exception kalau kosong), ubah `status` jadi `void`, isi `void_reason`. Tidak pernah mengubah nilai debit/credit atau menghapus baris `journal_entry_lines`, entry yang sudah void tetap immutable secara nilai (lihat ARCHITECTURE.md Section 5b).
- [x] 1.9 Unit test untuk `JournalEntryService`: test entry balance berhasil (assert `entry_number` ter-generate dengan format benar), test entry tidak balance ditolak dengan exception yang benar, test posting ke akun non-postable ditolak, test void mengubah status tanpa mengubah nilai lines, test void tanpa reason ditolak.
- [x] 1.9a `VendorBillService::createBill(array $data)`: buka DB transaction, buat record `vendor_bills`, panggil `JournalEntryService::createEntry()` untuk generate jurnal accrual (debit `account_id` dari input, kredit 201 Utang Usaha), commit.
- [x] 1.9b `VendorBillService::markAsPaid(VendorBill $bill)`: ubah `status` jadi `paid`, panggil `JournalEntryService::createEntry()` untuk generate jurnal pelunasan (debit 201 Utang Usaha, kredit 101/102 Kas/Bank).
- [x] 1.9c Unit test untuk `VendorBillService`: test createBill menghasilkan journal entry balance dengan akun yang benar, test markAsPaid menghasilkan journal entry pelunasan yang benar.

### UI

- [x] 1.10 `ChartOfAccountController` + view: list CoA dalam bentuk indented tree/list sesuai hierarki `parent_id`.
- [x] 1.11 `JournalEntryController` + view: list journal entry (read-only, tampilkan `entry_number` dan `status`), detail per entry menampilkan semua line item.
- [x] 1.11a Tombol "Void" di halaman detail journal entry (hanya untuk entry berstatus `posted`), modal konfirmasi dengan textarea `void_reason` wajib diisi sebelum submit, panggil `JournalEntryService::voidEntry()`. Badge status di list dan detail membedakan `posted`/`void` (lihat DESIGN.md Status Badge).
- [x] 1.12 Form manual journal entry (Livewire component untuk dynamic line rows, tambah/hapus baris debit-credit sebelum submit).
- [x] 1.13 `VendorController` CRUD dasar, `VendorBillController` create/list/detail dengan field `account_id` (dropdown akun `is_postable = true`) wajib diisi di form create, tombol "Mark as Paid" di halaman detail yang memanggil `VendorBillService::markAsPaid()`.

### Event Skeleton (Consumer Side)

- [x] 1.14a Definisikan Event class `SalesOrderCompleted` di `app/Modules/SalesInventory/Events/SalesOrderCompleted.php` dan `PayrollProcessed` di `app/Modules/HR/Events/PayrollProcessed.php` (Event hidup di module producer, sesuai ARCHITECTURE.md Section 5, folder skeleton sudah dibuat di task 1.2a).
- [x] 1.15 Buat Listener `CreateJournalEntryFromSalesOrder` dan `CreateJournalEntryFromPayroll` di `app/Modules/Finance/Listeners`, keduanya `implements ShouldQueue` sesuai ARCHITECTURE.md Section 5. Isi `handle()` masih kosong/placeholder (`// TODO: implemented in M2/M3`), yang penting struktur dan registrasi listener-nya sudah jalan.
- [x] 1.16 Register listener di `EventServiceProvider` atau lewat `Event::listen()` di `FinanceServiceProvider::boot()`.

### Review Exit Criteria M1

- [x] 1.17 Verifikasi terhadap ROADMAP.md M1: CoA terisi dan bisa dipakai, journal entry manual bisa dibuat dengan balance check yang benar-benar menolak entry tidak seimbang, `entry_number` ter-generate konsisten, void journal entry berfungsi dan mewajibkan reason, vendor bill create/pelunasan menghasilkan journal entry otomatis dengan angka yang benar.

---

## M2 - Sales & Inventory

**Catatan keputusan yang diambil sebelum mulai coding M2** (lihat riwayat chat sebelumnya untuk reasoning lengkap):

- Migration dan Model `Invoice`/`Payment` ada di module **Finance**, bukan SalesInventory, meski dipicu dari flow Sales.
- `Invoice` digenerate **sync** di `SalesOrderService::completeOrder()`, bukan di listener queued. Listener `CreateJournalEntryFromSalesOrder` cuma bikin `journal_entries`, tidak lagi bikin invoice.
- Journal entry dari Sales Order harus menangani order campuran barang+jasa lewat grouping per `item_type`, bukan asumsi satu order satu jenis item.
- Cancel Order ditambahkan sebagai fitur baru: valid dari status `draft`, melepas stock reservation, reason wajib. (**Revisi setelah task 2.15-2.20**: enum `confirmed` dihapus dari `sales_orders.status` — tidak ada kode manapun yang pernah men-set order ke status itu, jadi cancel eligibility disederhanakan jadi cuma `draft`.)
- **Gap ditemukan & ditambal saat task 2.21-2.23**: task 2.23 (dan DATABASE.md Section 3.7/Appendix C) tidak pernah eksplisit sebut jurnal apapun untuk pelunasan invoice AR — berbeda dari `VendorBillService` (AP, M1) yang eksplisit generate jurnal saat vendor bill dibayar. Tanpa jurnal pelunasan AR, akun 103 (Piutang Usaha) tidak akan pernah berkurang di buku besar meski invoice sudah `paid` secara status, melanggar prinsip double-entry dan bikin neraca tidak balance secara riil. Fix: `InvoiceController::storePayment()` generate journal entry **per payment** (bukan cuma saat full paid) — debit 101/102 (Kas/Bank, dipilih dari `payment_method`), kredit 103 (Piutang Usaha) sebesar `payment.amount`. **Asumsi belum dikonfirmasi**: pemilihan akun 101 (Kas) vs 102 (Bank) berdasarkan `payment_method === 'cash'`, sekelas dengan asumsi `due_date` net-30 di task 2.14 — perlu direview kalau perusahaan punya konvensi pencatatan kas/bank yang berbeda. Validasi tambahan: `amount` payment tidak boleh melebihi sisa tagihan (`invoice.amount - sum(payments.amount)`), mencegah overpayment.
- **Gotcha operasional ditemukan saat verifikasi manual task 2.21**: listener `CreateJournalEntryFromSalesOrder` adalah `ShouldQueue` (ARCHITECTURE.md Section 5), jadi journal entry dari Sales Order **tidak muncul otomatis** setelah "Complete Order" kalau `php artisan queue:work` tidak sedang jalan — job cuma nongkrong di tabel `jobs` sampai ada worker yang proses. Ini bukan bug, tapi konsekuensi arsitektur yang harus diingat setiap kali verifikasi manual fitur yang melibatkan listener queued (sama berlaku untuk M3 nanti, `CreateJournalEntryFromPayroll`). **Wajib jalankan `php artisan queue:work` (atau `--once` untuk verifikasi sekali pakai) di terminal terpisah selama development/testing manual**, konsisten dengan `QUEUE_CONNECTION=database` yang sudah dikonfirmasi jalan sejak M0 (SESSION_SUMMARY_M0.md). Payment journal entry (task 2.23) sengaja dibuat **sync** langsung di controller, bukan lewat event/listener, jadi tidak kena masalah yang sama.
- **Bug nyata ditemukan lewat test 2.24, bukan verifikasi manual**: `SalesOrderService::completeOrder()` awalnya panggil `InventoryService::decreaseStock()` untuk realisasi stok keluar, tapi method itu cuma mengurangi `quantity_on_hand`, sama sekali tidak menyentuh `quantity_reserved` yang sudah dibuat sejak `createOrder()` (task 2.13). Akibatnya `quantity_reserved` nyangkut permanen setiap kali order completed, `available_stock` (on_hand - reserved) makin lama makin salah. Fix: `InventoryService::fulfillReservedStock(Item $item, float $qty, array $referenceData)` — method baru yang mengurangi `quantity_on_hand` **dan** `quantity_reserved` sekaligus dalam satu transaction atomik (beda dari `releaseReservedStock()` yang dipakai `cancelOrder()`: reservasi di situ dilepas kembali ke pool tanpa stok fisik berkurang, sedangkan di `completeOrder()` reservasi itu **dikonsumsi**). `completeOrder()` diupdate memanggil `fulfillReservedStock()`, bukan `decreaseStock()`. Test regresi ditambahkan di `InventoryServiceTest::test_fulfill_reserved_stock_mengurangi_on_hand_dan_reserved_sekaligus`.

**Permission granular SalesInventory yang sudah ter-assign ke Super Admin** (pola sama seperti daftar Finance di SESSION_SUMMARY_M1.md, dikumpulkan di sini sebagai referensi untuk task 2.26 dan starting context M3 nanti):

```
sales.item.view
sales.item.create
sales.item.update
sales.category.manage
sales.customer.view
sales.customer.create
sales.customer.update
sales.order.view
sales.order.create
sales.order.complete
sales.order.cancel
sales.inventory.view
sales.inventory.adjust
```

- **Catatan proses (task 2.15-2.20)**: draf awal view sempat salah asumsi API `<x-data-table>` (pakai `<x-slot name="head">` dengan `<th>` manual), padahal komponen aktual M0 pakai prop `:headers` (array) dan `:empty` (boolean) plus named slot `emptyState`/`pagination`. Sebelum menulis view baru yang pakai component shared M0 (`x-button`, `x-input`, `x-badge`, `x-data-table`, dst) di module M3/M4 nanti, **cek dulu isi file component aslinya** di `resources/views/components/`, jangan asumsikan API dari nama komponen atau pola generik Blade.
- **Bug ditemukan & diperbaiki saat verifikasi manual UI (setelah 2.15-2.20 "selesai")**:
    1. `UpdateItemRequest` sempat pakai rule `unique:items,sku,{id}` sebagai notasi placeholder yang salah ditulis literal — Laravel kirim string `"{id}"` apa adanya ke query SQL, error `invalid input syntax for type bigint`. Fix: `Rule::unique('items', 'sku')->ignore($this->route('item')->id)`.
    2. `StoreCustomerRequest`/`UpdateCustomerRequest` sudah benar validasi `in:individual,corporate`, tapi view create/edit customer pakai `value="company"` di option select — mismatch enum menyebabkan submit "Perusahaan" selalu gagal validasi silent (tidak ada `@error` ditampilkan, select re-render tanpa `old()` sehingga terlihat seperti "fallback ke individu" padahal sebenarnya validation error yang tidak kelihatan). Fix: samakan value jadi `corporate`, tambahkan `@error('customer_type')`, inisialisasi `x-data` Alpine dari `old()`/nilai existing bukan hardcode.
    3. Livewire `CreateOrder::itemSelected()` sempat ketinggalan `dd($this->items)` debug statement — setiap pilih item di dropdown request Livewire mati total. Dihapus.
    4. `CreateOrder::save()` sempat panggil `Item::findOrFail()` sebelum `$this->validate()` — item_id invalid/kosong throw 404 mentah alih-alih pesan validasi. Urutan dibalik: validasi dulu, baru resolve item.
    5. Ditambahkan (di luar scope task awal, permintaan tambahan): filter dropdown item di `CreateOrder` cuma tampilkan `service` atau `physical_good` dengan available stock (`quantity_on_hand > quantity_reserved`) > 0, plus cap quantity input server-side lewat hook `updated()` (HTML `max` attribute cuma UI hint, bukan enforcement saat submit via Livewire AJAX). Kolom "Stok Tersedia" ditambah eksplisit di tabel, bukan caption di bawah input.
    6. `resources/views/orders/livewire/create-order.blade.php` dan `resources/views/chart-of-accounts/index.blade.php` (M1, ikut dirapikan) direfactor pakai `<x-data-table>` untuk konsistensi lintas module, sebelumnya tabel manual.
- `order_number` dan `invoice_number` pakai format `{PREFIX}-{YYYY}-{6 digit sequential}`, konsisten dengan `entry_number` M1, lewat trait shared.

### Struktur Module

- [x] 2.1 Buat struktur folder `app/Modules/SalesInventory` (catatan: satu module folder, tapi dua service class terpisah sesuai keputusan di ARCHITECTURE.md Section 2 dan percakapan sebelumnya).
- [x] 2.1a Extract trait `app/Shared/Support/GeneratesSequentialNumber.php` (lihat ARCHITECTURE.md Section 4a): terima prefix, tabel/kolom target, dan tahun eksplisit dari caller (bukan `now()->year`, mengikuti behavior asli `entry_date`-based di M1), cari nomor terakhir tahun yang sama, increment, format `{PREFIX}-{YYYY}-{6 digit sequential}`. Refactor `JournalEntryService::createEntry()` (M1) untuk pakai trait ini menggantikan logic inline generate `entry_number`, **tanpa mengubah format output**. **Jalankan ulang seluruh test M1 (`JournalEntryServiceTest`, `VendorBillServiceTest`, `EventListenerRegistrationTest`) dan pastikan tetap pass sebelum lanjut ke task berikutnya** — refactor ini menyentuh kode production-tested, regresi harus ketahuan di sini, bukan tercampur dengan perubahan M2 lain. **Terverifikasi: 10/10 test pass tanpa modifikasi assertion.**
- [x] 2.2 Buat `SalesInventoryServiceProvider`, register, load routes/views/migrations.

### Migration dan Model

- [x] 2.3 Migration `item_categories`, `items` sesuai DATABASE.md Section 4.2-4.3.
- [x] 2.4 Migration `stock_levels`, `stock_movements` sesuai DATABASE.md Section 4.4-4.5.
- [x] 2.5 Migration `customers` sesuai DATABASE.md Section 4.1.
- [x] 2.6 Migration `sales_orders` (termasuk kolom `cancel_reason`, lihat DATABASE.md Section 4.6), `sales_order_items` sesuai DATABASE.md Section 4.6-4.7.
- [x] 2.7 Migration `invoices`, `payments` sesuai DATABASE.md Section 3.6-3.7, ditempatkan di `app/Modules/Finance/database/migrations` (**keputusan final**: kedua tabel ini milik module Finance, bukan SalesInventory, lihat DATABASE.md ASUMSI 6). Migration file `invoices` **wajib** punya timestamp setelah migration `sales_orders` (task 2.6), supaya FK constraint `sales_order_id` tidak gagal saat `php artisan migrate` — urutan eksekusi migration Laravel mengikuti timestamp filename, bukan urutan `loadMigrationsFrom()` di ServiceProvider mana pun.
- [x] 2.8 Model untuk seluruh tabel di atas dengan relasi Eloquent yang sesuai (`Item::stockLevel()`, `SalesOrder::items()`, `SalesOrder::invoice()`, dst). Model `Invoice`/`Payment` ditaruh di `app/Modules/Finance/Models`, bukan `SalesInventory/Models`.

### Service Layer

- [x] 2.9 `InventoryService::increaseStock(Item $item, float $qty, array $referenceData)`.
- [x] 2.10 `InventoryService::decreaseStock(Item $item, float $qty, array $referenceData)`, tolak kalau `quantity_on_hand` tidak cukup.
- [x] 2.11 `InventoryService::reserveStock(Item $item, float $qty)` untuk sales order yang belum completed.
- [x] 2.11a `InventoryService::releaseReservedStock(Item $item, float $qty)`: kurangi `quantity_reserved` di `stock_levels`. **Tidak** insert row baru ke `stock_movements` (bukan physical movement, lihat DATABASE.md Section 4.5). Dipakai oleh `SalesOrderService::cancelOrder()` (task 2.13a).
- [x] 2.12 `InventoryService::recordAdjustment(Item $item, float $qty, string $direction, string $reasonCode, ?string $note)`, wajibkan `$reasonCode` tidak kosong sesuai DATABASE.md Assumption 3.
- [x] 2.13 `SalesOrderService::createOrder(array $data)`: buat `sales_orders` + `sales_order_items` dalam satu DB transaction, generate `order_number` lewat trait `GeneratesSequentialNumber` (format `SO-{YYYY}-{6 digit}`), panggil `InventoryService::reserveStock()` untuk tiap item `physical_good`.
- [x] 2.13a `SalesOrderService::cancelOrder(SalesOrder $order, string $reason)`: validasi status order saat ini `draft` (tolak dengan exception kalau sudah `completed`/`cancelled`), validasi `$reason` tidak kosong, panggil `InventoryService::releaseReservedStock()` untuk tiap item `physical_good` di order, ubah `status` jadi `cancelled` dan isi `cancel_reason`, dalam satu DB transaction.
- [x] 2.14 `SalesOrderService::completeOrder(SalesOrder $order)`: dalam satu DB transaction — ubah status jadi `completed`, panggil `InventoryService::decreaseStock()` untuk realisasi stok keluar tiap item `physical_good`, **generate record `Invoice`** (nomor lewat trait `GeneratesSequentialNumber` format `INV-{YYYY}-{6 digit}`, `invoice_date`, `due_date`, `amount` dari `total_amount` order), commit. Setelah commit, fire event `SalesOrderCompleted` dengan payload `sales_order_id` **dan** `invoice_id` (bukan cuma `sales_order_id` seperti draft awal skeleton M1). **Catatan asumsi belum dikonfirmasi**: `due_date` invoice memakai default net-30 (`invoice_date + 30 hari`) karena DATABASE.md tidak menetapkan term pembayaran eksplisit — perlu dikonfirmasi/disesuaikan sebelum go-live kalau perusahaan punya term standar berbeda.
- [x] 2.14a Unit test `SalesOrderService`: test `cancelOrder` melepas reservasi dengan benar dan menolak kalau status sudah `completed`, test `cancelOrder` menolak kalau `reason` kosong, test `completeOrder` menghasilkan `Invoice` dengan `invoice_number` format benar dan `amount` sesuai `total_amount` order. **Terverifikasi: 4/4 test pass.** Ditemukan gotcha baru saat menulis test ini: Model di `app/Modules/{Module}/Models` butuh override `newFactory()` eksplisit DAN factory class butuh set `$model` eksplisit, auto-resolve dua arah sama-sama gagal karena model tidak di `App\Models` — lihat ARCHITECTURE.md Section 3b untuk detail lengkap.

### UI

- [x] 2.15 `ItemController` + view: CRUD Product/Service Catalog, form dengan toggle `item_type` (physical_good/service) yang menyembunyikan field stock-related kalau `service`.
- [x] 2.15a **Gap ditemukan setelah 2.15**: `item_categories` (DATABASE.md Section 4.2) tidak punya cara diisi lewat UI — tidak ada task M2 manapun yang bikin CRUD untuk `ItemCategory`, padahal form Item create/edit mengasumsikan dropdown kategori terisi. `ItemController@create` manapun memanggil `$categories = ItemCategory::orderBy('name')->get()` tapi dari awal tabelnya kosong. Fix: `ItemCategoryController` minimal (index + store saja, tanpa edit/delete — schema cuma `id`+`name` tanpa field lain, full CRUD tidak proporsional), halaman kecil `item-categories/index.blade.php` dengan form tambah inline, diakses lewat tombol "Kelola Kategori" di halaman Item Catalog. Permission baru `sales.category.manage`. **Pelajaran untuk M3**: entity lookup/reference sederhana (mirip `item_categories`, kandidatnya `departments`/`positions` di M3) perlu dicek eksplisit di planning task apakah sudah ada cara mengisinya lewat UI atau seeder, jangan asumsikan dropdown-nya otomatis terisi hanya karena schema-nya sudah dimigrate.
- [x] 2.16 `CustomerController` + view: CRUD dasar.
- [x] 2.17 `SalesOrderController` + Livewire component: form create order dengan dynamic item rows (tambah/hapus baris, kalkulasi subtotal otomatis saat quantity/harga berubah). **Wajib ikuti pola associative-array `wire:key` dari M1** (lihat SESSION_SUMMARY_M1.md poin 3): key stabil per baris (`nextLineKey++`), bukan index array, dipakai konsisten di `wire:key`, `wire:model`, dan parameter `removeItem()`. **Terverifikasi diterapkan.**
- [x] 2.18 View detail Sales Order: tampilkan status (Status Badge sesuai DESIGN.md), item list, tombol "Complete Order" yang memanggil `completeOrder()`. Tombol "Cancel Order" (hanya tampil untuk status `draft`) dengan modal konfirmasi berisi textarea `cancel_reason` wajib diisi sebelum submit — mirror pola modal void journal entry dari M1 task 1.11a, termasuk gotcha toggle `hidden`+`flex` sekaligus lewat JS (lihat SESSION_SUMMARY_M1.md poin 8).
- [x] 2.19 View stock adjustment: form manual dengan field reason code wajib diisi.
- [x] 2.20 View list stock movement per item (riwayat in/out/adjustment).

### Integrasi ke Finance

- [x] 2.21 Isi logic nyata di `CreateJournalEntryFromSalesOrder::handle()` (listener dari M1 task 1.15): baca `Invoice` dari `invoice_id` di payload event (sudah dibuat sync di task 2.14, listener **tidak** membuat invoice lagi). Group `sales_order_items` per `item_type` sebelum membangun baris jurnal (lihat DATABASE.md Appendix C, catatan pemetaan): jumlahkan subtotal `physical_good` → kredit 402, jumlahkan subtotal `service` → kredit 401, satu baris debit 103 (Piutang) sebesar total gabungan, dan kalau ada item `physical_good` tambahkan pasangan debit 501/kredit 105 sebesar total HPP (`cost_price` × quantity, dijumlahkan lintas item fisik). Panggil `JournalEntryService::createEntry()` dengan seluruh baris ini sekaligus dalam satu journal entry.
- [x] 2.22 `InvoiceController` + view (di module Finance): detail invoice, tombol record payment.
- [x] 2.23 Form record payment terhadap invoice, update status invoice jadi `paid` setelah full payment tercatat. **Ditambah di luar spesifikasi awal**: journal entry pelunasan AR per payment (lihat catatan gap di atas), validasi anti-overpayment.

### Test M2

- [x] 2.24 Feature test end-to-end: buat sales order dengan item fisik saja → complete order → assert stok berkurang, invoice terbuat sync (tanpa perlu `Queue::fake()` untuk invoice-nya), journal entry pendapatan+HPP+piutang otomatis muncul dengan angka yang benar. **Terverifikasi, dan test ini yang menangkap bug `fulfillReservedStock()` di atas.**
- [x] 2.24a Feature test end-to-end: buat sales order **campuran** (item `physical_good` + `service` dalam satu order) → complete order → assert journal entry mengalokasikan pendapatan ke akun 401 dan 402 secara terpisah dengan angka yang benar per akun (bukan cuma assert total debit = total credit), assert HPP cuma dihitung dari item fisik. **Terverifikasi pass.**
- [x] 2.24b Feature test: buat sales order → cancel order → assert `quantity_reserved` kembali ke semula, assert tidak ada row baru di `stock_movements`, assert status jadi `cancelled` dengan `cancel_reason` terisi, assert cancel ditolak kalau dicoba pada order yang sudah `completed`. **Terverifikasi pass.**
- [x] 2.25 Unit test `InventoryService`: assert `decreaseStock` menolak kalau stok tidak cukup, assert `recordAdjustment` menolak kalau `reasonCode` kosong, assert `releaseReservedStock` mengurangi `quantity_reserved` tanpa membuat `stock_movements` baru. **Ditambah**: assert `reserveStock` menolak kalau available tidak cukup, assert `recordAdjustment` direction `out` menolak kalau stok tidak cukup, assert `fulfillReservedStock` mengurangi `quantity_on_hand` dan `quantity_reserved` sekaligus (regresi test untuk bug di atas). **7/7 pass.**

### Review Exit Criteria M2

- [x] 2.26 Verifikasi terhadap ROADMAP.md M2: alur sales order sampai invoice jalan penuh (invoice muncul sync tanpa jeda queue), integrasi ke Finance otomatis tanpa input manual dengan alokasi akun benar untuk order campuran, cancel order melepas reservasi dengan benar tanpa meninggalkan stok terkunci. **Terverifikasi**: full test suite 50/50 pass (poin E), alur end-to-end (poin A-D) terverifikasi berulang kali sepanjang sesi lewat siklus manual test — create order campuran, complete order, cek invoice+journal entry per-akun, cancel order, record payment partial+full. **M2 selesai**, lihat `SESSION_SUMMARY_M2.md`.

---

## M3 - HR & Payroll

**Catatan**: rate BPJS Kesehatan, JHT, JP, JKM sudah tersedia di DATABASE.md Appendix B. VERIFIKASI 2 (kelas risiko JKK, wage cap JP) tetap perlu diselesaikan sebelum M3 dianggap final, tapi tidak menghalangi mulai task di bawah.

**Keputusan planning M3 yang diambil sebelum mulai coding** (lihat riwayat chat sesi ini untuk reasoning lengkap, sudah diterapkan ke DATABASE.md Section 2.8/2.8a dan Appendix C, ARCHITECTURE.md Section 4/5, PRD.md, ROADMAP.md):

- **Prorate base salary berbasis attendance**: dipicu **hanya** oleh `status = 'absent'` (tanpa keterangan). `leave` dan `sick` dihitung penuh, tidak memotong. Hari kerja pakai weekday (Senin-Jumat), libur nasional diabaikan di MVP (tidak ada tabel calendar/holiday). Formula: `base_salary_prorated = employees.base_salary × (hari_kerja - hari_absent) / hari_kerja`. Ini membalik ASUMSI 1 versi sebelumnya (full month tanpa prorate) — perlakukan sebagai keputusan final baru.
- **Tunjangan (earning component) dibayar flat**, tidak ikut prorate. `percentage_of_base` di `employee_payroll_components` dihitung dari `employees.base_salary` kontraktual (fixed), bukan dari nilai yang sudah prorated.
- **BPJS dan PPh21 dihitung dari gross salary yang basisnya sudah termasuk prorate** (bukan gross penuh dengan potongan absent ditambahkan belakangan di net) — supaya akurat terhadap penghasilan yang benar-benar diterima di periode itu.
- **Attendance completeness check**: kalau jumlah row `attendances` employee di periode itu kurang dari hari kerja bulan tersebut, tampilkan warning sebelum payroll diproses (tidak block keras). Kalau user memaksa lanjut, hari kosong dianggap `present`.
- **PPh21 dibulatkan round half up** ke rupiah penuh.
- **Journal entry payroll: satu JE agregat per `payroll_period`** (bukan per employee/`payroll_run`), accrual basis (kredit 202 Utang Gaji, bukan langsung kas). BPJS di kredit 204/205 harus gabungan employee portion + company portion, bukan cuma employee portion dari `payroll_runs`.
- **Pelunasan net pay ke karyawan** adalah aksi UI terpisah ("Mark as Paid", toggle `payroll_runs.status` jadi `paid`), mirror pola `VendorBill` M1 — tidak fire event baru, tidak generate jurnal tambahan di MVP.
- Kolom baru `payroll_runs.working_days` dan `payroll_runs.absent_days` ditambahkan (di luar skema M3 awal) untuk audit trail prorate, supaya slip gaji bisa menjelaskan kenapa base salary beda dari kontrak tanpa perlu query ulang `attendances`.
- **Gap ditemukan saat task 3.14 (implementasi, bukan planning)**: `employees.base_salary` ternyata tidak ada di draft skema awal DATABASE.md Section 2.3 — kolom ini dibutuhkan sebagai basis prorate dan basis `percentage_of_base`, ditambahkan lewat migration terpisah `add_base_salary_to_employees_table` (bukan edit migration `create_employees_table` yang sudah dieksekusi). DATABASE.md Section 2.3 sudah diperbarui mencerminkan ini.
- **Gotcha autoload seeder module HR (ditemukan berulang saat task 3.10-3.11)**: namespace seeder `App\Modules\HR\Database\Seeders\...` (StudlyCase) tidak match folder fisik `app/Modules/HR/database/seeders/` (lowercase, mengikuti konvensi migration) — Linux case-sensitive filesystem membuat Composer PSR-4 gagal resolve, `Target class ... does not exist`. **Fix permanen**: mapping eksplisit ditambahkan di `composer.json` (`"App\\Modules\\HR\\Database\\Seeders\\": "app/Modules/HR/database/seeders/"`), bukan memindahkan folder — supaya folder tetap konsisten lowercase seperti `database/migrations` di sebelahnya, sementara namespace tetap StudlyCase sesuai konvensi Laravel umum. **Pelajaran untuk module berikutnya**: kalau bikin seeder baru di module manapun dengan pola serupa, cek dulu apakah sudah ada mapping serupa di `composer.json`, jangan asumsikan folder/namespace otomatis cocok.
- **Gotcha `whereIn()` terhadap kolom tanggal, SQLite (test) vs PostgreSQL (production) — ditemukan saat debugging task 3.31**: `Attendance::whereIn('date', ['2026-08-03', ...])->update(...)` mengembalikan 0 rows affected di test environment (SQLite `:memory:`, lihat `phpunit.xml`), meski data dengan tanggal persis itu terbukti ada. Root cause: SQLite tidak punya tipe `DATE` native, representasi internal tanggal bisa menyimpang dari string murni `YYYY-MM-DD` yang di-bind di `whereIn` (exact string match), sementara operator perbandingan (`>=`, `<`, `whereBetween`) tetap bekerja karena SQLite melakukan casting implisit untuk itu. **Fix**: pakai `whereDate('date', $tanggal)` untuk perbandingan tanggal spesifik (bukan `whereIn`/exact match terhadap kolom date/datetime), method ini menangani perbedaan dialek SQL antara SQLite dan PostgreSQL secara konsisten. **Wajib diikuti pola sama di M4 dan seterusnya** — jangan pakai `whereIn('date_column', [...])` atau `where('date_column', '=', $tanggal)` literal untuk query tanggal, selalu `whereDate()`.
- **Gotcha boundary `whereBetween` di query rentang tanggal — ditemukan saat debugging task 3.18/3.31**: `whereBetween('date', [$start->toDateString(), $end->toDateString()])` untuk akhir bulan (`endOfMonth()`) berpotensi exclude baris tepat di tanggal terakhir tergantung representasi internal boundary vs timezone session koneksi. **Fix**: query rentang tanggal per periode (dipakai di `checkAttendanceCompleteness()` dan `calculateProratedBaseSalary()`) diganti jadi half-open range eksplisit: `where('date', '>=', $periodStart)->where('date', '<', $nextPeriodStart)` (awal bulan berikutnya sebagai batas eksklusif), bukan `whereBetween` dengan `endOfMonth()`. Lebih robust terlepas dari tipe kolom atau timezone.

### Struktur Module

- [x] 3.1 Buat struktur folder `app/Modules/HR` sama seperti pattern module sebelumnya.
- [x] 3.2 Buat `HRServiceProvider`, register, load routes/views/migrations.

### Migration dan Model

- [x] 3.3 Migration `departments`, `positions`, `employees` sesuai DATABASE.md Section 2.1-2.3, pastikan `ptkp_status` NOT NULL sesuai keputusan.
- [x] 3.4 Migration `attendances` sesuai DATABASE.md Section 2.4.
- [x] 3.5 Migration `payroll_components`, `employee_payroll_components` sesuai DATABASE.md Section 2.5.
- [x] 3.6 Migration `bpjs_rates` sesuai DATABASE.md Section 2.6.
- [x] 3.7 Migration `ter_categories`, `ptkp_ter_mapping`, `ter_rates` sesuai DATABASE.md Section 2.7.
- [x] 3.8 Migration `payroll_periods`, `payroll_runs`, `payroll_run_items` sesuai DATABASE.md Section 2.8, **termasuk kolom `working_days` dan `absent_days` di `payroll_runs`** (keputusan M3 planning, lihat catatan di atas dan DATABASE.md Section 2.8a) — bukan cuma kolom yang ada di draft skema awal.
- [x] 3.9 Model untuk seluruh tabel di atas dengan relasi yang sesuai. Ingat pola dua-lapis factory override (`newFactory()` di model + `$model` eksplisit di factory class) kalau model butuh factory untuk test, sesuai ARCHITECTURE.md Section 3b.

### Seed Data

- [x] 3.10 Seeder `TerRateSeeder`: masukkan seluruh data dari DATABASE.md Appendix A (3 kategori, mapping PTKP, seluruh baris rate kategori A/B/C). Ini data yang sudah kamu konfirmasi resmi, tinggal dipindah jadi seeder PHP array, bukan ditulis ulang manual satu-satu supaya tidak ada typo, pertimbangkan generate dari CSV/array terstruktur.
- [x] 3.11 Seeder `BpjsRateSeeder`: masukkan seluruh data dari DATABASE.md Appendix B, termasuk `max_wage_base` untuk BPJS Kesehatan. Rate JKK pakai asumsi 0.24% (kelas risiko terendah) sampai VERIFIKASI 2 selesai, tandai dengan komentar di kode supaya gampang ditemukan kalau perlu diganti nanti.
- [x] 3.12 Seeder `PayrollComponentSeeder`: komponen dasar yang berlaku umum (kalau ada tunjangan standar perusahaan, masukkan di sini, kalau belum ada bisa dikosongkan dan diisi manual lewat UI nanti).

### Service Layer

- [x] 3.13 `PayrollService::calculateWorkingDays(int $year, int $month)`: hitung jumlah weekday (Senin-Jumat) dalam bulan tersebut. Method kecil dan murni (tanpa dependency ke Employee/database lain), gampang di-unit-test terpisah. Dipakai oleh 3.14 dan 3.16.
- [x] 3.14 `PayrollService::calculateProratedBaseSalary(Employee $employee, PayrollPeriod $period)`: turunkan `periode_awal`/`periode_akhir` dari `$period->period_month`/`$period->period_year` (`Carbon::create($year, $month, 1)->startOfMonth()`/`->endOfMonth()`, JANGAN asumsikan ada kolom tanggal tersimpan di `payroll_periods`, lihat DATABASE.md Section 2.8a). Hitung `hari_absent` (`count(attendances WHERE employee_id, status='absent', date BETWEEN periode_awal AND periode_akhir)`), pakai `calculateWorkingDays()` untuk `hari_kerja`, kembalikan `base_salary_prorated = employee->base_salary × (hari_kerja - hari_absent) / hari_kerja`. Kembalikan juga `hari_kerja` dan `hari_absent` (dibutuhkan buat isi `payroll_runs.working_days`/`absent_days` di task 3.18).
- [x] 3.15 `PayrollService::calculateGrossSalary(Employee $employee, float $baseSalaryProrated)`: jumlahkan `$baseSalaryProrated` + seluruh `employee_payroll_components` bertipe earning yang aktif (flat, tidak ikut prorate — `percentage_of_base` dihitung dari `employee->base_salary` kontraktual, **bukan** dari `$baseSalaryProrated`).
- [x] 3.16 `PayrollService::calculateBpjsDeductions(Employee $employee, float $grossSalary)`: hitung potongan **employee portion** berdasarkan `bpjs_rates` yang berlaku (`effective_date` terbaru yang belum `end_date` **relatif terhadap first day of period**, bukan `now()`). Terapkan `max_wage_base` sebagai cap: kalau `grossSalary > max_wage_base` (berlaku untuk BPJS Kesehatan), pakai `max_wage_base` sebagai dasar perhitungan, bukan `grossSalary` aktual. Method ini cuma kembalikan employee portion (dipakai mengurangi net salary) — company portion dihitung terpisah nanti di listener (task 3.25), bukan disimpan di `payroll_runs`.
- [x] 3.17 `PayrollService::calculatePph21(Employee $employee, float $grossSalary)`: lookup `ptkp_ter_mapping` berdasarkan `employee->ptkp_status`, cari `ter_category_id`, query `ter_rates` untuk bracket yang sesuai `income_lower_bound <= $grossSalary <= income_upper_bound` (atau `income_upper_bound IS NULL` untuk bracket teratas), kalikan rate dengan gross salary, **bulatkan round half up ke rupiah penuh**.
- [x] 3.18 `PayrollService::processPayrollRun(PayrollPeriod $period)`: sebelum loop, untuk tiap employee `employment_status = active` cek attendance completeness (`count(attendances) >= calculateWorkingDays()` untuk periode itu) — kalau ada yang kurang, kumpulkan daftar employee yang datanya belum lengkap dan kembalikan/lempar sebagai warning ke Controller (bukan langsung proses), supaya UI (task 3.24) bisa tampilkan konfirmasi sebelum lanjut. Terima parameter/flag eksplisit (misal `bool $forceIncomplete = false`) untuk kasus user memilih lanjut meski warning. Loop seluruh employee `employment_status = active`: hitung `base_salary_prorated` (3.14) → `gross_salary` (3.15) → BPJS employee portion (3.16) → PPh21 (3.17) → `net_salary`, simpan ke `payroll_runs` (termasuk `working_days`/`absent_days`) + `payroll_run_items` (breakdown per komponen, termasuk baris earning individual), dalam satu DB transaction per employee.
- [x] 3.19 Setelah seluruh employee diproses, fire event `PayrollProcessed` dengan payload cuma `payroll_period_id` (listener query semua `payroll_runs` milik period itu sendiri, lihat ARCHITECTURE.md Section 4/5).

### UI

- [x] 3.20 `DepartmentController`, `PositionController` + view: CRUD dasar.
- [x] 3.21 `EmployeeController` + view: CRUD dengan form termasuk field `ptkp_status` (dropdown TK0/TK1/K0/TK2/TK3/K1/K2/K3).
- [x] 3.22 `AttendanceController` + view: input kehadiran harian per employee, status `present`/`absent`/`leave`/`sick`.
- [x] 3.23 `PayrollComponentController` + view: CRUD komponen gaji, assignment ke employee.
- [x] 3.24 `PayrollRunController` + view: pilih/buat `payroll_periods`, tombol "Process Payroll" yang memanggil `PayrollService::processPayrollRun()`. **Wajib tampilkan warning attendance completeness** (dari task 3.18) sebelum submit final kalau ada employee dengan data attendance belum lengkap di periode itu — modal konfirmasi eksplisit sebelum lanjut dengan `forceIncomplete = true`, bukan silent default ke present. Tombol terpisah "Mark as Paid" per `payroll_run` (atau per period untuk seluruh employee sekaligus, pilih salah satu — kalau per period, pastikan idempotent dan tidak menimpa `payroll_run` yang sudah `paid` sebelumnya) yang toggle `status` jadi `paid`, **tidak** memanggil `JournalEntryService` (tidak ada jurnal tambahan, cuma toggle status).
- [x] 3.25 View slip gaji per employee: breakdown base salary (tampilkan info prorate — `working_days`/`absent_days` dan nilai sebelum/sesudah prorate kalau ada potongan absent), earning, BPJS deduction, PPh21 deduction, net salary, menampilkan `ter_category_used` untuk audit trail.

### Integrasi ke Finance

- [x] 3.26 Isi logic nyata di `CreateJournalEntryFromPayroll::handle()` (listener dari M1 task 1.15): terima `payroll_period_id` dari payload event, query seluruh `payroll_runs` milik period tersebut, sum per kolom (`gross_salary`, `net_salary`, `pph21_deduction`, BPJS employee portion per jenis). **Hitung ulang BPJS company portion** dari `bpjs_rates.rate_company_percentage` (rate yang berlaku di first day of period, sama seperti task 3.16) dikalikan gross salary tiap employee (dengan cap `max_wage_base` yang sama), jumlahkan lintas employee. Bangun **satu** journal entry sesuai mapping DATABASE.md Appendix C: debit 511 (total gross), debit 512 (BPJS Kesehatan company portion), debit 513 (BPJS Ketenagakerjaan company portion, gabungan JHT+JKK+JKM+JP), kredit 202 (total net salary), kredit 203 (total PPh21), kredit 204 (BPJS Kesehatan employee+company portion digabung), kredit 205 (BPJS Ketenagakerjaan employee+company portion digabung). Panggil `JournalEntryService::createEntry()` dengan seluruh baris ini sekaligus, `reference_type = 'PayrollPeriod'`, `reference_id = payroll_period_id`.

### Test M3 (Wajib, Bukan Opsional)

- [x] 3.27 Unit test `PayrollService::calculateWorkingDays()`: verifikasi jumlah weekday benar untuk beberapa bulan berbeda (termasuk bulan dengan jumlah hari ganjil/genap, tahun kabisat kalau relevan untuk Februari).
- [x] 3.28 Unit test `PayrollService::calculateProratedBaseSalary()`: skenario tanpa absent (hasil = base_salary penuh), skenario dengan beberapa hari absent (hasil terpotong proporsional), skenario `leave`/`sick` tidak memotong (assert eksplisit beda dari `absent`).
- [x] 3.29 Unit test `PayrollService::calculatePph21()` dengan minimal 3-5 skenario penghasilan berbeda per kategori TER (A/B/C), hasil dibandingkan manual terhadap kalkulator resmi DJP, verifikasi pembulatan round half up diterapkan benar. **Jangan lanjut ke task berikutnya kalau ada selisih angka yang tidak bisa dijelaskan.**
- [x] 3.30 Unit test `PayrollService::calculateBpjsDeductions()` dengan skenario gross salary berbeda (termasuk skenario di atas `max_wage_base` untuk memastikan cap diterapkan benar), verifikasi terhadap rate resmi di DATABASE.md Appendix B.
- [x] 3.31 Feature test end-to-end: process payroll run untuk beberapa employee sekaligus (termasuk minimal satu employee dengan hari absent supaya prorate teruji, bukan cuma skenario full attendance), assert `payroll_runs` (termasuk `working_days`/`absent_days`) dan **satu** `journal_entries` agregat terbentuk dengan angka yang konsisten (total debit = total credit, DAN assert nilai per akun individual — khususnya 204/205 harus mengandung employee+company portion, bukan cuma employee portion — supaya bug seperti M2 grouping per `item_type` tidak lolos tak terdeteksi cuma karena total balance).
- [x] 3.32 Feature test: attendance completeness — assert warning muncul/dilempar kalau ada employee dengan attendance kurang dari hari kerja bulan itu, assert `processPayrollRun()` tetap bisa jalan dengan `forceIncomplete = true` dan hari kosong dianggap `present`.
- [x] 3.33 Feature test: "Mark as Paid" — assert toggle status jadi `paid`, assert **tidak ada** `journal_entries` baru yang dibuat dari aksi ini.

### Review Exit Criteria M3

- [x] 3.34 Verifikasi terhadap ROADMAP.md M3: payroll run bisa diproses dengan prorate attendance diterapkan benar, PPh21 tervalidasi terhadap sumber resmi (termasuk pembulatan), integrasi ke Finance otomatis menghasilkan satu journal entry agregat per period dengan alokasi akun BPJS employee+company yang benar, Mark as Paid berfungsi tanpa jurnal tambahan. **Terverifikasi**: full test suite 83/83 pass (214 assertion) tanpa regresi ke M0-M2, PPh21 5/5 skenario cocok kalkulator resmi DJP (task 3.29), verifikasi manual UI end-to-end lewat browser (attendance warning, Cancel Draft, Process Payroll, slip gaji, Mark as Paid, queue worker) dikonfirmasi berfungsi. **M3 selesai**, lihat `SESSION_SUMMARY_M3.md`.

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
