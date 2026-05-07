FROM php:8.3-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libxml2-dev \
    && docker-php-ext-install zip gd xml pdo pdo_sqlite \
    && pecl install grpc && docker-php-ext-enable grpc \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Node
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build \
    && php artisan storage:link || true \
    && mkdir -p database && touch database/database.sqlite

# Decode Firebase credentials from env at runtime
RUN echo '#!/bin/sh\n\
if [ -n "$FIREBASE_CREDENTIALS_JSON" ]; then\n\
  echo "$FIREBASE_CREDENTIALS_JSON" | base64 -d > /app/firebase-credentials.json\n\
fi\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}' > /start.sh \
    && chmod +x /start.sh

EXPOSE 8000

CMD ["/start.sh"]
