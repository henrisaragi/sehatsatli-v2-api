# System Palette & Guide: Sehat Satli

> **Sistem Informasi Kesehatan Satwa Liar**  
> Panduan Sistem Desain Visual dan Penerapan Warna

---

## 1. Ekstraksi Warna Utama

Berikut adalah warna-warna dominan yang diekstraksi langsung dari logo **Sehat Satli**:

| Kategori       | Nama Warna    | Kode HEX  | RGB                  | Deskripsi & Penggunaan                                                                             |
| :------------- | :------------ | :-------- | :------------------- | :------------------------------------------------------------------------------------------------- |
| **Primary**    | Hijau Utama   | `#17B273` | `rgb(23, 178, 115)`  | Diambil dari elemen globe & teks "SEHAT". Digunakan untuk tombol utama, header, dan elemen aktif.  |
| **Secondary**  | Oranye Utama  | `#E67E22` | `rgb(230, 126, 34)`  | Diambil dari elemen teks "SATLI". Digunakan untuk aksen, sorotan (highlight), dan penanda penting. |
| **Text Dark**  | Abu-abu Gelap | `#555555` | `rgb(85, 85, 85)`    | Diambil dari deskripsi subtitle logo. Digunakan untuk teks deskriptif / caption.                   |
| **Background** | Putih Murni   | `#FFFFFF` | `rgb(255, 255, 255)` | Warna dasar/latar latar belakang logo.                                                             |

---

## 2. Palet Warna UI & Status (SASS / CSS Variables)

Variabel SASS/CSS yang dikembangkan untuk kebutuhan UI (User Interface) aplikasi/situs web:

```scss
// Theme Colors (Logo-based)
$primary: #17b273;
$secondary: #e67e22;

// State / Utility Colors
$success: #17b273; // Sama dengan $primary$info:          #2980B9; // Biru Pelengkap
$warning: #e67e22; // Sama dengan $secondary$danger:        #C0392B; // Merah
$light: #f8f9fa; // Off-white / Gray Light
$dark: #343a40; // Slate Dark

// Light Variants (Background Alert / Badges)
$primary-light: #e7f7f1;
$secondary-light: #fdeddf;
$info-light: #ebf5fb;
$danger-light: #fdedec;
```
