#!/bin/sh
set -e

echo "Esperando a que la base de datos acepte conexiones..."
until php artisan migrate --force; do
  echo "DB todavia no esta lista, reintentando en 3s..."
  sleep 3
done

php artisan db:seed --force

exec "$@"
