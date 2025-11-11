#!/bin/bash
set -e

if [ ! -d "/app/vendor" ]; then
    echo "установка зависимостей"
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

echo "подключение к бд"
until php yii migrate --interactive=0 2>/dev/null; do
  echo "бд недоступна"
  sleep 2
done

echo "запуск миграций"
php yii migrate --interactive=0

echo "бд подключена"

exec "$@"