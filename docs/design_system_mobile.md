# SehatSatli — Design System & Arsitektur Mobile App (Flutter / FlutterFlow)

> Lanjutan dari [`design-system.md`](./design-system.md) (proses bisnis & domain API) dan
> [`palette_sehat_satli.md`](./palette_sehat_satli.md) (token warna dasar dari logo).
> Dokumen ini adalah acuan teknis untuk membangun **mobile app Flutter**, dibuat agar token
> & strukturnya bisa langsung diterapkan di **FlutterFlow** (Theme Settings, Custom Data
> Type, API Group, Custom Action), dengan pendekatan **Offline-First** sebagai prioritas
> utama (pengisian laporan tanpa koneksi internet + sinkronisasi otomatis).

---

## 1. Prinsip Desain & Batasan Teknis

1. **Offline-first, bukan offline-tolerant.** Petugas lapangan sering berada di kawasan
   konservasi tanpa sinyal — form laporan **harus 100% bisa diisi & disimpan tanpa internet**,
   termasuk foto. Sinkronisasi adalah proses *background*, bukan syarat untuk lanjut kerja.
2. **`client_id` sebagai sumber kebenaran lokal.** Backend (`GeneralReportSourceMobileController`)
   sudah dirancang idempotent lewat `client_id` (§5, §7 `design-system.md`) — app tinggal
   menghasilkan UUID di device dan **selalu** mengirim `client_id` yang sama di setiap
   `save` (create maupun update), termasuk saat retry sync.
3. **FlutterFlow-compatible.** Semua keputusan (state management, storage, custom code)
   dipilih agar bisa dibangun di FlutterFlow: gunakan **App State** untuk data sesi ringan,
   **Custom Action (Dart)** untuk logika DB lokal/sync yang tidak didukung visual builder,
   dan **API Group** bawaan FlutterFlow untuk semua panggilan REST.
4. **Satu sumber warna.** Semua token warna di §2 mengikuti nama slot resmi FlutterFlow
   Theme Settings agar tinggal disalin ke panel *Theme Settings* proyek.

---

## 2. Design Tokens

### 2.1 Warna — mapping ke slot Theme Settings FlutterFlow

Berbasis [`palette_sehat_satli.md`](./palette_sehat_satli.md). Isi persis ke kolom
FlutterFlow **Theme Settings → Colors**:

| Slot FlutterFlow | Hex | Sumber / Fungsi |
|---|---|---|
| `primary` | `#17B273` | Hijau logo — tombol utama, AppBar, active state, ikon terpilih |
| `secondary` | `#E67E22` | Oranye logo — aksen, badge "Prioritas/Dilindungi", FAB tambah laporan |
| `tertiary` | `#2980B9` | Biru — info, link, elemen sekunder pada chart dashboard |
| `alternate` | `#E5E7EB` | Border/divider netral |
| `primaryText` | `#1F2937` | Teks judul & isi utama |
| `secondaryText` | `#555555` | Caption/subtitle (sesuai token asli logo) |
| `primaryBackground` | `#FFFFFF` | Latar dasar layar |
| `secondaryBackground` | `#F8F9FA` | Latar kartu/section (`$light`) |
| `accent1` | `#E7F7F1` | `primary-light` — background badge status "Tervalidasi/Aman" |
| `accent2` | `#FDEDDF` | `secondary-light` — background badge "Prioritas/Menunggu" |
| `accent3` | `#EBF5FB` | `info-light` — background badge info/draft |
| `accent4` | `#FDEDEC` | `danger-light` — background badge error/ditolak |
| `success` | `#17B273` | Status: laporan tersinkron, verifikasi selesai |
| `warning` | `#E67E22` | Status: pending sync, menunggu verifikasi, mode latihan |
| `error` | `#C0392B` | Status: gagal sync, validasi gagal, offline critical |
| `info` | `#2980B9` | Status: draft lokal, info umum |

Warna khusus non-slot (dipakai lewat *Custom Color* di widget tertentu):

| Nama | Hex | Pemakaian |
|---|---|---|
| `dark` | `#343A40` | Teks pada background gelap / overlay peta |
| `offlineBannerBg` | `#343A40` @ 92% opacity | Banner "Anda sedang offline" |
| `trainingModeBg` | `#FDEDDF` | Banner "Mode Latihan Aktif" (§9.4) |

### 2.2 Tipografi

| Style | Font | Size / Weight | Pemakaian |
|---|---|---|---|
| `displaySmall` | Poppins SemiBold | 22 / 600 | Judul halaman (AppBar besar, header dashboard) |
| `titleLarge` | Poppins Medium | 18 / 500 | Judul kartu, judul section form |
| `bodyLarge` | Inter Regular | 15 / 400 | Isi teks utama, label form |
| `bodyMedium` | Inter Regular | 13 / 400 | Deskripsi, isi tabel |
| `bodySmall` / caption | Inter Regular | 11 / 400 | Timestamp, metadata, helper text |
| `labelLarge` | Inter Medium | 13 / 500 | Teks tombol |

Pasangan font Poppins (heading) + Inter (body) tersedia langsung di Google Fonts picker
FlutterFlow — set di **Theme Settings → Typography**.

### 2.3 Spacing, Radius, Elevation

| Token | Nilai | Catatan |
|---|---|---|
| `space.xs / s / m / l / xl` | 4 / 8 / 16 / 24 / 32 | Skala 4pt, dipakai `Padding`/`Gap` |
| `radius.card` | 12 | Kartu laporan, kartu master data |
| `radius.button` | 10 | Tombol primary/secondary |
| `radius.input` | 8 | TextField, dropdown |
| `radius.chip` | 999 (pill) | Status badge |
| `elevation.card` | 1 (shadow tipis, blur 8, opacity 6%) | Hindari elevation tinggi — desain flat mengikuti gaya web admin |

### 2.4 Komponen Kunci

- **Status Chip / Badge** (dipakai di kartu laporan & indikator sync — lihat §7.7):
  bentuk pill, `radius.chip`, warna latar dari `accent1-4`, teks dari `success/warning/error/info`.
- **Tombol Primary**: latar `primary`, teks putih, `radius.button`.
- **Tombol Secondary/Outline**: border `primary`, teks `primary`, latar transparan — dipakai
  untuk aksi sekunder (mis. "Simpan sebagai Draft").
- **Form Stepper** (untuk form laporan multi-tahap §4.3): horizontal step indicator di atas
  form, warna `primary` untuk step aktif/selesai, `alternate` untuk step belum dikerjakan.
- **Bottom Navigation** (4 item): Beranda, Laporan, (FAB tengah: Buat Laporan), Notifikasi, Profil.
- **Offline Banner**: sticky di bawah AppBar, muncul otomatis saat `isOnline == false`
  (App State, lihat §7.3), latar `offlineBannerBg`, ikon cloud-off, teks "Mode Offline — data akan disinkronkan otomatis".
- **Training Mode Banner**: muncul jika `user.training_mode == true`, latar `trainingModeBg`,
  teks "Mode Latihan Aktif — data tidak dihitung resmi".

---

## 3. Tech Stack Mobile

| Layer | Pilihan | Alasan |
|---|---|---|
| Framework | Flutter 3.x via FlutterFlow export | Sesuai permintaan, bisa lanjut dikembangkan di IDE native |
| State (sesi/ephemeral) | FlutterFlow **App State** | Token, user login, status konektivitas, filter aktif |
| State (data relasional besar) | **Local DB kustom (sqflite/drift)** via Custom Action | App State FlutterFlow tidak cocok untuk data laporan + antrean sync yang relasional & besar |
| HTTP | FlutterFlow **API Group** (berbasis `http`/`dio` internal) | Semua endpoint di §6 didaftarkan sebagai 1 API Group `SehatSatliAPI` |
| Konektivitas | `connectivity_plus` (via Custom Action) | Deteksi online/offline realtime |
| Push Notification | `firebase_messaging` (FCM) | Cocok dgn backend `kreait/laravel-firebase` |
| Penyimpanan token | `flutter_secure_storage` (via Custom Action) | Token Passport Bearer tidak boleh di plain SharedPreferences |
| ID unik | `uuid` package | Generate `client_id` |
| Foto | `image_picker` + `path_provider` | Ambil foto, simpan sementara di filesystem sebelum di-encode base64 saat sync |

---

## 4. Information Architecture (Struktur Halaman)

### 4.1 Alur Non-Auth

- **Splash** → cek token tersimpan (secure storage) → cek `ping` (opsional, jangan blocking jika gagal) → ke Home atau Login.
- **Login** — form email/HP + password → `auth/login-app`. Tangani kuirk backend: HTTP 200
  dengan `success:false` tetap **dianggap error** (lihat catatan §6.4).
- **Lupa Password (saat sudah login)** — hanya dari menu Profil, bukan flow lupa password klasik (`auth/forgot-password` butuh token aktif).

### 4.2 Alur Utama (Bottom Nav)

| Tab | Halaman | Sumber Data | Role Gate |
|---|---|---|---|
| Beranda | Ringkasan (jumlah laporan bulan ini, status sync, shortcut buat laporan) | Lokal (cache) + `dashboard/get-*` opsional | Semua |
| Laporan | List laporan (tab: Semua / Draft Lokal / Menunggu Sync / Gagal Sync) | Lokal DB (draft) digabung hasil `general-reports/getGeneralReportMobile` | Semua, auto-scope UPT |
| **[FAB] Buat Laporan** | Form multi-step (lihat §4.3) | Ditulis ke Local DB dulu | Semua petugas |
| Notifikasi | Inbox in-app | `user-inbox/getAll` | Semua |
| Profil | Data diri, toggle mode latihan (read-only, info saja), logout, tombol "Sync Sekarang" manual | `update-user`, App State | Semua |

Halaman tambahan (bukan di bottom nav, diakses dari list/detail):
- **Detail Laporan** — semua tahap (reporter, lokasi, spesies, diagnosis, investigasi, verifikasi, lab, pengesahan) ditampilkan sebagai accordion/tab, hanya field yang terisi yang tampil.
- **Form Verifikasi Dokter** — hanya muncul jika `user.is_doctor == true`.
- **Form Pengesahan Kepala UPT** — hanya muncul jika `user.heads_upt == true`.
- **Laporan Masyarakat** (opsional, jika app dipakai publik juga) — form ringan tanpa login (`community-reports/save`), terpisah dari flow utama.
- **Referensi Master** (read-only) — daftar Spesies & Penyakit dengan foto/deskripsi, untuk bantu petugas saat isi form (dari cache `species/getAll`, `diseases/getAll`).

### 4.3 Form Laporan Multi-Step (inti aplikasi)

Stepper linear, tiap step **disimpan lokal segera setelah "Lanjut"** ditekan (bukan menunggu submit akhir):

```
1. Pelapor        → reporter.*
2. Lokasi         → locations.*  (ambil GPS otomatis via geolocator, fallback input manual)
3. Spesies        → species.*    (dropdown dari cache Species, atau input manual jika tidak ketemu)
4. Diagnosis Awal → diagnoses.*  (+ ambil foto, disimpan sbg path lokal)
5. [opsional, muncul belakangan sbg edit]:
   - Investigasi   → investigation.*
   - Verifikasi Dokter → verification.*  (khusus is_doctor)
   - Hasil Lab     → lab.*
   - Pengesahan    → acknowledgement.* (khusus heads_upt)
```

Setiap "Lanjut"/"Simpan" pada step manapun memicu **`upsertLocalDraft()`** (Custom Action,
§7.2) — bukan langsung call API. Tombol eksplisit **"Simpan & Selesai"** di step terakhir
menandai draft `readyToSync = true` lalu memicu percobaan sync langsung jika online.

---

## 5. Data Model (FlutterFlow Custom Data Type)

Definisikan sebagai **Custom Data Type** di FlutterFlow agar response API bisa langsung
di-parse otomatis. Struktur mengikuti bentuk nested payload backend (§5 & §7 `design-system.md`).

```
GeneralReportSource
├─ id: int?              // null selama masih draft lokal murni
├─ clientId: String      // UUID, wajib, dibuat di device
├─ reportCode: String?   // diisi server setelah sync pertama
├─ reportDate: DateTime
├─ speciesId: int?
├─ protected: bool
├─ dead: int?
├─ live: int?
├─ description: String?
├─ trainingMode: bool    // disalin dari user saat draft dibuat, hanya utk tampilan lokal
├─ syncStatus: enum { draft, pendingSync, syncing, synced, failed }
├─ reporter: ReporterData?
├─ location: LocationData?
├─ species: SpeciesData?
├─ diagnosis: DiagnosisData?
├─ investigation: InvestigationData?
├─ verification: VerificationData?
├─ lab: LabData?
├─ acknowledgement: AcknowledgementData?
└─ photos: List<LocalPhoto>   // { localPath, remoteId?, uploaded: bool, deleted: bool }
```

Sub-tipe (`ReporterData`, `LocationData`, dst.) field-nya **sama persis** dengan payload
`reporter.*`, `locations.*`, dll. yang divalidasi di
`GeneralReportSourceMobileController::save()` — jangan menambah/mengganti nama field agar
mapping ke JSON body tetap 1:1 tanpa transformasi.

Data referensi (read-only, hasil cache):
- `Option` → `{ name, category, field, type, value: List<dynamic> }`
- `Species`, `Disease`, `Upt`, `Province/District/SubDistrict/Village` — sesuai kolom di §4 `design-system.md`.

---

## 6. Integrasi API (FlutterFlow API Group: `SehatSatliAPI`)

### 6.1 Base config

- **Base URL**: dari `.env` sesuai environment (staging/production).
- **Auth Header**: `Authorization: Bearer {{accessToken}}` — set sebagai *API Group variable*
  yang dibaca dari App State `accessToken`, dipasang di semua API Call kecuali grup 6.2.
- **Content-Type**: `application/json`.

### 6.2 Endpoint publik (tanpa token)

`auth/login-app`, `dokter/getAll`, `dokter/getOne`, `community-reports/save`.

### 6.3 Endpoint utama untuk mobile (butuh token)

| Fungsi | Endpoint | Dipakai di |
|---|---|---|
| Bootstrap dropdown | `masters` | App start / pull-to-refresh cache |
| Bootstrap lokasi | `locations` atau `location-user` | App start / cache |
| Master spesies/penyakit/UPT | `species/getAll`, `diseases/getAll`, `upts/getAll` | Cache + halaman referensi |
| List laporan | `general-reports/getGeneralReportMobile` | Tab "Laporan" (paginated) |
| Detail laporan | `general-reports/getOne` | Halaman Detail |
| **Simpan laporan (create/update)** | `general-reports/saveGeneralReportMobile` | Sync engine (§7) |
| **Hapus laporan** | `general-reports/deleteGeneralReportMobile` (by `client_id`) | List → swipe delete |
| Verifikasi/Lab/Investigasi terpisah | `general-reports/saveVerify`, `saveLab`, `saveInvestigation` | Fallback jika tidak digabung di payload utama |
| Token FCM | `users/user/token` | Setelah login & saat token refresh |
| Profil | `update-user`, `users/uploadPhotoMobile` | Halaman Profil |
| Notifikasi in-app | `user-inbox/getAll`, `getOne` | Tab Notifikasi |
| Heartbeat aktivitas | `user-activities/track` | Dipanggil berkala (mis. tiap buka app) |

> **Gunakan hanya endpoint di dalam grup `auth:api`** (yang terdaftar di `$apiRoutes`).
> Jangan pakai varian di luar grup (`generalReport/getAll`, `locationss`, dst.) — itu
> endpoint legacy tanpa proteksi token, tidak di-scope ke user (lihat peringatan §6 `design-system.md`).

### 6.4 Penanganan respons tidak standar

- **Login gagal**: server balas **HTTP 200** dengan `{ success:false, message:{...} }` — di
  FlutterFlow API Call, jangan andalkan *status code* untuk deteksi error; selalu cek field
  `success` di body JSON hasil parse. Buat 1 Custom Action helper `isApiSuccess(response)`
  dipakai konsisten di semua pemanggilan.
- **Upload foto**: base64 string langsung di field body (`file`, `verification.file_verification`,
  `investigation.file_investigation`), **bukan** multipart. Hapus foto lama = kirim
  `{ id, deleted: true }` di posisi array yang sama.

---

## 7. Arsitektur Offline-First

Ini bagian inti dari dokumen ini. Tujuan: petugas bisa membuka app, mengisi seluruh form
laporan (termasuk foto & GPS), menyimpan, menutup app — semua tanpa internet sama sekali —
lalu begitu perangkat kembali online, semua data terkirim **otomatis tanpa aksi manual**.

### 7.1 Lapisan Penyimpanan Lokal

Karena data relasional (laporan + 8 sub-entitas + antrean sync + cache master) terlalu
kompleks untuk **App State** FlutterFlow, gunakan **SQLite lokal** (`sqflite` atau `drift`)
yang diakses lewat **Custom Action**. Skema:

```sql
-- Menyimpan seluruh draft laporan sbg JSON (struktur GeneralReportSource §5)
CREATE TABLE local_reports (
  client_id       TEXT PRIMARY KEY,       -- UUID, dibuat di device
  server_id       INTEGER,                -- diisi setelah sync sukses pertama
  report_code     TEXT,                   -- diisi setelah sync sukses pertama
  payload_json    TEXT NOT NULL,          -- seluruh nested object (reporter, locations, dst)
  sync_status     TEXT NOT NULL,          -- draft | pending | syncing | synced | failed
  last_error      TEXT,
  retry_count     INTEGER DEFAULT 0,
  created_at      TEXT NOT NULL,
  updated_at      TEXT NOT NULL
);

-- Antrean operasi terpisah per aksi (save laporan, saveVerify, delete, dsb)
-- supaya urutan & retry per-aksi bisa dikontrol independen dari isi draft.
CREATE TABLE sync_queue (
  id              INTEGER PRIMARY KEY AUTOINCREMENT,
  client_id       TEXT NOT NULL,          -- FK ke local_reports.client_id
  endpoint        TEXT NOT NULL,          -- mis. 'saveGeneralReportMobile'
  method          TEXT NOT NULL DEFAULT 'POST',
  payload_json    TEXT NOT NULL,          -- snapshot payload saat dijadwalkan
  status          TEXT NOT NULL DEFAULT 'pending', -- pending | in_progress | done | failed
  retry_count     INTEGER DEFAULT 0,
  last_error      TEXT,
  created_at      TEXT NOT NULL
);

-- Foto tersimpan lokal, di-encode base64 hanya saat akan dikirim (hemat memori)
CREATE TABLE local_photos (
  id              INTEGER PRIMARY KEY AUTOINCREMENT,
  client_id       TEXT NOT NULL,          -- laporan pemilik foto
  field_target    TEXT NOT NULL,          -- 'file' | 'verification.file_verification' | 'investigation.file_investigation'
  local_path      TEXT NOT NULL,          -- path filesystem device
  remote_media_id INTEGER,                -- diisi setelah upload sukses
  is_deleted      INTEGER DEFAULT 0,      -- ditandai hapus, dikirim {id, deleted:true} saat sync
  uploaded        INTEGER DEFAULT 0
);

-- Cache read-only untuk kebutuhan dropdown/offline reference
CREATE TABLE master_cache (
  cache_key       TEXT PRIMARY KEY,       -- 'masters' | 'locations' | 'species' | 'diseases' | 'upts'
  data_json       TEXT NOT NULL,
  fetched_at      TEXT NOT NULL
);
```

### 7.2 Custom Action yang perlu dibuat di FlutterFlow

| Custom Action | Tugas |
|---|---|
| `initLocalDb()` | Buat tabel jika belum ada, dipanggil sekali saat app start |
| `upsertLocalDraft(clientId, payloadJson)` | Simpan/update draft tiap step form disentuh — `sync_status = draft` |
| `markReadyToSync(clientId)` | Set `sync_status = pending`, buat 1 baris di `sync_queue` |
| `savePhotoLocal(clientId, field, filePath)` | Simpan referensi path foto, belum encode base64 |
| `checkConnectivity()` | Wrapper `connectivity_plus`, update App State `isOnline`, dipanggil di app start, saat app resume, dan via listener stream |
| `runSyncQueue()` | Inti sync engine (§7.4) — proses semua baris `sync_queue.status = pending` FIFO by `created_at` |
| `refreshMasterCache(force)` | Ambil `masters`/`locations`/`species`/`diseases`/`upts`, simpan ke `master_cache` dengan TTL |
| `getCachedMasters()` | Baca `master_cache`, dipakai isi dropdown form saat offline |
| `getLocalReportsList(filter)` | Gabungkan `local_reports` (belum synced) + hasil terakhir `getGeneralReportMobile` (sudah synced) untuk tab "Laporan" |

### 7.3 Deteksi Konektivitas

- App State: `isOnline: bool` (default hasil `connectivity_plus` saat start).
- Listener stream `Connectivity().onConnectivityChanged` aktif selama app hidup (dipasang di
  Custom Action yang dipanggil dari `main`/halaman root FlutterFlow) — begitu status berubah
  dari offline → online, **langsung trigger `runSyncQueue()`**, bukan menunggu polling.
- Tambahan **polling ringan tiap 60 detik** (Timer di App State action) sebagai fallback,
  karena event `connectivity_plus` kadang tidak menjamin ada akses internet nyata (hanya
  status radio/WiFi) — sebelum sync, lakukan **actual reachability check** (ping `ping`
  endpoint dengan timeout pendek, mis. 5 detik) supaya tidak boros retry saat WiFi tersambung
  tapi tanpa internet.
- Tombol manual **"Sync Sekarang"** di halaman Profil/List Laporan untuk memicu `runSyncQueue()` langsung.

### 7.4 Alur Sinkronisasi (`runSyncQueue`)

```
1. Guard: jika sedang syncing (flag App State `isSyncing`), skip — cegah proses ganda.
2. Set isSyncing = true.
3. Baca reachability nyata (ping endpoint). Jika gagal → isSyncing = false, stop.
4. Ambil semua baris sync_queue WHERE status='pending' ORDER BY created_at ASC.
5. Untuk tiap baris:
   a. status = 'in_progress'
   b. Bangun payload final:
      - Ambil payload_json dari sync_queue
      - Untuk tiap foto terkait (local_photos WHERE client_id=... AND uploaded=0 AND is_deleted=0):
          baca file lokal → encode base64 → sisipkan ke array field_target yang sesuai
      - Untuk tiap foto is_deleted=1 yang punya remote_media_id:
          sisipkan { id: remote_media_id, deleted: true }
   c. POST ke endpoint terkait (mis. general-reports/saveGeneralReportMobile) via API Group.
   d. Jika sukses (success:true):
        - Update local_reports: server_id, report_code dari response, sync_status='synced'
        - Update local_photos terkait: uploaded=1
        - sync_queue.status = 'done'
   e. Jika gagal karena JARINGAN (timeout/no connection):
        - sync_queue.status tetap 'pending', retry_count += 1
        - Hentikan loop for (asumsikan koneksi putus lagi, jangan habiskan retry ke semua baris)
   f. Jika gagal karena VALIDASI SERVER (success:false, request terkirim tapi ditolak):
        - sync_queue.status = 'failed', simpan last_error
        - local_reports.sync_status = 'failed' → tampil di tab "Gagal Sync" utk dikoreksi manual user
        - LANJUT ke baris berikutnya (bukan connectivity issue, tidak perlu diblokir)
6. isSyncing = false.
```

Backpressure: proses `sync_queue` **satu per satu secara berurutan** (bukan paralel) supaya
urutan `create` → `update susulan` per `client_id` yang sama tidak saling mendahului dan
memicu race condition di server (relasi tabel dicocokkan via `client_id`).

### 7.5 Idempotency & Konsistensi dengan Backend

- Body **selalu** menyertakan `client_id` yang sama untuk satu laporan, dari pembuatan awal
  hingga seluruh update tahap berikutnya. Backend memakai `client_id` untuk `firstOrCreate`
  sehingga retry sync (mis. request pertama sukses di server tapi response timeout di client)
  **aman diulang** — tidak akan membuat laporan duplikat.
- Backend melakukan **diffing** field lama vs baru sebelum update (§5.1 `design-system.md`) —
  artinya app **boleh** mengirim ulang seluruh objek nested (`reporter`, `locations`, dst)
  di setiap sync meskipun sebagian tidak berubah; tidak perlu logika diff di client.
- Karena satu `client_id` hanya pernah dibuat & diedit dari **satu device milik satu petugas**
  (bukan kolaboratif multi-device), **tidak diperlukan conflict resolution** ala CRDT —
  cukup last-write-wins berbasis urutan `sync_queue` (FIFO).

### 7.6 Cache Master Data (agar form tetap berfungsi offline)

- Saat login sukses & online: langsung panggil `refreshMasterCache(force: true)` untuk
  `masters`, `locations` (atau `location-user`), `species/getAll`, `diseases/getAll`, `upts/getAll`.
- TTL cache: 24 jam. Refresh otomatis di background jika app dibuka dan cache lebih tua dari TTL
  **dan** online — tanpa memblokir UI (dropdown tetap pakai cache lama sambil refresh jalan).
- Semua dropdown pada form laporan **wajib** membaca dari `master_cache`, bukan langsung
  hasil API call — supaya form tetap terisi penuh saat offline.

### 7.7 Indikator Status di UI

| `sync_status` | Label Badge | Warna (token §2.1) | Aksi tersedia |
|---|---|---|---|
| `draft` | "Draft Lokal" | `info` / `accent3` | Lanjutkan isi, hapus |
| `pending` | "Menunggu Sync" | `warning` / `accent2` | Hapus (batalkan), tunggu otomatis |
| `syncing` | "Sedang Sync…" | `warning` (dengan spinner kecil) | — |
| `synced` | "Tersinkron" | `success` / `accent1` | Lihat detail, edit (akan re-queue) |
| `failed` | "Gagal Sync" | `error` / `accent4` | Lihat pesan error, edit & kirim ulang |

Badge ini muncul di setiap kartu pada tab "Laporan" dan di header halaman Detail Laporan.

### 7.8 Media/Foto Offline

- Foto diambil via `image_picker`, disimpan sebagai file lokal (`path_provider`) — **jangan**
  encode base64 saat pengambilan foto (boros memori untuk banyak draft menumpuk offline).
- Encode base64 dilakukan **tepat sebelum** request sync dikirim (lazy encoding), lalu
  dibuang dari memori setelah request selesai.
- Kompresi gambar (`flutter_image_compress`, target ~70% quality / max 1280px) sebelum
  disimpan lokal — penting karena payload base64 dikirim di atas koneksi lapangan yang lemah.

---

## 8. Push Notification (FCM)

Backend memakai legacy FCM HTTP API dgn 3 pemicu (§10 `design-system.md`): laporan baru,
satwa dilindungi, penyakit prioritas.

1. Setup `firebase_messaging`, minta permission saat onboarding.
2. Dapatkan token FCM → kirim ke `users/user/token` setelah login **dan** setiap kali token
   berubah (`onTokenRefresh`).
3. Notifikasi masuk saat app foreground/background → tampilkan local notification, tap →
   deep-link ke halaman Detail Laporan terkait (jika payload data berisi id laporan) atau ke
   tab Notifikasi jika tidak spesifik.
4. Saat logout: backend otomatis set `user.devices = null` — app tidak perlu hapus token FCM
   sendiri, cukup panggil `auth/logout` seperti biasa.

---

## 9. Keamanan & Sesi

1. **Token storage**: `access_token` dari Passport disimpan via `flutter_secure_storage`
   (Custom Action), **bukan** SharedPreferences/App State biasa (App State FlutterFlow persist
   ke SharedPreferences tanpa enkripsi by default).
2. **Logout**: hapus token lokal, panggil `auth/logout`. **Jangan hapus** `local_reports` /
   `sync_queue` — laporan yang belum tersinkron harus tetap ada jika user login kembali
   dengan akun yang sama (field kerja sering ganti-ganti koneksi, bukan alasan untuk logout
   permanen). Jika user berbeda login di device yang sama, tampilkan peringatan ada draft
   milik user sebelumnya sebelum menimpa sesi.
3. **401 dari API manapun** → treat sebagai token expired → paksa ke halaman Login, **draft
   lokal tetap aman** (tidak hilang), sync otomatis lanjut setelah login ulang.
4. **Mode Latihan** (`user.training_mode`): tampilkan banner permanen (§2.4) selama flag ini
   aktif — field officer harus selalu sadar datanya tidak dihitung produksi.

---

## 10. Checklist Setup Proyek FlutterFlow

**Packages (Custom Code → Dependencies)**
`sqflite` atau `drift`, `connectivity_plus`, `uuid`, `flutter_secure_storage`,
`image_picker`, `path_provider`, `flutter_image_compress`, `firebase_messaging`, `geolocator`.

**Custom Data Types**: `GeneralReportSource` + 8 sub-tipe (§5), `Option`, `Species`, `Disease`,
`Upt`, `LocationAdmin` (province/district/subdistrict/village).

**API Group**: `SehatSatliAPI` dengan seluruh endpoint §6.3 + variabel header `Authorization`.

**Custom Actions**: seluruh daftar §7.2 + `isApiSuccess()` (§6.4) + `sendFcmTokenToServer()`.

**App State**: `accessToken`, `currentUser` (object lengkap dari login), `isOnline`,
`isSyncing`, `pendingSyncCount` (badge counter di tab Laporan/Beranda).

**Theme Settings**: isi sesuai §2.1 & §2.2.

---

## 11. Ringkasan Prioritas Implementasi

1. Local DB schema (§7.1) + Custom Actions inti (`initLocalDb`, `upsertLocalDraft`, `runSyncQueue`).
2. Auth flow + secure token storage + `isApiSuccess()` helper.
3. Master cache (`refreshMasterCache`, `getCachedMasters`) — wajib sebelum form bisa dibangun offline-safe.
4. Form laporan multi-step yang menulis ke `local_reports` di setiap step.
5. Sync engine + connectivity listener + indikator status (§7.7).
6. Foto offline (ambil → kompres → simpan path → lazy base64 saat sync).
7. Push notification FCM + deep link.
8. Halaman role-gated (verifikasi dokter, pengesahan kepala UPT) + laporan masyarakat (opsional).
