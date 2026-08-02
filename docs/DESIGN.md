# Design System

## Sistem ERP - Perusahaan IT Service & Consulting

> quiet, data-dense, warm-neutral workspace

Status: Draft v1.0
Terakhir diperbarui: 2026-07-30

**Theme:** light

Design system ini diadaptasi dari referensi editorial (`Steep`) yang dilampirkan, tapi disesuaikan untuk konteks internal tool berbasis data, bukan marketing landing page. Yang dipertahankan dari referensi: palet near-monochrome dengan satu aksen hangat, radius lembut di card, dan tipografi dengan hierarki halus lewat variasi weight, bukan lompat ke bold. Yang diubah: skala tipografi dipangkas jauh (tidak ada heading 90px di aplikasi kerja), dan ditambah warna semantik status karena ERP butuh menandai kondisi data (lunas/belum lunas, stok rendah, aktif/resign) dengan jelas, bukan cuma estetika restrained.

Halaman ERP dibangun sebagai dashboard shell standar: sidebar navigasi kiri, top bar dengan breadcrumb dan user menu, konten utama berupa tabel/form/card di kanan. Ini beda dari referensi asli yang menampilkan produk sebagai "floating artifact" di sekitar headline, karena itu pattern hero section, bukan pattern kerja harian.

---

## Tokens - Colors

| Name         | Value     | Token                  | Role                                                                                         |
| ------------ | --------- | ---------------------- | -------------------------------------------------------------------------------------------- |
| Ink Black    | `#17191c` | `--color-ink-black`    | Primary text, sidebar active state, filled button background                                 |
| Paper White  | `#ffffff` | `--color-paper-white`  | Page canvas, card surface, button text di atas ink black                                     |
| Mist Gray    | `#f2f2f3` | `--color-mist-gray`    | Secondary background, input fill, hover state row tabel                                      |
| Fog White    | `#fafafb` | `--color-fog-white`    | Sidebar background, alternating table row                                                    |
| Slate Gray   | `#777b86` | `--color-slate-gray`   | Body text sekunder, helper text, label form                                                  |
| Ash Gray     | `#979799` | `--color-ash-gray`     | Placeholder text, metadata tersier                                                           |
| Border Gray  | `#e5e5e7` | `--color-border-gray`  | Hairline border tabel, input, card                                                           |
| Blush Peach  | `#fbe1d1` | `--color-blush-peach`  | Aksen hangat untuk highlight non-status (misal badge "Baru" pada fitur baru), dipakai jarang |
| Sienna Brown | `#5d2a1a` | `--color-sienna-brown` | Teks di atas peach surface                                                                   |

### Semantic Status Colors

Ditambahkan di luar referensi asli karena ERP wajib mengkomunikasikan state data dengan jelas.

| Name             | Value     | Token                | Role                                           |
| ---------------- | --------- | -------------------- | ---------------------------------------------- |
| Success Green    | `#1a7f4e` | `--color-success`    | Invoice lunas, employee aktif, stock cukup     |
| Success Green BG | `#e6f4ec` | `--color-success-bg` | Background badge success                       |
| Warning Amber    | `#b5750a` | `--color-warning`    | Invoice mendekati jatuh tempo, stock menipis   |
| Warning Amber BG | `#fdf1de` | `--color-warning-bg` | Background badge warning                       |
| Danger Red       | `#c0362c` | `--color-danger`     | Invoice overdue, stock habis, validation error |
| Danger Red BG    | `#faeae8` | `--color-danger-bg`  | Background badge danger                        |
| Info Blue        | `#2563a8` | `--color-info`       | Status netral informatif (draft, in progress)  |
| Info Blue BG     | `#e9f1fa` | `--color-info-bg`    | Background badge info                          |

Aturan pakai: warna semantik **hanya** untuk badge status, alert banner, dan validation message. Tidak dipakai untuk dekorasi atau branding, supaya makna warnanya tetap konsisten dan tidak diinterpretasikan salah oleh user (kalau merah dipakai untuk elemen dekoratif juga, user akan mulai ragu apakah merah di tabel itu error atau bukan).

---

## Tokens - Typography

Beda signifikan dari referensi: tidak ada display type 90px/64px. ERP tidak punya hero section, heading terbesar yang dibutuhkan adalah page title.

### Sohne - Body, UI, dan navigation sans, satu-satunya typeface di seluruh aplikasi

Referensi asli pakai dua typeface (serif Signifier untuk heading, sans Sohne untuk body). Untuk ERP, saya rekomendasikan **hanya pakai Sohne** di semua ukuran, termasuk page title. Alasan: aplikasi kerja dengan tabel dan form padat butuh konsistensi visual yang tinggi dan rendering cepat, dua typeface family menambah kompleksitas font-loading tanpa benefit yang sepadan untuk konteks non-editorial. Kalau tetap mau nuansa "warm editorial" dari referensi asli, serif bisa dipakai terbatas hanya di halaman login/branding, bukan di dashboard kerja.

- **Substitute**: Inter, atau `ui-sans-serif, system-ui` sebagai fallback.
- **Weights**: 400, 450, 500, 600
- **Letter spacing**: 0 di semua ukuran (tracking negatif di referensi asli cuma relevan untuk display type besar, tidak perlu di skala kecil)

### Type Scale

| Role       | Size | Line Height | Weight | Token               | Pemakaian                                     |
| ---------- | ---- | ----------- | ------ | ------------------- | --------------------------------------------- |
| caption    | 12px | 1.4         | 400    | `--text-caption`    | Timestamp, footnote tabel                     |
| label      | 13px | 1.4         | 500    | `--text-label`      | Label form, kolom header tabel                |
| body-sm    | 14px | 1.5         | 400    | `--text-body-sm`    | Body text sekunder, helper text               |
| body       | 15px | 1.5         | 400    | `--text-body`       | Body text utama, isi tabel, isi form          |
| body-lg    | 16px | 1.5         | 450    | `--text-body-lg`    | Emphasis body, nilai metrik di card           |
| heading-sm | 18px | 1.3         | 500    | `--text-heading-sm` | Card title, section heading dalam page        |
| heading    | 22px | 1.3         | 500    | `--text-heading`    | Page title (misal "Sales Order #SO-2026-001") |
| heading-lg | 28px | 1.25        | 500    | `--text-heading-lg` | Dashboard summary title, jarang dipakai       |

Font tabular numeric (`font-variant-numeric: tabular-nums`) wajib dipakai untuk semua angka finansial di tabel (harga, quantity, saldo), supaya digit berbaris rapi secara vertikal saat scanning kolom angka, ini detail kecil tapi penting untuk data finansial yang dibaca cepat oleh user.

---

## Tokens - Spacing & Shapes

**Base unit:** 4px (dipertahankan dari referensi)

**Density:** compact-comfortable — lebih rapat dari referensi asli karena tabel ERP perlu menampilkan banyak baris data tanpa scroll berlebihan.

### Spacing Scale

| Name | Value | Token          |
| ---- | ----- | -------------- |
| 4    | 4px   | `--spacing-4`  |
| 8    | 8px   | `--spacing-8`  |
| 12   | 12px  | `--spacing-12` |
| 16   | 16px  | `--spacing-16` |
| 20   | 20px  | `--spacing-20` |
| 24   | 24px  | `--spacing-24` |
| 32   | 32px  | `--spacing-32` |
| 48   | 48px  | `--spacing-48` |
| 64   | 64px  | `--spacing-64` |

### Border Radius

| Element | Value         | Catatan                                                                                                                                                                                                 |
| ------- | ------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| cards   | 12px          | Diturunkan dari 24px di referensi, radius besar terlihat aneh pada card dashboard padat data                                                                                                            |
| inputs  | 8px           |                                                                                                                                                                                                         |
| buttons | 8px           | **Bukan pill/9999px** seperti referensi. Pill button cocok untuk CTA marketing, tapi di form ERP dengan banyak tombol berjajar (Save, Cancel, Delete), radius kecil terlihat lebih tegas dan fungsional |
| badges  | 9999px (pill) | Justru badge status yang cocok pill, karena bentuknya kecil dan butuh terlihat sebagai penanda diskret, bukan tombol aksi                                                                               |
| table   | 8px           | Radius pada container tabel, bukan per-row                                                                                                                                                              |

### Shadows

| Name       | Value                            | Token                 | Pemakaian                                                            |
| ---------- | -------------------------------- | --------------------- | -------------------------------------------------------------------- |
| subtle     | `0 1px 2px rgba(23,25,28,0.06)`  | `--shadow-subtle`     | Card default                                                         |
| elevated   | `0 4px 12px rgba(23,25,28,0.10)` | `--shadow-elevated`   | Modal, dropdown, popover                                             |
| focus-ring | `0 0 0 3px rgba(37,99,168,0.25)` | `--shadow-focus-ring` | Focus state input, wajib ada untuk accessibility keyboard navigation |

### Layout

- **Sidebar width**: 240px (expanded), 64px (collapsed, icon only)
- **Top bar height**: 56px
- **Content max-width**: tidak dibatasi (full width dikurangi sidebar), beda dari referensi yang page max-width 1200px, karena tabel data ERP justru butuh lebar penuh untuk menampilkan banyak kolom.
- **Card padding**: 16px
- **Table cell padding**: 12px vertikal, 16px horizontal
- **Section gap**: 24px (jauh lebih rapat dari 80px di referensi, karena tidak ada hero section)

---

## Components

Component set di bawah menggantikan sepenuhnya component list di referensi asli (Pill Button, Floating Product Artifact, dst yang berorientasi marketing page), diganti dengan component yang benar-benar dipakai di ERP.

### Sidebar Navigation

**Role:** navigasi utama antar module (Dashboard, HR, Finance, Sales & Inventory, Settings)

Background `--color-fog-white`, lebar 240px, item aktif berlatar `--color-mist-gray` dengan indikator garis vertikal 3px `--color-ink-black` di sisi kiri. Icon + label, `--text-body` weight 450. Grup berdasarkan module dengan section label kecil (`--text-caption`, `--color-ash-gray`) di atas tiap grup item.

### Top Bar

**Role:** breadcrumb, page context, user menu

Background `--color-paper-white`, border-bottom hairline `--color-border-gray`, tinggi 56px. Breadcrumb kiri (`--text-body-sm`, `--color-slate-gray`, separator `/`), user avatar + dropdown kanan.

### Data Table

**Role:** menampilkan daftar data (sales order, invoice, employee, item)

Header row: background `--color-fog-white`, `--text-label`, `--color-slate-gray`, border-bottom `--color-border-gray`. Body row: `--text-body`, border-bottom hairline antar row, hover background `--color-mist-gray`. Angka finansial rata kanan dengan tabular numeric. Baris punya row action (icon button, muncul saat hover) di kolom paling kanan, bukan tombol permanen yang bikin tabel ramai.

Wajib punya: empty state (lihat komponen terpisah di bawah), pagination di bawah tabel, dan sortable column header untuk kolom yang relevan (tanggal, jumlah).

### Status Badge

**Role:** menandai state data (invoice unpaid/paid/overdue, employee active/resigned, sales order draft/confirmed/completed)

Pill shape (`border-radius: 9999px`), padding 2px 10px, `--text-caption` weight 500, warna sesuai semantic token (success/warning/danger/info) untuk background dan text yang senada (bukan text putih di atas background pekat, supaya tetap terasa ringan sesuai semangat referensi asli "quiet components").

Contoh mapping: `draft` → info, `confirmed`/`active` → success, `pending`/`near due date` → warning, `cancelled`/`overdue`/`resigned` → danger.

### Button - Primary

**Role:** aksi utama per halaman (Save, Create Sales Order, Process Payroll)

Background `--color-ink-black`, text `--color-paper-white`, border-radius 8px, padding 8px 16px, `--text-body` weight 500. Tidak memakai shadow. Hover: background sedikit lebih terang (`opacity: 0.9` atau shade lebih ringan).

### Button - Secondary

**Role:** aksi sekunder berdampingan dengan primary (Cancel, Back)

Background transparent, border 1px `--color-border-gray`, text `--color-ink-black`, radius dan padding sama dengan primary.

### Button - Danger

**Role:** aksi destruktif (Delete, Void Invoice, Cancel Order)

Background transparent, border 1px `--color-danger`, text `--color-danger`. Solid fill danger direservasi untuk konfirmasi modal destruktif saja (misal tombol "Ya, Hapus" di dalam confirmation dialog), supaya tombol danger solid tidak terlalu sering muncul dan kehilangan urgensi.

### Form Input

**Role:** text field, number field, date field, textarea

Background `--color-paper-white`, border 1px `--color-border-gray`, border-radius 8px, padding 8px 12px, `--text-body`. Focus state pakai `--shadow-focus-ring` plus border berubah jadi `--color-ink-black`. Placeholder `--color-ash-gray`. Label di atas input, `--text-label`, `--color-slate-gray`, margin-bottom 4px.

Validation error: border berubah jadi `--color-danger`, pesan error di bawah input `--text-caption` `--color-danger`.

### Select / Dropdown

**Role:** pilihan dari daftar terbatas (status filter, customer picker, item picker)

Styling sama dengan Form Input, ditambah chevron icon kanan. Untuk item/customer picker dengan banyak opsi, pakai searchable dropdown (Livewire component), bukan native `<select>` polos yang sulit dicari di daftar panjang.

### Stat Card

**Role:** ringkasan metrik di dashboard (Total Revenue Bulan Ini, Outstanding Invoice, Employee Aktif)

Background `--color-paper-white`, border 1px `--color-border-gray`, border-radius 12px, padding 16px, shadow `--shadow-subtle`. Label metrik di atas (`--text-label`, `--color-slate-gray`), angka besar di bawah (`--text-heading-lg`, weight 500), opsional delta indicator kecil (`--text-caption`, warna success/danger sesuai arah perubahan).

### Modal / Dialog

**Role:** konfirmasi aksi, form singkat tanpa pindah halaman (confirm delete, quick edit)

Overlay `rgba(23,25,28,0.4)`, dialog surface `--color-paper-white`, border-radius 12px, shadow `--shadow-elevated`, max-width 480px untuk konfirmasi, 640px untuk form. Title `--text-heading-sm`, body `--text-body`, footer berisi button group rata kanan (Secondary lalu Primary/Danger, urutan kiri-ke-kanan).

### Toast / Alert Banner

**Role:** notifikasi sesaat (Save successful, Failed to process payroll)

Posisi top-right, background sesuai semantic token bg variant, border-left 3px sesuai semantic token solid, radius 8px, auto-dismiss 4 detik untuk success, tetap tampil sampai di-dismiss manual untuk error.

### Empty State

**Role:** kondisi tabel/list kosong (belum ada sales order, belum ada employee)

Ilustrasi minimal atau icon besar `--color-ash-gray`, heading singkat (`--text-heading-sm`), deskripsi singkat (`--text-body-sm`, `--color-slate-gray`), tombol primary untuk aksi utama ("+ Buat Sales Order Pertama"). Ditempatkan di tengah area konten, padding vertikal besar (64px) supaya tidak terasa seperti bug/error.

### Tabs

**Role:** navigasi antar sub-view dalam satu halaman (Detail / Riwayat / Dokumen pada halaman Sales Order)

Underline style: tab aktif `--color-ink-black` text weight 500 dengan underline 2px `--color-ink-black`, tab non-aktif `--color-slate-gray` tanpa underline. Tidak pakai background pill untuk tab (beda dari badge), supaya tidak rancu secara visual dengan Status Badge.

### Tag / Category Label

Dipertahankan dari referensi asli dengan penyesuaian ukuran: no background, no border, `--text-caption`, `--color-ash-gray`. Dipakai untuk kategori non-status seperti kategori produk atau department, bukan untuk status transaksi (itu domain Status Badge).

### Icon Library

Pakai Lucide icons via `mallardduck/blade-lucide-icons` (Blade component `<x-lucide-{name}>`, server-rendered SVG), bukan JS-based Lucide. Alasan: kompatibel dengan Livewire partial re-render tanpa perlu re-init JS.

---

## Do's and Don'ts

### Do

- Pakai satu typeface (Sohne/Inter) di seluruh aplikasi kerja untuk konsistensi rendering dan kecepatan load, simpan serif (kalau memang mau dipakai) hanya untuk halaman login/branding.
- Pakai tabular numeric untuk semua kolom angka finansial di tabel, supaya digit berbaris rapi saat discan cepat oleh user.
- Reservasi warna semantik (success/warning/danger/info) hanya untuk badge status, alert, dan validation, supaya makna warna tetap konsisten dan tidak diinterpretasikan campur aduk dengan elemen dekoratif.
- Gunakan radius kecil (8px) untuk button dan input, radius sedang (12px) untuk card, radius pill hanya untuk badge kecil, ini kebalikan dari referensi asli yang pill untuk button.
- Sediakan empty state yang jelas untuk setiap tabel/list, jangan biarkan halaman kosong tanpa penjelasan atau call-to-action.
- Pastikan focus-ring terlihat jelas di semua elemen interaktif untuk keyboard navigation, penting untuk aplikasi kerja yang dipakai berjam-jam oleh staff.

### Don't

- Jangan pakai heading besar (44px+) di halaman dashboard/tabel, itu pattern hero landing page, bukan aplikasi kerja. Page title cukup 22-28px.
- Jangan pakai pill-shape untuk button aksi (Save, Delete, dst), radius besar cocok untuk CTA marketing tapi bikin button aksi kerja terlihat kurang tegas saat berjajar banyak dalam satu toolbar.
- Jangan pakai warna semantik status untuk elemen non-status (misal jangan warnai card dekoratif dengan warna danger cuma karena "terlihat menarik"), itu merusak kepercayaan user terhadap makna warna di seluruh sistem.
- Jangan campur Tag/Category Label dengan Status Badge secara visual, keduanya harus mudah dibedakan sekilas karena fungsinya beda (kategori vs state).
- Jangan gunakan shadow berat di card konten biasa, sesuai semangat referensi asli, shadow direservasi untuk elemen yang benar-benar elevated (modal, dropdown, popover).

---

## Quick Start

### CSS Custom Properties

```css
:root {
    /* Colors - Neutral */
    --color-ink-black: #17191c;
    --color-paper-white: #ffffff;
    --color-mist-gray: #f2f2f3;
    --color-fog-white: #fafafb;
    --color-slate-gray: #777b86;
    --color-ash-gray: #979799;
    --color-border-gray: #e5e5e7;
    --color-blush-peach: #fbe1d1;
    --color-sienna-brown: #5d2a1a;

    /* Colors - Semantic Status */
    --color-success: #1a7f4e;
    --color-success-bg: #e6f4ec;
    --color-warning: #b5750a;
    --color-warning-bg: #fdf1de;
    --color-danger: #c0362c;
    --color-danger-bg: #faeae8;
    --color-info: #2563a8;
    --color-info-bg: #e9f1fa;

    /* Typography */
    --font-sohne:
        "Sohne", "Inter", ui-sans-serif, system-ui, -apple-system, "Segoe UI",
        Roboto, sans-serif;

    --text-caption: 12px;
    --leading-caption: 1.4;
    --text-label: 13px;
    --leading-label: 1.4;
    --text-body-sm: 14px;
    --leading-body-sm: 1.5;
    --text-body: 15px;
    --leading-body: 1.5;
    --text-body-lg: 16px;
    --leading-body-lg: 1.5;
    --text-heading-sm: 18px;
    --leading-heading-sm: 1.3;
    --text-heading: 22px;
    --leading-heading: 1.3;
    --text-heading-lg: 28px;
    --leading-heading-lg: 1.25;

    --font-weight-regular: 400;
    --font-weight-w450: 450;
    --font-weight-medium: 500;
    --font-weight-semibold: 600;

    /* Spacing */
    --spacing-4: 4px;
    --spacing-8: 8px;
    --spacing-12: 12px;
    --spacing-16: 16px;
    --spacing-20: 20px;
    --spacing-24: 24px;
    --spacing-32: 32px;
    --spacing-48: 48px;
    --spacing-64: 64px;

    /* Layout */
    --sidebar-width: 240px;
    --sidebar-width-collapsed: 64px;
    --topbar-height: 56px;
    --card-padding: 16px;
    --table-cell-padding-y: 12px;
    --table-cell-padding-x: 16px;
    --section-gap: 24px;

    /* Border Radius */
    --radius-card: 12px;
    --radius-input: 8px;
    --radius-button: 8px;
    --radius-badge: 9999px;
    --radius-table: 8px;

    /* Shadows */
    --shadow-subtle: 0 1px 2px rgba(23, 25, 28, 0.06);
    --shadow-elevated: 0 4px 12px rgba(23, 25, 28, 0.1);
    --shadow-focus-ring: 0 0 0 3px rgba(37, 99, 168, 0.25);
}
```

### Tailwind v3

```css
@theme {
    --color-ink-black: #17191c;
    --color-paper-white: #ffffff;
    --color-mist-gray: #f2f2f3;
    --color-fog-white: #fafafb;
    --color-slate-gray: #777b86;
    --color-ash-gray: #979799;
    --color-border-gray: #e5e5e7;
    --color-blush-peach: #fbe1d1;
    --color-sienna-brown: #5d2a1a;

    --color-success: #1a7f4e;
    --color-success-bg: #e6f4ec;
    --color-warning: #b5750a;
    --color-warning-bg: #fdf1de;
    --color-danger: #c0362c;
    --color-danger-bg: #faeae8;
    --color-info: #2563a8;
    --color-info-bg: #e9f1fa;

    --font-sohne:
        "Sohne", "Inter", ui-sans-serif, system-ui, -apple-system, "Segoe UI",
        Roboto, sans-serif;

    --text-caption: 12px;
    --text-label: 13px;
    --text-body-sm: 14px;
    --text-body: 15px;
    --text-body-lg: 16px;
    --text-heading-sm: 18px;
    --text-heading: 22px;
    --text-heading-lg: 28px;

    --spacing-4: 4px;
    --spacing-8: 8px;
    --spacing-12: 12px;
    --spacing-16: 16px;
    --spacing-20: 20px;
    --spacing-24: 24px;
    --spacing-32: 32px;
    --spacing-48: 48px;
    --spacing-64: 64px;

    --radius-card: 12px;
    --radius-input: 8px;
    --radius-button: 8px;
    --radius-badge: 9999px;

    --shadow-subtle: 0 1px 2px rgba(23, 25, 28, 0.06);
    --shadow-elevated: 0 4px 12px rgba(23, 25, 28, 0.1);
    --shadow-focus-ring: 0 0 0 3px rgba(37, 99, 168, 0.25);
}

// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Modules/**/resources/views/**/*.blade.php',
        './app/Modules/**/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            colors: {
                'ink-black': '#17191c',
                'paper-white': '#ffffff',
                'mist-gray': '#f2f2f3',
                'fog-white': '#fafafb',
                'slate-gray': '#777b86',
                'ash-gray': '#979799',
                'border-gray': '#e5e5e7',
                'blush-peach': '#fbe1d1',
                'sienna-brown': '#5d2a1a',
                success: { DEFAULT: '#1a7f4e', bg: '#e6f4ec' },
                warning: { DEFAULT: '#b5750a', bg: '#fdf1de' },
                danger: { DEFAULT: '#c0362c', bg: '#faeae8' },
                info: { DEFAULT: '#2563a8', bg: '#e9f1fa' },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                caption: ['12px', { lineHeight: '1.4' }],
                label: ['13px', { lineHeight: '1.4' }],
                'body-sm': ['14px', { lineHeight: '1.5' }],
                body: ['15px', { lineHeight: '1.5' }],
                'body-lg': ['16px', { lineHeight: '1.5' }],
                'heading-sm': ['18px', { lineHeight: '1.3' }],
                heading: ['22px', { lineHeight: '1.3' }],
                'heading-lg': ['28px', { lineHeight: '1.25' }],
            },
            spacing: {
                18: '4.5rem',
            },
            borderRadius: {
                card: '12px',
                input: '8px',
                button: '8px',
                badge: '9999px',
                table: '8px',
            },
            boxShadow: {
                subtle: '0 1px 2px rgba(23,25,28,0.06)',
                elevated: '0 4px 12px rgba(23,25,28,0.10)',
                'focus-ring': '0 0 0 3px rgba(37,99,168,0.25)',
            },
            width: {
                sidebar: '240px',
                'sidebar-collapsed': '64px',
            },
            height: {
                topbar: '56px',
            },
        },
    },

    plugins: [forms],
};

```

---

Dokumen terkait: `PRD.md`, `ARCHITECTURE.md`, `ROADMAP.md`, `TASKS.md`.
