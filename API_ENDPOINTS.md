# API Endpoints — Sehat Satli (untuk Flutter Mobile App)

Base URL: `{APP_URL}/api/` (lihat `routes/api.php`). Semua request `POST` kecuali `GET /ping`.
Auth: **Laravel Passport** (Bearer token). Kirim header:
```
Authorization: Bearer {access_token}
Accept: application/json
```

> ⚠️ Beberapa endpoint didefinisikan **dua kali** dengan path berbeda (satu di dalam group `auth:api`, satu di luar/publik) — kemungkinan sisa migrasi web→mobile. Endpoint yang direkomendasikan untuk dipakai Flutter app ditandai **✅ Pakai ini**, duplikat lama ditandai **⚠️ Legacy**.

---

## 1. Auth

| Method | Path | Auth | Deskripsi |
|---|---|---|---|
| POST | `/auth/login-app` | Publik | ✅ **Pakai ini** untuk login mobile. Body: `{ username, password }`. `username` = email **atau** nomor HP (dicocokkan ke kolom `username`/`username1` yang berisi MD5 di server — kirim plain text, jangan di-hash di client). Response: `{ success, data: { user, access_token } }`. |
| POST | `/auth/login` | Publik | ⚠️ Legacy, sama persis dengan `login-app`, dipakai web admin. |
| POST | `/auth/logout` | Bearer token | Revoke access token yang sedang dipakai + hapus FCM `devices` token milik user. Panggil saat user logout dari app. |
| POST | `/auth/forgot-password` | Bearer token | Nama menyesatkan — ini **ganti password saat sedang login** (bukan alur lupa password via OTP/email). Body: `{ password }`. Biasanya dipanggil saat `user.reset_password == true` (login pertama pakai password default dari admin). |

---

## 2. Master Data & Lookup (wajib di-load saat app start / cache lokal)

| Method | Path | Auth | Deskripsi |
|---|---|---|---|
| POST | `/masters` | Bearer token | Dropdown/lookup form (`upt_type`, `sample_results`, `case_status`, `species_category`, `suspek_disease`, `protected`, dst) dari tabel `options`, format `{ name: value }`. Juga return `trained` (mode training user). |
| POST | `/locations` | Bearer token | Seluruh data wilayah Indonesia: `provinces`, `districts` (per provinsi), `subdistricts` (per kabupaten), `villages` (per kecamatan). Di-cache di Redis server-side — payload besar, load sekali & simpan lokal (SQLite/Hive) di app. |
| POST | `/location-user` | Bearer token | Sama seperti `/locations` tapi hanya wilayah di provinsi UPT tempat user bertugas. Lebih ringan, cocok untuk default form location picker. |
| POST | `/diseases/getAll` | Bearer token | List penyakit aktif (untuk dropdown diagnosis). |
| POST | `/diseases/getOne` | Bearer token | Detail 1 penyakit. Body: `{ id }`. |
| POST | `/species/getAll` | Bearer token | List spesies aktif (untuk dropdown spesies). |
| POST | `/species/getOne` | Bearer token | Detail 1 spesies. Body: `{ id }`. |
| POST | `/upts/getAll` | Bearer token | List UPT (unit kerja), auto di-scope ke UPT user login jika bukan admin pusat. |
| POST | `/upts/getOne` | Bearer token | Detail 1 UPT. Body: `{ id }`. |
| POST | `/dokter/getAll` | Publik | List dokter hewan (`is_doctor=1`) — untuk pilih dokter di form verifikasi. |
| POST | `/dokter/getOne` | Publik | ⚠️ Bug: filter `is_doctor = 0` (kebalikan dari `getAll`) — kemungkinan selalu kosong untuk id dokter yang valid. Cek ulang sebelum dipakai. |

---

## 3. Laporan Kasus (Case Report) — modul utama mobile app

Gunakan controller **mobile** (`general-reports/*GeneralReportMobile`), bukan versi web (`general-reports/getAll`/`save` biasa) karena versi mobile mendukung **offline-first sync** via `client_id`.

| Method | Path | Auth | Deskripsi |
|---|---|---|---|
| POST | `/general-reports/getGeneralReportMobile` | Bearer token | ✅ List laporan kasus, **paginated** (10/halaman, param standar Laravel `?page=`). Body wajib: `{ id: 1|0 }` — filter kolom `training` (1 = data latihan, 0 = data produksi). Auto scope ke UPT user. |
| POST | `/general-reports/getOne` | Bearer token | Detail 1 laporan (semua relasi: reporter, location, species, diagnoses, verification, lab, investigation, media). Body: `{ id }`. |
| POST | `/general-reports/saveGeneralReportMobile` | Bearer token | ✅ **Create/update laporan (nested, offline-first)**. Lihat skema payload di bawah. |
| POST | `/general-reports/deleteGeneralReportMobile` | Bearer token | ✅ Soft delete (`status=0`). Body: `{ client_id }` (bukan `id` server — sesuai konsep offline-first). |
| POST | `/generalReport/getAll` | Publik | ⚠️ Legacy/duplikat `getGeneralReportMobile`, tapi **di luar** group auth padahal controller butuh user login (`auth()->user()->id`) — kemungkinan bug, jangan dipakai. |

### Payload `saveGeneralReportMobile` (nested per tahap)

Kirim `client_id` unik yang dibuat di device (mis. UUID) sebagai identitas laporan untuk sinkronisasi:
- Jika `client_id` **belum ada** di server → dibuat laporan baru (`report_code` & `training` di-generate otomatis dari UPT & mode training user).
- Jika `client_id` **sudah ada** → data di-update (aman untuk retry saat sinkronisasi ulang koneksi terputus).

```jsonc
{
  "client_id": "uuid-dari-device",   // wajib, identitas offline-first
  "training": false,                  // opsional, biasanya auto dari training_mode user
  "report_date": "2026-08-03",
  "description": "string",

  "reporter": {
    "name": "string", "gender": "string", "occupation": "string",
    "phone": "string", "address": "string", "case_found": true
  },

  "locations": {
    "upt_id": 1, "upt_type": "string", "location_name": "string",
    "conservation_type": "string",
    "insitu_conservation": ["..."], "insitu_other": "string",
    "exsitu_conservation": ["..."], "exsitu_other": "string",
    "province_id": 1, "district_id": 1, "subdistrict_id": 1, "village_id": 1,
    "location_description": "string", "latitude": -6.2, "longitude": 106.8
  },

  "species": {
    "category": "string", "protected": true, "protected_species": 1,
    "species_name": "string", "species_latin_name": "string",
    "species_family": "string", "species_age": "string", "population": 1
  },

  "diagnoses": {
    "report_date": "2026-08-03",
    "dead": 0, "dead_sign": ["..."], "dead_sign_other": "string",
    "live": 1, "live_sign": ["..."], "live_sign_other": "string",
    "chronological": "string", "sampling": "string", "follow_up": "string",
    "diagnosis": "string", "temporary_diagnosis_id": 1
  },

  // opsional — tahap lanjutan (verifikasi dokter), kirim jika sudah terisi:
  "verification": {
    "verified_date": "2026-08-03", "verified": true, "verification": "string",
    "temporary_disease_id": 1, "sampling": "string", "action": "string",
    "doctor_information": "string", "involved_doctors": [1, 2],
    "file_verification": ["<base64>", { "id": 5, "deleted": true }]
  },

  // opsional — hasil lab
  "lab": {
    "final_disease_id": 1, "final_diagnosis": "string", "follow_up": "string"
  },

  // opsional — investigasi lapangan
  "investigation": {
    "investigation_date_from": "2026-08-03", "investigation_date_to": "2026-08-05",
    "inspection_method": "string", "evidence": ["..."],
    "follow_up_carried_out": "string", "additional_information": "string",
    "data_investigation": ["..."],
    "file_investigation": ["<base64>", { "id": 5, "deleted": true }]
  },

  // foto utama laporan — array of base64 string; kirim object {"id":5,"deleted":true} untuk hapus foto lama
  "file": ["<base64-string>"]
}
```

**Catatan penting untuk implementasi Flutter:**
- Semua foto dikirim sebagai **base64 string** dalam JSON body (bukan multipart), field: `file` (foto utama), `verification.file_verification`, `investigation.file_investigation`.
- Untuk menghapus foto yang sudah ter-upload, kirim item array berupa object `{ "id": <media_id>, "deleted": true }`, bukan string base64.
- Server membuat push notification (FCM) otomatis ke petugas terkait setiap kali laporan **baru** dibuat (bukan saat update).
- Karena mendukung retry, aman dipanggil ulang dari local queue/sync worker jika request sebelumnya gagal karena koneksi.

---

## 4. Laporan Masyarakat (Community Report) — laporan awam sebelum jadi kasus resmi

| Method | Path | Auth | Deskripsi |
|---|---|---|---|
| POST | `/community-reports/save` | **Publik** | Buat/ubah laporan warga. Body: `{ id?, name, report_date, description }`. Tidak perlu login — cocok untuk fitur "lapor cepat" tanpa akun. |
| POST | `/community-reports/getAll` | **Publik** | List semua laporan warga + relasi `user`, `laporan.upt`. |
| POST | `/community-reports/getOne` | Bearer token | Detail 1 laporan warga. Body: `{ id }`. |
| POST | `/community-reports/delete` | **Publik** | ⚠️ Hard delete (bukan soft delete). Body: `{ id }`. Pertimbangkan proteksi tambahan di app (misal hanya pelapor/admin). |

---

## 5. User & Profil

| Method | Path | Auth | Deskripsi |
|---|---|---|---|
| POST | `/users/getAll` | Bearer token | List user dengan `user_level` lebih junior dari user login, auto scope UPT. |
| POST | `/users/getOne` | Bearer token | Detail 1 user. Body: `{ id }`. |
| POST | `/update-user` | Bearer token | Update profil sendiri. ⚠️ **Bug backend**: kode cek `id` di params tapi `id` tidak pernah di-passing ke `$request->only()` → cabang update **tidak pernah jalan**, selalu return `{success:true, data:null}` tanpa menyimpan perubahan. Jangan andalkan endpoint ini sampai backend diperbaiki. |
| POST | `/users/uploadPhotoMobile` | Bearer token | ✅ Upload foto profil versi mobile. Body: `{ id, file: "<base64>" }` (bukan multipart). Menggantikan foto lama. |
| POST | `/users/uploadPhoto` | Bearer token | ⚠️ Versi web, `multipart/form-data` field `file` — tidak dipakai mobile. |
| POST | `/users/user/token` | Bearer token | ✅ Simpan/update FCM device token. Body: `{ token }`. Panggil setiap app mendapat/refresh token FCM. |
| POST | `/users/sendNotification` | Bearer token | Kirim push notification ke 1 user tertentu. Body: `{ user_id, title, description }`. |
| POST | `/users/sendNotifications` | **Publik** | ⚠️ Legacy/duplikat (`sendNotification` vs `sendNotifications`), tapi tanpa proteksi auth — pertimbangkan tidak dipakai dari client publik. |

---

## 6. Inbox (notifikasi in-app)

| Method | Path | Auth | Deskripsi |
|---|---|---|---|
| POST | `/user-inbox/getAll` | Bearer token | List semua pesan inbox aktif. |
| POST | `/user-inbox/getOne` | Bearer token | Detail 1 pesan. Body: `{ id }`. |
| POST | `/user-inbox/save` | Bearer token | Buat/ubah pesan. Body: `{ id?, user_id, received_date, read_date, subject, message, read }`. |
| POST | `/user-inbox/delete` | Bearer token | Soft delete. Body: `{ id }`. |

---

## 7. Dashboard (opsional, jika app punya menu ringkasan/statistik)

| Method | Path | Auth | Deskripsi |
|---|---|---|---|
| POST | `/dashboard/get-location` | Bearer token | Data lokasi kasus untuk peta. |
| POST | `/dashboard/get-totalLaporan` | Bearer token | Total laporan per bulan. |
| POST | `/dashboard/get-Suspek` | Bearer token | Data kasus suspek. |
| POST | `/dashboard/get-NonSuspek` | Bearer token | Data kasus non-suspek. |
| POST | `/dashboard/get-SuspekNotFound` | Bearer token | Data suspek tidak ditemukan. |
| POST | `/dashboard/get-lab` | Bearer token | Data hasil lab. |
| POST | `/dashboard/get-suspekPenyakit` | Bearer token | Data suspek per penyakit. |
| POST | `/dashboard/get-nonPenyakit` | Bearer token | Data non-suspek per penyakit. |
| POST | `/dashboard/get-petugas` | Bearer token | Data per petugas. |
| POST | `/dashboard/get-upt` | Bearer token | Data per UPT. |
| POST | `/dashboard/get-rincian` | Bearer token | Rincian laporan. |
| POST | `/user-active` | Bearer token | Data user aktif. |
| POST | `/user-activities/track` | Bearer token | Heartbeat aktivitas bulanan user (panggil berkala, mis. saat app dibuka/tiap sesi). |

> Endpoint `dashboards/*` (dengan `s`) di luar group adalah versi web publik terpisah — jangan dipakai dari mobile app.

---

## 8. Misc

| Method | Path | Auth | Deskripsi |
|---|---|---|---|
| GET | `/ping` | Publik | Health check, selalu return `true`. Bisa dipakai app untuk cek konektivitas ke server sebelum sync. |

---

## Ringkasan Response Envelope

Hampir semua endpoint mengembalikan bentuk konsisten:
```jsonc
{ "success": true, "data": { /* ... */ } }
// atau
{ "success": false, "message": "..." }
```
Tapi **tidak 100% konsisten** — beberapa endpoint (`getOne` di beberapa controller, error handler tertentu) mengembalikan array PHP langsung tanpa `response()->json()` (masih ter-serialize jadi JSON oleh Laravel, HTTP status tetap 200 bahkan saat gagal). Jangan asumsikan HTTP status code mencerminkan sukses/gagal — selalu cek field `success` di body.
