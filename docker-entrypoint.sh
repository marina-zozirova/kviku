#!/bin/bash
set -e

echo "подключение к бд"
until php yii migrate --interactive=0 2>/dev/null; do
  echo "бд недоступна"
  sleep 2
done

echo "запуск миграций"
php yii migrate --interactive=0

echo "бд подключена"

exec "$@"