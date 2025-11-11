<?php

namespace app\commands;

use app\helpers\ErrorHelper;
use yii\console\Controller;
use app\models\Message;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;

class ServerController extends Controller
{
    /**
     * @return void
     */
    public function actionListen()
    {
        $this->stdout("Сервер запущен. Ожидание сообщений...\n");
        Yii::info("Server started listening for messages", __METHOD__);

        try {
            $callback = function (AMQPMessage $msg) {
                $this->processMessage($msg);
            };

            Yii::$app->rabbitmq->consume($callback);

        } catch (\Exception $e) {
            $this->stderr("Ошибка: " . $e->getMessage() . "\n");
            Yii::error("Failed to consume messages: " . $e->getMessage(), __METHOD__);
        }
    }

    /**
     * @param AMQPMessage $msg
     * @return void
     */
    private function processMessage(AMQPMessage $msg)
    {
        $body = $msg->body;

        try {
            $this->stdout("Получено сообщение\n");

            $message = Message::createFromJson($body);

            if ($message->save()) {
                $this->stdout("Сообщение сохранено: {$message->request_id}\n");
                Yii::info("Сообщение успешно сохранено: {$message->request_id}", __METHOD__);

                $msg->ack();
            } else {
                $errorText = ErrorHelper::formatModelErrors($message);
                Yii::error("Ошибки валидации: {$errorText}", __METHOD__);
                throw new \Exception("Не удалось сохранить сообщение: {$errorText}");
            }

        } catch (\Exception $e) {
            $this->stderr("Ошибка обработки: " . $e->getMessage() . "\n");
            Yii::error("Ошибка обработки сообщения: " . $e->getMessage() . " | Body: " . $body, __METHOD__);

            $msg->nack(false);
        }
    }
}