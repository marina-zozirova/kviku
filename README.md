# тестовое квику 


```bash
git clone https://github.com/marina-zozirova/kviku.git
cd kviku
docker-compose up -d --build
```

## Запуск
```bash
# прослушка очереди
docker exec -it yii2_app php yii server/listen

# отправка сообщений
docker exec -it yii2_app php yii client/send
```


- RabbitMQ: http://localhost:15672 (guest/guest)
- PostgreSQL: localhost:5432 (yii2user/yii2pass)

