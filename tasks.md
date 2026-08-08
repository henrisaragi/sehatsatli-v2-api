# Tasks — Sehat Satli Flutter Mobile App

Referensi endpoint lengkap: [API_ENDPOINTS.md](API_ENDPOINTS.md).
Backend: REST API Laravel Passport (Bearer token), base URL `{APP_URL}/api/`.

## Desain UI

Mockup visual: **[sehatsatli-ui-mockup.html](sehatsatli-ui-mockup.html)** — buka di browser untuk lihat tampilan tiap layar.

### Identitas visual

| Token | Nilai | Pemakaian |
|---|---|---|
| `primary` (Forest) | `#1F4B3F` | App bar, tombol utama, ikon aktif |
| `primary-strong` | `#163A30` | Pressed/hover state tombol utama |
| `amber` (Field Amber) | `#D9922E` | Status "menunggu sinkron", highlight, badge training-mode |
| `success` | `#3C8558` | Status terverifikasi/tersinkron |
| `danger` | `#C1432E` | Prioritas penyakit/zoonosis, tombol hapus, validasi error |
| `surface` | `#F7F5EE` | Background layar (light) |
| `surface-alt` | `#EFEBDE` | Card/section alternatif |
| `ink` | `#1E2620` | Teks utama |
| `muted` | `#62705F` | Teks sekunder/label |
| `border` | `#DAD5C4` | Divider, outline input |

- **Font UI/body**: system font stack (`-apple-system, Segoe UI, Roboto, Helvetica, Arial`) — konsisten dengan default Material di Flutter, ringan untuk perangkat lapangan.
- **Font judul/app-bar**: serif ledger-style (`Iowan Old Style/Palatino/Georgia`) — dipakai terbatas di judul layar & app bar untuk kesan dokumen resmi/instansi, bukan di body teks.
- **Font data**: monospace (`SF Mono/Roboto Mono/Consolas`) — khusus untuk `report_code`, koordinat GPS, timestamp, id sinkronisasi — supaya angka/kode selalu rata & gampang di-scan petugas.
- Prinsip: app dipakai petugas lapangan (outdoor, koneksi tidak stabil) → kontras tinggi, target sentuh besar, status sinkronisasi harus selalu terlihat (bukan disembunyikan di menu).

### Navigasi utama (bottom nav, 4 tab)

1. **Beranda** — ringkasan, quick action "Buat Laporan", status sync outbox
2. **Laporan** — list laporan kasus (`getGeneralReportMobile`), filter status & training mode
3. **Notifikasi** — inbox (`user-inbox/*`) + riwayat push FCM
4. **Profil** — data diri, ganti password, upload foto, logout

Form buat laporan diakses sebagai **flow terpisah** (bukan tab) dari FAB di Beranda/Laporan — full-screen stepper, agar tidak tercampur dengan navigasi utama saat mengisi form panjang.

### Spesifikasi per layar

| Layar | Komponen kunci | Sumber data |
|---|---|---|
| **Login** | Field username (email/HP), password, tombol masuk full-width, teks bantuan format username | `POST /auth/login-app` |
| **Beranda** | Kartu status sync ("3 laporan menunggu dikirim"), tombol besar "Buat Laporan Baru", ringkasan bulan ini | `dashboard/get-*`, local outbox |
| **List Laporan** | Kartu per laporan: kode laporan (monospace), spesies, tanggal, chip status (`Draft` abu / `Menunggu Sync` amber / `Terverifikasi` hijau / `Prioritas` merah), infinite scroll | `general-reports/getGeneralReportMobile` |
| **Detail Laporan** | Timeline vertikal tahap (Pelapor → Lokasi → Spesies → Diagnosis → Verifikasi → Lab → Investigasi), tiap tahap collapsible, galeri foto | `general-reports/getOne` |
| **Form Laporan (stepper)** | Progress indicator 4–7 step sesuai tahap terisi, tombol "Simpan Draft" di tiap step (tulis ke outbox lokal), tombol "Kirim" di step akhir | `general-reports/saveGeneralReportMobile` |
| **Step Lokasi** | Dropdown cascading provinsi→kab→kec→desa (dari cache `/location-user`), tombol "Ambil Lokasi GPS", peta preview kecil | cache lokal + GPS device |
| **Notifikasi/Inbox** | List pesan, unread dot, pull-to-refresh | `user-inbox/getAll` |
| **Profil** | Avatar + upload, nama/instansi (read-only sesuai bug backend `update-user`), tombol ganti password, tombol logout | `users/getOne`, `auth/forgot-password`, `auth/logout` |

## Fase 0 — Setup Proyek

- [ ] Inisialisasi project Flutter (`flutter create sehatsatli_mobile`)
- [ ] Setup state management (pilih: Riverpod/Bloc/Provider)
- [ ] Setup HTTP client (dio) + interceptor untuk attach `Authorization: Bearer {token}` otomatis
- [ ] Setup local storage:
  - [ ] Secure storage untuk `access_token` (flutter_secure_storage)
  - [ ] Local DB (Hive/Drift/SQLite) untuk cache master data, lokasi, dan **outbox laporan offline**
- [ ] Setup Firebase project + `google-services.json`/`GoogleService-Info.plist` untuk FCM
- [ ] Struktur folder: `core/`, `features/`, `data/` (repositories), `domain/`, `presentation/`

## Fase 1 — Auth

- [ ] Login screen → `POST /auth/login-app` (`username` = email/HP, `password`)
- [ ] Simpan `access_token` + objek `user` hasil login
- [ ] Ganti password wajib saat `user.reset_password == true` → `POST /auth/forgot-password`
- [ ] Logout → `POST /auth/logout`, hapus token lokal
- [ ] Auto-logout/refresh flow saat token invalid (401)
- [ ] Kirim FCM token setelah login → `POST /users/user/token`

## Fase 2 — Master Data & Cache Lokal

- [ ] Load & cache saat pertama login / app start:
  - [ ] `POST /masters` (dropdown lookup)
  - [ ] `POST /location-user` (wilayah admin sesuai UPT user — lebih ringan dari `/locations`)
  - [ ] `POST /diseases/getAll`, `POST /species/getAll`, `POST /upts/getAll`
- [ ] Mekanisme refresh cache (manual pull-to-refresh + TTL)
- [ ] Fallback baca dari cache lokal saat offline

## Fase 3 — Modul Laporan Kasus (fitur utama)

- [ ] List laporan (paginated) → `POST /general-reports/getGeneralReportMobile` (`{ id: 0|1 }` untuk mode training/produksi), infinite scroll pakai `page`
- [ ] Detail laporan → `POST /general-reports/getOne`
- [ ] Form buat laporan **multi-step** sesuai payload nested (`reporter`, `locations`, `species`, `diagnoses`, opsional `verification`/`lab`/`investigation`):
  - [ ] Step 1: Data pelapor
  - [ ] Step 2: Lokasi kejadian (province/district/subdistrict/village cascading dari cache lokasi + koordinat GPS)
  - [ ] Step 3: Data spesies
  - [ ] Step 4: Diagnosis awal + foto (base64) via kamera/galeri
  - [ ] Step 5 (opsional): Verifikasi dokter, hasil lab, investigasi lanjutan
- [ ] Generate `client_id` (UUID) di device saat draft dibuat pertama kali — dipakai sebagai identitas sync
- [ ] Simpan draft ke local outbox sebelum submit (mendukung isi offline)
- [ ] Submit → `POST /general-reports/saveGeneralReportMobile`, retry idempotent pakai `client_id` yang sama
- [ ] Hapus laporan → `POST /general-reports/deleteGeneralReportMobile` (`{ client_id }`)
- [ ] **Background sync worker**: kirim ulang item outbox saat konektivitas kembali (cek `GET /ping` dulu)
- [ ] Handling upload foto: compress sebelum encode base64 (batasi ukuran payload)

## Fase 4 — Laporan Masyarakat (publik, tanpa login)

- [ ] Form "Lapor Cepat" tanpa perlu akun → `POST /community-reports/save`
- [ ] List laporan masyarakat (opsional, untuk petugas) → `POST /community-reports/getAll` / `getOne`

## Fase 5 — Profil & Notifikasi

- [ ] Halaman profil: tampilkan data dari objek `user` hasil login / `POST /users/getOne`
- [ ] Upload foto profil → `POST /users/uploadPhotoMobile` (base64)
- [ ] ⚠️ Update profil (`POST /update-user`) saat ini **broken di backend** (lihat catatan di API_ENDPOINTS.md) — koordinasikan fix backend sebelum implementasi UI simpan profil, atau beri tahu tim backend lebih dulu.
- [ ] Inbox notifikasi in-app → `POST /user-inbox/getAll`, `getOne`, tandai dibaca via `save` (`read: true`)
- [ ] Terima push notification (FCM) → tampilkan sebagai local notification + refresh inbox/list laporan
- [ ] Daftar dokter hewan (untuk pilih di form verifikasi) → `POST /dokter/getAll`

## Fase 6 — Dashboard (opsional, jika scope termasuk ringkasan statistik)

- [ ] Ringkasan jumlah laporan per bulan → `POST /dashboard/get-totalLaporan`
- [ ] Peta sebaran kasus → `POST /dashboard/get-location`
- [ ] Statistik suspek/non-suspek/lab → endpoint `dashboard/get-*` lainnya

## Fase 7 — QA & Rilis

- [ ] Uji alur offline: isi laporan tanpa koneksi → kirim otomatis saat online kembali, tidak duplikat (verifikasi `client_id`)
- [ ] Uji upload foto besar (ukuran & waktu upload base64)
- [ ] Uji push notification (foreground/background/terminated)
- [ ] Uji multi-role (`user_level`, `upt_id`) — pastikan data ter-scope sesuai UPT sesuai perilaku backend
- [ ] Setup app icon, splash screen, versioning
- [ ] Build & submit ke Play Store / App Store

---

## Catatan Risiko dari Audit Backend

- `POST /update-user` tidak benar-benar menyimpan perubahan (bug logic `array_key_exists('id', ...)`) — lihat API_ENDPOINTS.md §5.
- `POST /dokter/getOne` filter `is_doctor = 0` kemungkinan terbalik dari `getAll` (`is_doctor = 1`).
- Beberapa endpoint punya duplikat versi "publik" tanpa auth (`generalReport/getAll`, `users/sendNotifications`, `community-reports/delete` hard-delete) — **jangan dipakai dari mobile app**, pakai versi yang ditandai ✅ di API_ENDPOINTS.md.
- Response envelope tidak 100% konsisten (`success`/`data` vs array mentah) — selalu defensif saat parsing di Flutter (null-safety pada setiap field).
