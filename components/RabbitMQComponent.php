<?php

namespace app\components;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\base\Component;
use Yii;

class RabbitMQComponent extends Component
{
    public $host;
    public $port;
    public $user;
    public $password;
    public $queueName;

    private $connection;
    private $channel;

    /**
     * @return void
     * @throws \Exception
     */
    public function init()
    {
        parent::init();
        $this->connect();
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function connect()
    {
        try {
            $this->connection = new AMQPStreamConnection(
                $this->host,
                $this->port,
                $this->user,
                $this->password
            );
            $this->channel = $this->connection->channel();

            $this->channel->queue_declare(
                $this->queueName,
                false,
                true,
                false,
                false
            );

            Yii::info("Подключено к RabbitMQ", __METHOD__);
        } catch (\Exception $e) {
            Yii::error("Ошибка подключения к RabbitMQ: " . $e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * @param $message
     * @return bool
     */
    public function publish($message)
    {
        try {
            $msg = new AMQPMessage(
                json_encode($message),
                ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
            );

            $this->channel->basic_publish($msg, '', $this->queueName);

            Yii::info("Сообщение опубликовано в очередь", __METHOD__);
            return true;
        } catch (\Exception $e) {
            Yii::error("Ошибка публикации сообщения: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * @param $callback
     * @return void
     * @throws \Exception
     */
    public function consume($callback)
    {
        try {
            $this->channel->basic_qos(null, 1, null);
            $this->channel->basic_consume(
                $this->queueName,
                '',
                false,
                false,
                false,
                false,
                $callback
            );

            Yii::info("Ожидание сообщений в очереди: {$this->queueName}", __METHOD__);

            while ($this->channel->is_open()) {
                try {
                    $this->channel->wait(null, false, 30);
                } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            Yii::error("Ошибка получения сообщений: " . $e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }
}