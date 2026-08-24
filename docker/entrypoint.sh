#!/bin/bash
set -e

# --force wajib karena APP_ENV=production menolak migrate interaktif.
# set -e di atas memastikan kalau migration gagal, nginx TIDAK ikut start
# melayani traffic ke skema database yang belum siap.
php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

php-fpm -D
# nginx di foreground (bukan -D) supaya jadi process utama yang menahan
# container tetap hidup -- Docker container mati begitu process utamanya
# selesai.
nginx -g "daemon off;"
