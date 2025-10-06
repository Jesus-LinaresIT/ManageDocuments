# --- Etapa 1: Composer (dependencias PHP) ---
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress
COPY . .
# Reconfirma por si hay paquetes con path repos
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress || true

# --- Etapa 2: Node (assets Vite) ---
FROM node:20-bookworm AS assets
WORKDIR /app
COPY package.json package-lock.json* pnpm-lock.yaml* yarn.lock* ./
RUN if [ -f pnpm-lock.yaml ]; then npm i -g pnpm && pnpm i; \
    elif [ -f yarn.lock ]; then npm i -g yarn && yarn; \
    else npm ci; fi
COPY . .
RUN if [ -f pnpm-lock.yaml ]; then pnpm run build; \
    elif [ -f yarn.lock ]; then yarn build; \
    else npm run build; fi

# --- Etapa 3: Runtime súper simple (PHP built-in server) ---
FROM php:8.3-cli-bookworm

# Extensiones mínimas que Laravel suele necesitar
RUN apt-get update && apt-get install -y --no-install-recommends \
      git unzip libzip-dev libpng-dev \
  && docker-php-ext-install pdo_mysql mbstring bcmath opcache \
  && rm -rf /var/lib/apt/lists/*

ENV APP_ENV=production \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

WORKDIR /var/www/html

# Copia el código + vendor + build de assets
COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

# Permisos Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

# Servidor embebido de PHP (suficiente para demo)
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
