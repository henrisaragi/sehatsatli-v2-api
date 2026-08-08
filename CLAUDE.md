# CLAUDE.md - Project Guidelines & Context

## Project Overview

- **Type:** REST API backend (tidak ada Blade admin panel — hanya `welcome.blade.php` default bawaan Laravel).
- **Framework:** Laravel 9.52 (`laravel/framework: ^9.19`)
- **PHP Version:** 8.4
- **Database:** MySQL
- **Auth:** Laravel Passport (OAuth2) & Sanctum
- **Cache/Queue Driver:** Predis (Redis client)
- **API Docs:** dedoc/scramble (auto-generated OpenAPI docs)
- **Key Modules:**
    - Real-time broadcasting via Pusher (`config/broadcasting.php`, driver `pusher`)
    - Push notifications via Firebase (kreait/laravel-firebase + laravel-notification-channels/fcm)
    - Aktivitas & audit log via Spatie Activitylog
    - Backup otomatis via Spatie Backup
    - Media/file handling via Spatie Media Library
    - Debugging via Laravel Telescope
    - Log viewer via rap2hpoutre/laravel-log-viewer

---

## Technical Stack & Architecture Guidelines

### 1. Code Style & Standards

- Ikuti standar **PSR-12**.
- Gunakan **Strict Types** pada setiap file PHP baru: `declare(strict_types=1);` (belum diterapkan di kode lama — terapkan bertahap saat menyentuh file terkait, jangan ubah file lain sekaligus).
- **Form Request:** Pisahkan logika validasi dari Controller ke file `FormRequest` (saat ini validasi masih banyak dilakukan langsung di Controller — refactor bertahap saat menyentuh endpoint terkait).
- **Services & Repositories:** Tempatkan logika bisnis yang kompleks di layer `Services`, jangan menumpuk kode bisnis di Controller atau Model (folder ini belum ada di proyek — buat saat dibutuhkan, jangan taruh semua di `app/Http/Controllers`).
- **Type Hinting & Return Types:** Setiap method/fungsi **wajib** memiliki type hint pada argumen dan return type secara eksplisit untuk kode baru.

### 2. Laravel Specific Conventions

- Gunakan **Eloquent API Resources** untuk format response JSON, bukan `$model->toArray()` manual (saat ini banyak controller masih return Model/array langsung — arahkan ke API Resources untuk endpoint baru/yang direfactor).
- Gunakan **Enums** (native PHP 8.1+, tersedia karena base PHP 8.4) untuk status atau tipe data yang bersifat fixed, menggantikan magic string/int.
- Gunakan **Database Transactions** (`DB::transaction`) untuk operasi mutasi data berseri/multi-tabel.
- Ini proyek API murni — tidak ada Blade view untuk data listing, jadi aturan terkait Blade/DataTables/Bootstrap tidak relevan.

### 3. Database & Migrations

- Setiap migration wajib memiliki foreign key constraint dan indexes pada kolom yang sering di-query.
- Selalu sediakan `down()` method pada migration untuk kemudahan rollback.
- Gunakan `Model::preventLazyLoading(!app()->isProduction());` pada `AppServiceProvider` untuk mencegah issue N+1 Query (belum ada di `AppServiceProvider` saat ini — tambahkan bila diminta).

---

## Development Workflow & Commands

### Useful Commands

```bash
# Development Server
php artisan serve
php artisan migrate

# Run Queue & Broadcasting (Pusher, bukan Reverb)
php artisan queue:work

# Run Tests (paratest belum terinstall, jalankan tanpa --parallel)
php artisan test

# Code Formatting
./vendor/bin/pint
```

## claude config set -g allowedTools "Bash(php artisan*),Bash(sed*),Bash(git\*)"

## Terminal & Command Permissions

- **Auto-Approved Commands:**
    - `php artisan *`
    - `herd php artisan *`
    - `sed *`
    - `git status` / `git add` / `git commit`
- Execute terminal commands directly without asking for confirmation if they match the auto-approved pattern above.

---

## AI / Claude Interaction Rules (Token Savers)

1. **Be Concise & Direct:** Berikan jawaban langsung ke kode solusi tanpa penjelasan teori/intro yang terlalu panjang.
2. **No Unnecessary Explanations:** Jangan jelaskan cara kerja fungsi standar Laravel kecuali diminta secara eksplisit.
3. **Diff/Incremental Code Only:** Untuk perbaikan/refactoring, cukup tunjukkan bagian kode yang diubah atau ditambah, bukan seluruh isi file jika file tersebut panjang.
4. **Follow Project Structure:** Pertahankan konvensi penamaan dan struktur folder yang sudah ada di proyek ini (Controllers, Models, Notifications, Events, Listeners, Traits).
