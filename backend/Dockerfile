FROM php:8.4-cli-alpine

RUN apk add --no-cache \
        $PHPIZE_DEPS \
        oniguruma-dev \
        unzip \
        mysql-client \
    && docker-php-ext-install pdo pdo_mysql mbstring \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# No hay secretos reales aca: todas las variables las inyecta docker-compose
# via `environment:`, y esas siempre tienen prioridad sobre lo que traiga un
# .env. Esto solo evita que Dotenv se queje de que el archivo no existe.
RUN cp .env.example .env

RUN chmod +x docker-entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["./docker-entrypoint.sh"]

# --no-reload es obligatorio ademas de cosmetico: sin el, "artisan serve" mata
# y reinicia su proceso hijo cada vez que detecta un cambio en .env, y al
# reiniciar solo reenvia al hijo las variables de una lista fija (PATH, HOME,
# etc) — descartando JWT_SECRET, DB_PASSWORD y cualquier otro secreto real que
# venga de docker-compose. Sintoma tipico sin este flag: el primer login
# funciona y los siguientes fallan con "Key cannot be empty".
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]
