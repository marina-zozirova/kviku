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

пометка: если падает прослушка, то, скорее всего, запускается реббит. выждать около 10 секунд и попробовать прослушать очередь еще раз

