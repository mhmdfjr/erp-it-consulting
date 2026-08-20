# Design System

## Sistem ERP - Perusahaan IT Service & Consulting

> modern, data-dense, vibrant-violet workspace

Status: Draft v1.1
Terakhir diperbarui: 2026-08-20

**Theme:** light

Design system ini disesuaikan untuk konteks internal tool berbasis data (ERP) yang menggabungkan struktur dashboard fungsional yang bersih dengan palet aksen modern berbasis **Deep Violet & Coral Palette** (`#8100D1`, `#B500B2`, `#FF52A0`, `#FFA47F`). Pendekatan ini mempertahankan keterbacaan data tinggi dengan palet netral yang tenang, sambil memberikan identitas brand yang kuat dan hierarki interaktif yang tajam melalui gradasi aksen ungu–magenta–pink–peach.

Halaman ERP dibangun sebagai dashboard shell standar: sidebar navigasi kiri, top bar dengan breadcrumb dan user menu, konten utama berupa tabel/form/card di kanan untuk mengakomodasi alur kerja harian secara optimal.

---

## Tokens - Colors

### Brand & Accent Palette (Color Hunt #8100d1 / #b500b2 / #ff52a0 / #ffa47f)

| Name               | Value     | Token                   | Role                                                                                 |
| ------------------ | --------- | ----------------------- | ------------------------------------------------------------------------------------ |
| Royal Violet       | `#8100d1` | `--color-primary`       | Brand primary, active sidebar indicator, primary button background, key emphasis     |
| Royal Violet Hover | `#6d00b0` | `--color-primary-hover` | State hover untuk button primary dan active elements                                 |
| Deep Magenta       | `#b500b2` | `--color-secondary`     | Secondary brand accent, tab active indicator, focused interactive borders            |
| Vibrant Pink       | `#ff52a0` | `--color-accent-pink`   | Highlight tags, special indicator, promotional badge, secondary action badge         |
| Coral Peach        | `#ffa47f` | `--color-accent-peach`  | Warm accent untuk highlight non-status (badge fitur baru, info highlight, warm tags) |
| Violet Tint BG     | `#f6edfd` | `--color-primary-tint`  | Background item aktif pada sidebar, selected row tabel, light highlight container    |
| Peach Tint BG      | `#fff2ec` | `--color-peach-tint`    | Background untuk chip/badge aksen peach                                              |

### Neutral Workspace Colors

| Name        | Value     | Token                 | Role                                                         |
| ----------- | --------- | --------------------- | ------------------------------------------------------------ |
| Ink Black   | `#15131b` | `--color-ink-black`   | Primary text, deep dark elements                             |
| Paper White | `#ffffff` | `--color-paper-white` | Page canvas, card surface, button text di atas primary solid |
| Mist Gray   | `#f4f3f7` | `--color-mist-gray`   | Secondary background, input fill, hover state row tabel      |
| Fog White   | `#faf9fc` | `--color-fog-white`   | Sidebar background, alternating table row                    |
| Slate Gray  | `#6f6b7d` | `--color-slate-gray`  | Body text sekunder, helper text, label form                  |
| Ash Gray    | `#9e9aa8` | `--color-ash-gray`    | Placeholder text, metadata tersier, separator line           |
| Border Gray | `#e7e5ed` | `--color-border-gray` | Hairline border tabel, input, card                           |

### Semantic Status Colors

Ditambahkan agar ERP mengkomunikasikan state data finansial, inventaris, dan operasional dengan jelas dan tegas.

| Name             | Value     | Token                | Role                                           |
| ---------------- | --------- | -------------------- | ---------------------------------------------- |
| Success Green    | `#15803d` | `--color-success`    | Invoice lunas, employee aktif, stock cukup     |
| Success Green BG | `#ecfdf5` | `--color-success-bg` | Background badge success                       |
| Warning Amber    | `#b45309` | `--color-warning`    | Invoice mendekati jatuh tempo, stock menipis   |
| Warning Amber BG | `#fffbeb` | `--color-warning-bg` | Background badge warning                       |
| Danger Red       | `#b91c1c` | `--color-danger`     | Invoice overdue, stock habis, validation error |
| Danger Red BG    | `#fef2f2` | `--color-danger-bg`  | Background badge danger                        |
| Info Violet      | `#8100d1` | `--color-info`       | Status netral informatif (draft, in progress)  |
| Info Violet BG   | `#f6edfd` | `--color-info-bg`    | Background badge info                          |

Aturan pakai: warna semantik **hanya** untuk badge status, alert banner, dan validation message. Tidak dipakai untuk dekorasi sembarangan, agar pemaknaan indikator data oleh user tetap akurat dan konsisten.

---

## Tokens - Typography

### Sohne / Inter - Body, UI, dan Navigation

- **Typeface**: `Inter`, atau `"Sohne"`, fallback `ui-sans-serif, system-ui, -apple-system, sans-serif`.
- **Weights**: 400 (Regular), 450 (Book/Medium Light), 500 (Medium), 600 (Semi-Bold)
- **Letter spacing**: Default / 0 pada semua skala teks.

### Type Scale

| Role       | Size | Line Height | Weight | Token               | Pemakaian                                     |
| ---------- | ---- | ----------- | ------ | ------------------- | --------------------------------------------- |
| caption    | 12px | 1.4         | 400    | `--text-caption`    | Timestamp, footnote tabel                     |
| label      | 13px | 1.4         | 500    | `--text-label`      | Label form, kolom header tabel                |
| body-sm    | 14px | 1.5         | 400    | `--text-body-sm`    | Body text sekunder, helper text               |
| body       | 15px | 1.5         | 400    | `--text-body`       | Body text utama, isi tabel, isi form          |
| body-lg    | 16px | 1.5         | 450    | `--text-body-lg`    | Emphasis body, nilai metrik di card           |
| heading-sm | 18px | 1.3         | 500    | `--text-heading-sm` | Card title, section heading dalam page        |
| heading    | 22px | 1.3         | 600    | `--text-heading`    | Page title (misal "Sales Order #SO-2026-001") |
| heading-lg | 28px | 1.25        | 600    | `--text-heading-lg` | Dashboard summary title                       |

Font tabular numeric (`font-variant-numeric: tabular-nums`) wajib dipakai untuk semua angka numerik & finansial di tabel (harga, kuantitas, saldo) agar digit sejajar sempurna saat scanning data.

---

## Tokens - Spacing & Shapes

**Base unit:** 4px
**Density:** compact-comfortable

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

| Element | Value         | Catatan                                                                        |
| ------- | ------------- | ------------------------------------------------------------------------------ |
| cards   | 12px          | Clean & balanced untuk modul dashboard padat                                   |
| inputs  | 8px           | Standar ergonomis input field                                                  |
| buttons | 8px           | Radius fungsional, rapi saat disusun berdampingan (Primary, Secondary, Action) |
| badges  | 9999px (pill) | Khusus pill badge agar kontras dengan button fungsional                        |
| table   | 8px           | Radius container pembungkus tabel                                              |

### Shadows

| Name       | Value                             | Token                 | Pemakaian                                                |
| ---------- | --------------------------------- | --------------------- | -------------------------------------------------------- |
| subtle     | `0 1px 2px rgba(21,19,27,0.05)`   | `--shadow-subtle`     | Card default                                             |
| elevated   | `0 4px 14px rgba(129,0,209,0.08)` | `--shadow-elevated`   | Modal, dropdown, popover                                 |
| focus-ring | `0 0 0 3px rgba(129,0,209,0.20)`  | `--shadow-focus-ring` | Focus ring input & button dengan aksen `--color-primary` |

### Layout Dimensions

- **Sidebar width**: 240px (expanded), 64px (collapsed)
- **Top bar height**: 56px
- **Content width**: Fluid / Full Width minus Sidebar
- **Card padding**: 16px
- **Table cell padding**: 12px vertikal, 16px horizontal
- **Section gap**: 24px

---

## Components

### Sidebar Navigation

**Role:** Navigasi utama antar modul ERP

Background `--color-fog-white`, border-right 1px solid `--color-border-gray`. Item navigasi aktif memiliki background `--color-primary-tint`, teks `--color-primary` (weight 500), serta indikator garis vertikal 3px `--color-primary` di sisi kiri. Section heading kategori menggunakan `--text-caption` dengan warna `--color-ash-gray`.

### Top Bar

**Role:** Breadcrumb, context status, global search, dan user profile

Background `--color-paper-white`, border-bottom hairline `--color-border-gray`, tinggi 56px. Breadcrumb menggunakan `--text-body-sm` dengan separator `/`, avatar user diberi subtle border atau ring aksen `--color-primary-tint`.

### Data Table

**Role:** Menampilkan dataset (sales order, invoice, ledger, employee)

- **Header row**: background `--color-fog-white`, text `--text-label`, color `--color-slate-gray`, border-bottom `--color-border-gray`.
- **Body row**: `--text-body`, border-bottom hairline `--color-border-gray`, hover background `--color-mist-gray`.
- **Selected row**: background `--color-primary-tint`.
- Kolom finansial rata kanan dengan `tabular-nums`.

### Status Badge

**Role:** Menandai lifecycle state data

Pill shape (`border-radius: 9999px`), padding 2px 10px, `--text-caption` weight 500. Menggunakan pasangan warna solid dan background tint:

- `draft` / `in review`: text `--color-info` (`#8100d1`), bg `--color-info-bg` (`#f6edfd`)
- `paid` / `completed` / `active`: text `--color-success` (`#15803d`), bg `--color-success-bg` (`#ecfdf5`)
- `pending` / `due soon`: text `--color-warning` (`#b45309`), bg `--color-warning-bg` (`#fffbeb`)
- `overdue` / `cancelled`: text `--color-danger` (`#b91c1c`), bg `--color-danger-bg` (`#fef2f2`)
- `new feature` / `special tag`: text `#9c3f15`, bg `--color-peach-tint` (`#fff2ec`) dengan subtle border `#ffa47f`

### Button - Primary

**Role:** Aksi utama (Save, Submit, Create Order, Export)

Background `--color-primary` (`#8100d1`), text `--color-paper-white`, border-radius 8px, padding 8px 16px, `--text-body` weight 500. Hover: background `--color-primary-hover` (`#6d00b0`). Focus: `--shadow-focus-ring`.

### Button - Secondary

**Role:** Aksi sekunder (Cancel, Back, Filter)

Background transparent, border 1px `--color-border-gray`, text `--color-ink-black`, hover background `--color-mist-gray`.

### Button - Danger

**Role:** Aksi destruktif (Delete, Void, Terminate)

Background transparent, border 1px `--color-danger`, text `--color-danger`. Solid background `--color-danger` hanya digunakan pada tombol konfirmasi modal final.

### Form Input & Select

**Role:** Input form data, filter bar, datepicker

Background `--color-paper-white`, border 1px `--color-border-gray`, border-radius 8px, padding 8px 12px, text `--color-ink-black`. Focus state mengaktifkan border `--color-primary` (`#8100d1`) dan `--shadow-focus-ring`. Label form menggunakan `--text-label` dengan warna `--color-slate-gray`.

### Stat Card

**Role:** Ringkasan metrik dashboard (Total Revenue, Cashflow, Active Projects)

Background `--color-paper-white`, border 1px `--color-border-gray`, border-radius 12px, padding 16px, shadow `--shadow-subtle`. Label metrik di atas (`--text-label`, `--color-slate-gray`), angka utama (`--text-heading-lg`, weight 600, color `--color-ink-black`), dan aksen tren / badge delta indikator.

### Tabs

**Role:** Navigasi sub-view (Overview / Details / Invoices / Logs)

Underline style: tab aktif memiliki text `--color-primary` (`#8100d1`) dengan border-bottom 2px `--color-primary`. Tab inactive memakai text `--color-slate-gray` tanpa border-bottom.

---

## Quick Start

### CSS Custom Properties

```css
:root {
    /* Brand Palette - Color Hunt #8100d1 / #b500b2 / #ff52a0 / #ffa47f */
    --color-primary: #8100d1;
    --color-primary-hover: #6d00b0;
    --color-secondary: #b500b2;
    --color-accent-pink: #ff52a0;
    --color-accent-peach: #ffa47f;
    --color-primary-tint: #f6edfd;
    --color-peach-tint: #fff2ec;

    /* Neutrals */
    --color-ink-black: #15131b;
    --color-paper-white: #ffffff;
    --color-mist-gray: #f4f3f7;
    --color-fog-white: #faf9fc;
    --color-slate-gray: #6f6b7d;
    --color-ash-gray: #9e9aa8;
    --color-border-gray: #e7e5ed;

    /* Semantic Status */
    --color-success: #15803d;
    --color-success-bg: #ecfdf5;
    --color-warning: #b45309;
    --color-warning-bg: #fffbeb;
    --color-danger: #b91c1c;
    --color-danger-bg: #fef2f2;
    --color-info: #8100d1;
    --color-info-bg: #f6edfd;

    /* Typography */
    --font-sans:
        "Inter", "Sohne", ui-sans-serif, system-ui, -apple-system, "Segoe UI",
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
    --shadow-subtle: 0 1px 2px rgba(21, 19, 27, 0.05);
    --shadow-elevated: 0 4px 14px rgba(129, 0, 209, 0.08);
    --shadow-focus-ring: 0 0 0 3px rgba(129, 0, 209, 0.2);
}
```

### Tailwind Config (`tailwind.config.js`)

```javascript
import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./app/Modules/**/resources/views/**/*.blade.php",
        "./app/Modules/**/Livewire/**/*.php",
    ],

    theme: {
        extend: {
            colors: {
                // Color Hunt Palette: #8100d1 #b500b2 #ff52a0 #ffa47f
                primary: {
                    DEFAULT: "#8100d1",
                    hover: "#6d00b0",
                    tint: "#f6edfd",
                },
                secondary: {
                    DEFAULT: "#b500b2",
                },
                accent: {
                    pink: "#ff52a0",
                    peach: "#ffa47f",
                    "peach-tint": "#fff2ec",
                },
                // Neutrals
                "ink-black": "#15131b",
                "paper-white": "#ffffff",
                "mist-gray": "#f4f3f7",
                "fog-white": "#faf9fc",
                "slate-gray": "#6f6b7d",
                "ash-gray": "#9e9aa8",
                "border-gray": "#e7e5ed",
                // Semantic Status
                success: { DEFAULT: "#15803d", bg: "#ecfdf5" },
                warning: { DEFAULT: "#b45309", bg: "#fffbeb" },
                danger: { DEFAULT: "#b91c1c", bg: "#fef2f2" },
                info: { DEFAULT: "#8100d1", bg: "#f6edfd" },
            },
            fontFamily: {
                sans: ["Inter", "Sohne", ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                caption: ["12px", { lineHeight: "1.4" }],
                label: ["13px", { lineHeight: "1.4" }],
                "body-sm": ["14px", { lineHeight: "1.5" }],
                body: ["15px", { lineHeight: "1.5" }],
                "body-lg": ["16px", { lineHeight: "1.5" }],
                "heading-sm": ["18px", { lineHeight: "1.3" }],
                heading: ["22px", { lineHeight: "1.3" }],
                "heading-lg": ["28px", { lineHeight: "1.25" }],
            },
            borderRadius: {
                card: "12px",
                input: "8px",
                button: "8px",
                badge: "9999px",
                table: "8px",
            },
            boxShadow: {
                subtle: "0 1px 2px rgba(21,19,27,0.05)",
                elevated: "0 4px 14px rgba(129,0,209,0.08)",
                "focus-ring": "0 0 0 3px rgba(129,0,209,0.20)",
            },
            width: {
                sidebar: "240px",
                "sidebar-collapsed": "64px",
            },
            height: {
                topbar: "56px",
            },
        },
    },

    plugins: [forms],
};
```

---

Dokumen terkait: `PRD.md`, `ARCHITECTURE.md`, `ROADMAP.md`, `TASKS.md`.
