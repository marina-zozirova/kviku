<?php

namespace app\models;

use app\helpers\ErrorHelper;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * Модель для работы с сообщениями из RabbitMQ
 *
 * @property int $id ID записи
 * @property string $request_id UUID
 * @property int $user_id ID пользователя
 * @property string $user_email Email пользователя
 * @property string|null $user_name Имя пользователя
 * @property string $action Тип действия
 * @property string|null $ip_address IP адрес клиента
 * @property float|null $total_amount Общая сумма транзакции
 * @property string|null $currency Код валюты
 * @property string|null $country Код страны
 * @property string|null $city Город
 * @property string|null $raw_data Исходные данные JSON
 * @property int $created_at Время создания записи
 * @property int $updated_at Время обновления записи
 */
class Message extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%messages}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['request_id', 'user_id', 'user_email', 'action'], 'required'],
            [['request_id'], 'string', 'max' => 255],
            [['request_id'], 'unique'],
            [['user_id'], 'integer'],
            [['user_email'], 'email'],
            [['user_email', 'user_name'], 'string', 'max' => 255],
            [['action'], 'string', 'max' => 100],
            [['ip_address'], 'string', 'max' => 45],
            [['ip_address'], 'ip'],
            [['total_amount'], 'number'],
            [['currency'], 'string', 'max' => 3],
            [['country'], 'string', 'max' => 2],
            [['city'], 'string', 'max' => 100],
            [['raw_data'], 'string'],

            // Защита от XSS и SQL-инъекций
            [['user_email', 'user_name', 'action', 'city'], 'filter', 'filter' => [$this, 'sanitizeInput']],
        ];
    }

    /**
     * @param string|null $value
     * @return string
     */
    public function sanitizeInput($value)
    {
        if (empty($value)) {
            return $value;
        }

        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        $sqlPatterns = [
            '/(\bUNION\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|\bDROP\b)/i',
            '/--/',
            '/\/\*.*\*\//',
        ];

        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                Yii::warning("Обнаружена потенциальная SQL-инъекция: {$value}", __METHOD__);
                return '';
            }
        }

        return $value;
    }

    /**
     * создание из json
     * @param string $jsonData JSON строка с данными сообщения
     * @return self
     * @throws \Exception
     */
    public static function createFromJson($jsonData)
    {
        $data = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = "Некорректный JSON: " . json_last_error_msg();
            Yii::error($error, __METHOD__);
            throw new \Exception($error);
        }

        $message = new self();
        $message->setAttributes([
            'request_id' => $data['request_id'] ?? null,
            'user_id' => $data['user']['id'] ?? null,
            'user_email' => $data['user']['email'] ?? null,
            'user_name' => $data['user']['name'] ?? null,
            'action' => $data['action'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'total_amount' => $data['payload']['total_amount'] ?? null,
            'currency' => $data['payload']['currency'] ?? null,
            'country' => $data['geo']['country'] ?? null,
            'city' => $data['geo']['city'] ?? null,
            'raw_data' => $jsonData,
        ]);

        if (!$message->validate()) {
            $errorText = $message->getFormattedErrors();
            Yii::error("Ошибка валидации: {$errorText}", __METHOD__);
            throw new \Exception("Ошибка валидации: {$errorText}");
        }

        return $message;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID записи',
            'request_id' => 'UUID',
            'user_id' => 'ID пользователя',
            'user_email' => 'Email пользователя',
            'user_name' => 'Имя пользователя',
            'action' => 'Тип действия',
            'ip_address' => 'IP адрес',
            'total_amount' => 'Общая сумма',
            'currency' => 'Валюта',
            'country' => 'Страна',
            'city' => 'Город',
            'raw_data' => 'Исходные данные JSON',
            'created_at' => 'Время создания',
            'updated_at' => 'Время обновления',
        ];
    }
}