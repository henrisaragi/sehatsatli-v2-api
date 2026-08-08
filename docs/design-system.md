# SehatSatli — Dokumentasi Proses Bisnis & Domain API

> Disusun dari hasil membaca `routes/api.php`, `app/Http/Controllers/*`, dan `app/Models/*`.
> Tujuan: menjadi acuan utama untuk membangun **frontend mobile app** (Flutter/React Native/dst) di atas API Laravel ini.
> Untuk token warna & visual, lihat [`palette_sehat_satli.md`](./palette_sehat_satli.md) (warna diambil dari logo Sehat Satli: hijau `#17B273`, oranye `#E67E22`).

---

## 1. Ringkasan Domain

**SehatSatli** ("Sistem Informasi Kesehatan Satwa Liar") adalah aplikasi pelaporan dan investigasi kasus kesehatan **satwa liar** (wildlife health surveillance), dipakai oleh unit-unit KLHK (Kementerian Lingkungan Hidup dan Kehutanan): Pusat, BKSDA, Taman Nasional (TN), Lembaga Konservasi Umum (LU), dan Lembaga Konservasi Khusus (LK).

Alur inti aplikasi: **petugas lapangan melaporkan kasus satwa sakit/mati** → laporan diperkaya bertahap (investigasi, verifikasi dokter hewan, hasil lab) → jika hasil menunjukkan kasus prioritas (penyakit prioritas / satwa dilindungi) → **notifikasi push (FCM)** dikirim ke pihak terkait (kepala UPT, Pusat, dokter hewan).

Aplikasi punya dua sisi konsumen:
- **Web admin** (dashboard, manajemen master data) — di luar cakupan mobile app.
- **Mobile app** (petugas lapangan input laporan, upload foto, kerja offline) — controller `*MobileController` dan payload nested (`reporter.*`, `locations.*`, dst.) dibuat khusus untuk ini.

---

## 2. Aktor & Role (`users` table)

Tidak ada tabel roles terpisah — hak akses ditentukan kombinasi beberapa kolom di `User`:

| Kolom | Arti | Dampak ke akses data |
|---|---|---|
| `upt_id` | UPT/unit tempat user bertugas. `1`/`2` biasanya level Pusat/nasional. | Jika `upt_id > 2`, banyak query di-filter otomatis `where upt_id = current_user.upt_id` (user hanya lihat data unitnya sendiri). Jika `<= 2`, user bisa melihat semua data (lintas UPT). |
| `user_level` | Level hierarki user (angka lebih kecil = lebih senior). | `UserController::getAll` hanya mengembalikan user dengan `user_level` **lebih besar** dari user yang login (yaitu hanya bisa mengelola bawahan). |
| `is_doctor` | Flag dokter hewan (drh). | Menentukan siapa yang boleh mengisi tahap **verifikasi dokter** pada laporan. Field `drh_specialist` menyimpan spesialisasi. |
| `heads_upt` | Flag kepala UPT. | Penerima notifikasi push untuk kasus penting (satwa dilindungi, penyakit prioritas). |
| `web_admin` | Flag admin web. | Membedakan user yang hanya boleh pakai web vs mobile. |
| `training_mode` | User sedang dalam mode latihan/simulasi. | Semua laporan yang dibuat ikut ditandai `training` sama dengan nilai ini — data latihan terpisah dari data produksi di semua listing. |
| `all_notification` | Ikut menerima semua notifikasi laporan baru di unitnya. | Dipakai filter penerima FCM. |
| `devices` | FCM token device (string, satu per user — bukan array meski field lain bernama jamak). | Dipakai kirim push notification. Diisi lewat `user-activities/track` atau `users/user/token`. Dikosongkan saat logout. |

**Kesimpulan untuk mobile app:** setelah login, cek `user.upt_id`, `user.upt_type`, `user.is_doctor`, `user.heads_upt`, `user.training_mode` untuk menentukan menu/form apa yang tampil (mis. hanya dokter hewan yang lihat form "Verifikasi Dokter").

---

## 3. Struktur Organisasi (UPT) & Lokasi

- **UPT** (`upts` table, model `App\Models\Upt`) = unit kerja. `type`: `PUSAT | KSDA | TN | LU | LK` (Lembaga Umum / Lembaga Khusus di bawah BKSDA lewat `unit_id` sebagai parent). Punya `upt_heads` (array pejabat), lokasi (province/district/subdistrict/village + lat/long), info konservasi (`conservation_type`, `insitu_conservation`, `exsitu_conservation`).
- **UptSpecies** = daftar satwa yang dikelola/ada di suatu UPT (`upt_id`, `species_id`, `population`).
- **Wilayah administratif Indonesia**: `Province` → `District` (`id_province`) → `SubDistrict` (`id_district`) → `Village` (`id_sub_district`). Dipakai untuk field lokasi laporan dan alamat UPT.

---

## 4. Master Data

| Entitas | Model | Keterangan |
|---|---|---|
| **Species** | `Species` | Master satwa/tumbuhan. `category` (Hewan/Tumbuhan via Option), `type`, `priority` (bool — spesies prioritas), `protected` (bool — dilindungi). Punya foto (`media`, via Spatie MediaLibrary). |
| **Disease** | `Disease` | Master penyakit. `priority` (bool — penyakit prioritas → memicu notifikasi ke Pusat saat diverifikasi), `zoonosis` (bool — bisa menular ke manusia), `symptom`/`symptom_details`, `treatment`/`treatment_details` (JSON), foto. |
| **Option** | `Option` | Tabel **lookup generik** serba-guna: `{name, slug, category, field, type, value(JSON array)}`. Endpoint `POST masters` mengembalikan **semua** Option sebagai map `name → value`. Dipakai untuk dropdown form (lihat §8). |

Semua entitas master pakai soft-delete via kolom `status` (`1`=aktif, `0`=nonaktif) — controller **tidak pernah** `DELETE` fisik, hanya `status = 0`.

---

## 5. Entitas Inti: Laporan Kasus (`GeneralReportSource` & tabel satelitnya)

Ini **jantung sistem**. Satu kasus laporan tersebar ke banyak tabel yang **berbagi primary key `id` yang sama** dengan `general_report_sources.id` (relasi 1-to-1 via `id`, bukan foreign key konvensional `xxx_id`). Artinya tiap sub-tabel punya `$incrementing = false` dan `id`-nya di-set manual = `id` induk.

```
GeneralReportSource (induk — id, report_code, report_date, status, training, user_id, upt_id, client_id, species_id, protected, dead, live, description)
 ├─ GeneralReportReporter        (siapa yang lapor: name, gender, occupation, phone, address, case_found, additional_reporters)
 ├─ GeneralReportLocation        (dimana: upt_id/type/name, province/district/subdistrict/village_id, lat/long, conservation_type, insitu/exsitu_conservation)
 ├─ GeneralReportSpecies         (satwa apa: category, protected, protected_species, species_name/latin_name/family, species_age, population)
 ├─ GeneralReportDiagnosis       (temuan awal petugas: report_date, dead/dead_sign, live/live_sign, chronological, sampling, follow_up, diagnosis, temporary_diagnosis_id → Disease)
 ├─ GeneralReportInvestigation   (opsional, jika ada investigasi lanjutan: investigation_date_from/to, inspection_method, evidence[], follow_up_carried_out, data_investigation[], + media)
 ├─ GeneralReportVerification    (verifikasi dokter hewan: verified_date, verified(bool), verification, temporary_disease_id → Disease, sampling, action, doctor_information, involved_doctors[], + media)
 ├─ GeneralReportLab             (hasil lab final: final_disease_id → Disease, final_diagnosis, follow_up)
 ├─ GeneralReportAcknowledgement (tanda tangan kepala UPT: upt_head_date/name/occupation/sign)
 └─ media (foto, via Spatie MediaLibrary, banyak per laporan)
```

Field penting di `GeneralReportSource`:
- `report_code` — auto-generate: `"LU" + tipe_UPT + 12-char_nama_UPT(tanpa_spasi) + (id_laporan_terakhir + 1)` (lihat `generateKode()`).
- `training` — disalin dari `user.training_mode` saat laporan dibuat; semua query listing selalu filter berdasarkan ini, laporan latihan **tidak akan muncul** di listing produksi dan sebaliknya.
- `client_id` — **ID yang dibuat di sisi mobile app (offline-first)**. Dipakai untuk mencocokkan laporan yang dibuat saat offline dengan record di server ketika sinkron (lihat §7).
- `status` — soft delete (`delete` endpoint hanya set `status = 0`).
- `protected` — jika `true`, memicu notifikasi "Laporan Kasus Satwa di Lindungi" ke Pusat/kepala UPT.

### 5.1 Siklus Hidup Laporan (Case Lifecycle)

```
1. PELAPORAN AWAL (field officer / komunitas)
   └─ create GeneralReportSource + Reporter + Location + Species + Diagnosis (+ foto)
   └─ trigger: sendNotificationReport() → push ke sesama petugas UPT sejenis
   └─ jika species.protected == true → sendNotifikasiSatwaDiLindungi() → push ke Pusat/kepala UPT

2. INVESTIGASI (opsional, tim investigasi turun ke lapangan)
   └─ isi GeneralReportInvestigation (tanggal, metode, bukti, hasil)

3. VERIFIKASI DOKTER HEWAN
   └─ dokter hewan (is_doctor) isi GeneralReportVerification
      (diagnosa sementara/temporary_disease_id, tindakan, permintaan sampling)
   └─ jika disease.priority == true → sendNotificationDiseasePriority() → push ke Pusat/kepala UPT

4. HASIL LAB
   └─ isi GeneralReportLab (final_disease_id, final_diagnosis, tindak lanjut)

5. PENGESAHAN KEPALA UPT
   └─ GeneralReportAcknowledgement (tanda tangan digital kepala UPT)
```

Setiap tahap **independen** — endpoint mobile mendukung pengisian bertahap (field bisa `null` di awal, diisi belakangan lewat `save` ulang dengan `id`/`client_id` yang sama). Sistem melakukan **diffing manual** (`array_diff` antara data lama & baru) sebelum update — jika tidak ada perubahan, update di-skip (optimisasi, bukan validasi bisnis).

### 5.2 Laporan dari Masyarakat (`CommunityReport`)

Warga bisa mengirim laporan awam (`name`, `report_date`, `description`) lewat `community-reports/save` **tanpa perlu login sebagai petugas** (endpoint ini berada di luar grup `auth:api`). Laporan ini **belum** menjadi kasus resmi. Seorang petugas kemudian bisa "menaikkan" laporan masyarakat menjadi `GeneralReportSource` resmi — terlihat dari `GeneralReportSourceController::GenerateReportCommunity()` yang menghubungkan `community_reports.id_laporan` ke laporan resmi yang baru dibuat, memakai `reporter_id`.

---

## 6. Autentikasi

- **Laravel Passport** (OAuth2 Bearer token, bukan Sanctum meskipun `sanctum` ada di config — cek `HasApiTokens` di model `User` dari package Passport).
- Login: `POST /auth/login` **atau** `POST /auth/login-app` (identik secara fungsional) dengan body `{ username, password }`.
  - Username bisa cocok ke kolom `username` **atau** `username1` — keduanya disimpan sebagai **MD5 hash dari email / nomor telepon** (`UserController::save()`: `username = md5(email)`, `username1 = md5(phone)`). Artinya user login pakai **email atau nomor HP**, backend yang men-hash untuk pencocokan.
  - Response sukses: `{ success, data: { user, access_token } }`. `access_token` dipakai sebagai `Authorization: Bearer <token>` untuk semua endpoint terproteksi.
  - Response gagal: HTTP 200 (bukan 4xx!) dengan `{ success:false, message:{ username, password } }`, atau HTTP 404 kalau user sama sekali tidak ditemukan.
- **Logout**: `POST /auth/logout` (butuh token) — revoke token Passport + set `user.devices = null` (hapus token push notif).
- **Ganti password saat login** (bukan reset via email): `POST /auth/forgot-password` (butuh token aktif!) — body `{ password }`, langsung set password baru + `reset_password = 0`. Field `reset_password` di user dipakai mobile app untuk **memaksa user ganti password** setelah akun baru dibuat oleh admin.
- Tidak ada refresh-token flow yang terlihat — asumsikan token long-lived atau re-login saat 401.

⚠️ **Inkonsistensi otorisasi yang perlu diwaspadai** (bukan bug untuk diperbaiki di FE, tapi perlu tahu): beberapa endpoint terdaftar **di luar** grup `auth:api` meski secara fungsi butuh data user (mis. `community-reports/getAll`, `community-reports/delete`, `generalReport/getAll`, `locationss`, `dashboards/get-*Web`, `dokter/getAll`). Endpoint-endpoint ini **bisa diakses tanpa token**. Jangan mengandalkan endpoint tanpa `s` ganda/tanpa awalan resmi ini untuk fitur yang butuh scoping user — pakai versi di dalam grup (`general-reports/getAll`, `community-reports/getOne` di dalam grup, dst).

---

## 7. Catatan Khusus Mobile (`*MobileController`)

Ada controller kembar untuk mobile yang formatnya **berbeda** dari versi web:

| Aspek | Versi Web (`GeneralReportSourceController`) | Versi Mobile (`GeneralReportSourceMobileController`) |
|---|---|---|
| Route save | `general-reports/save` | `general-reports/saveGeneralReportMobile` |
| Route getAll | `general-reports/getAll` (tanpa pagination) | `general-reports/getGeneralReportMobile` (**paginated**, 10/halaman) |
| Route delete | `general-reports/delete` (by `id`) | `general-reports/deleteGeneralReportMobile` (by **`client_id`**) |
| Bentuk payload `save` | Flat: `name`, `gender`, `province_id`, dst langsung di root | **Nested**: `reporter.name`, `locations.province_id`, `species.category`, `diagnoses.report_date`, `verification.*`, `lab.*`, `investigation.*` |
| Identifikasi record | `id` | `id` **atau `client_id`** (untuk dukung pembuatan offline) |
| Sub-tahap (investigation/verification/lab) | Endpoint terpisah (`saveVerify`, `saveLab`, `saveInvestigation`) | Bisa dikirim **sekaligus** dalam satu payload `save` (dicek `array_key_exists('investigation', $params) && != null`, dst) |

**Rekomendasi untuk mobile app:**
1. Gunakan endpoint `*Mobile` (`GeneralReportSourceMobileController`) untuk semua operasi laporan dari app.
2. Saat membuat laporan **offline**, generate `client_id` unik di device (mis. UUID) dan simpan itu sebagai identifier lokal. Saat sinkron ke server, kirim `client_id` yang sama — server akan `create` jika belum ada, atau `update` jika `client_id` sudah match record yang ada (mendukung retry-safe sync / idempotency).
3. Upload foto pakai **base64** langsung di field body (`file`, `verification.file_verification`, `investigation.file_investigation`) — bukan `multipart/form-data`. Untuk menghapus foto lama, kirim objek `{ id, deleted: true }` alih-alih string base64 di array yang sama.
4. Field `training` pada laporan ikut `user.training_mode` otomatis — app **tidak perlu** kirim field ini manual, tapi UI sebaiknya menampilkan indikator "Mode Latihan" bila `user.training_mode == true` supaya petugas sadar datanya tidak masuk hitungan resmi.

---

## 8. Endpoint Reference

### 8.1 Publik (tanpa token)

| Method | Endpoint | Controller@method | Fungsi |
|---|---|---|---|
| POST | `auth/login`, `auth/login-app` | `AuthController@login/loginApp` | Login |
| GET | `ping` | closure | Health check |
| POST | `dokter/getAll`, `dokter/getOne` | `UserController@getAllDokter/getOneDokter` | Daftar dokter hewan (untuk pilih dokter saat verifikasi) |
| POST | `community-reports/save` | `CommunityReportController@save` | Warga kirim laporan awam |
| POST | `sehatsatli/getReports` | `ApiGeneralReportController@getApi` | Proxy ke server training eksternal (jarang dipakai mobile) |

### 8.2 Terproteksi (`Authorization: Bearer <token>`)

**Auth & profil**
| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `auth/forgot-password` | Ganti password saat sudah login |
| POST | `update-user` | Update profil sendiri (`updateProfile`) |
| POST | `users/user/token` | Simpan/refresh FCM device token |
| POST | `users/uploadPhotoMobile` | Upload foto profil (base64) |

**Master data untuk form**
| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `masters` | Semua `Option` (dropdown generik) + `trained` flag user |
| POST | `locations` | Semua provinsi/kab-kota/kecamatan/desa (cached Redis) |
| POST | `location-user` | Lokasi yang di-scope ke UPT user login |
| POST | `species/getAll`, `species/getOne` | Master satwa |
| POST | `diseases/getAll`, `diseases/getOne` | Master penyakit |
| POST | `upts/getAll`, `upts/getOne` | Daftar UPT |

**Laporan kasus (mobile)**
| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `general-reports/getGeneralReportMobile` | List laporan (paginated), filter `training`, auto-scope ke UPT user |
| POST | `general-reports/getOne` | Detail 1 laporan (semua relasi + media) |
| POST | `general-reports/saveGeneralReportMobile` | Create/update laporan (nested payload, semua tahap) |
| POST | `general-reports/deleteGeneralReportMobile` | Soft-delete by `client_id` |
| POST | `general-reports/saveVerify` | Isi/ubah tahap verifikasi dokter (payload flat/web-style) |
| POST | `general-reports/saveLab` | Isi/ubah hasil lab |
| POST | `general-reports/saveInvestigation` | Isi/ubah investigasi |
| POST | `general-reports/uploadPhoto` | Upload foto tambahan |
| POST | `send/notification/satwa-protected` | Trigger manual notifikasi satwa dilindungi |
| POST | `send/notification/disease-priority` | Trigger manual notifikasi penyakit prioritas |

**Laporan masyarakat**
| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `community-reports/getOne` | Detail laporan warga (untuk ditindaklanjuti petugas) |

**Inbox & aktivitas**
| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `user-inbox/getAll`, `getOne` | Notifikasi in-app |
| POST | `user-activities/track` | Heartbeat aktivitas bulanan user + simpan device token |

**Dashboard** (kemungkinan besar untuk web, opsional untuk mobile — ringkasan statistik)
`dashboard/get-Suspek`, `get-NonSuspek`, `get-SuspekNotFound`, `get-lab`, `get-suspekPenyakit`, `get-nonPenyakit`, `get-petugas`, `get-upt`, `get-rincian`, `get-totalLaporan`, `get-location`, `user-active`

---

## 9. Nilai Enum / Referensi Penting (dari `Option` seeder)

Diakses via `POST masters` → `data.options.<name>`:

| `name` | Isi |
|---|---|
| `upt_type` | `PUSAT`, `KSDA`, `TN` (Taman Nasional), `LU` (Lembaga Umum), `LK` (Lembaga Khusus) |
| `sample_results` | Positif, Negatif, Sample tidak dapat diuji, Tidak Jelas, Lainnya |
| `case_status` | 1=Bukan Suspect Penyakit, 2=Suspect Penyakit |
| `species_category` | 1=Hewan, 2=Tumbuhan |
| `suspek_disease` | 0=Bukan Suspect Penyakit, 1=Suspect Penyakit — dipakai sebagai `temporary_diagnosis_id` di `GeneralReportDiagnosis` |
| `protected` | 0=Tidak Di Lindungi, 1=Di Lindungi |
| `verification_actions` | Daftar tindakan tahap verifikasi dokter |
| `investigation_team` | Tim Investigasi Internal KLHK / Lintas Sektor |
| `lab_actions`, `investigation_action`, `sample_lab`, `sample_templates` | Opsi teks untuk masing-masing form tahap |
| `age_groups`, `animal_conditions`, `animal_genders`, `clinical_signs`, `conservation_institutions`, `conservation_types`, `follow_ups`, `genders`, `location_types`, `sample_times`, `team_categories`, `user_groups`, `yesno` | Lookup umum lain, migrasi dari sistem lama (`ms_*`) |

Semua nilai ini **dinamis di database** — jangan hardcode di app kecuali sebagai fallback; selalu ambil dari `masters` saat startup/refresh.

---

## 10. Notifikasi Push (FCM)

Semua dikirim lewat legacy FCM HTTP API (`https://fcm.googleapis.com/fcm/send`, header `Authorization: key=<APP_SERVER_API_KEY>`), target `devices` token per user. Tiga pemicu:

1. **Laporan baru dibuat** (`sendNotificationReport`, dipanggil otomatis tiap create laporan mobile) → semua user `all_notification=1` di UPT-type yang sama, ditambah `heads_upt` & `upt_id=1`.
2. **Satwa dilindungi terdeteksi** (`sendNotifikasiSatwaDiLindungi`) → user di UPT-type sama / `PUSAT` / `heads_upt=1`.
3. **Penyakit prioritas terverifikasi** (`sendNotificationDiseasePriority`, dicek dari `disease.priority`) → sama seperti di atas.

Mobile app perlu: (a) request FCM token saat pertama buka, (b) kirim ke `users/user/token` atau `user-activities/track`, (c) handle notifikasi masuk untuk deep-link ke detail laporan terkait.

---

## 11. Media / Upload Foto

Pakai **Spatie MediaLibrary**. Semua entitas dengan foto (`Species`, `Disease`, `UPT`, `User`, `GeneralReportSource`, `GeneralReportVerification`, `GeneralReportInvestigation`) punya relasi `media` dan endpoint `uploadPhoto` masing-masing. Untuk laporan kasus, upload foto terjadi **inline** saat `save` (base64 di field `file` dsb, lihat §7) — bukan endpoint terpisah wajib dipanggil.

---

## 12. Ringkasan Prioritas Implementasi Mobile

1. **Auth**: login (email/HP + password) → simpan `access_token` + object `user` lengkap.
2. **Bootstrap data**: panggil `masters` dan `locations` (atau `location-user` bila ingin scoped) sekali di awal, cache lokal.
3. **List & buat laporan**: `general-reports/getGeneralReportMobile` (list+pagination), `general-reports/saveGeneralReportMobile` (create/update dengan dukungan offline via `client_id`).
4. **Form bertingkat**: dukung pengisian tahap (pelapor → lokasi → spesies → diagnosis awal) sebagai satu form, dan tahap lanjutan (investigasi/verifikasi dokter/lab) sebagai form terpisah yang hanya muncul sesuai role (`is_doctor` dsb.) dan status laporan saat ini.
5. **Upload foto**: base64 encode di client sebelum kirim, dukung penghapusan foto lewat objek `{id, deleted:true}`.
6. **Push notification**: daftarkan token FCM, tampilkan indikator mode latihan (`training_mode`).
7. **Laporan masyarakat** (opsional, jika app juga dipakai publik): endpoint terbuka `community-reports/save`.
