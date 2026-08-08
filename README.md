Cara memulai :

0. Setup .env, kosongkan database
1. composer install
2. Import database di /database/sehatsatli.sql.zip (di unzip jika perlu)
3. Jalankan di command line : php artisan migrate
4. Jalankan di command line : php artisan db:seed
5. Jika belum pernah jalankan laravel passport jalankan: php artisan passport:install, jika sudah lanjut no 5
6. Jalankan di command line : php artisan passport:client --personal
7. Jalankan linking storage Laravel : php artisan storage:link

Jika perlu untuk testing seeder :
php artisan migrate:rollback && php artisan migrate && php artisan db:seed
