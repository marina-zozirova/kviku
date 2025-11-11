<?php

namespace app\commands;

use yii\console\Controller;
use Ramsey\Uuid\Uuid;
use Yii;

class ClientController extends Controller
{
    /**
     * @return void
     */
    public function actionSend()
    {
        $this->stdout("Клиент запущен. Отправка сообщений в очередь...\n");
        Yii::info("Client started sending messages", __METHOD__);

        $counter = 0;
        while (true) {
            try {
                $message = $this->generateMessage();

                if (Yii::$app->rabbitmq->publish($message)) {
                    $counter++;
                    $this->stdout("[$counter] Сообщение отправлено: {$message['request_id']}\n");
                    Yii::info("Message published: {$message['request_id']}", __METHOD__);
                } else {
                    $this->stdout("Ошибка отправки сообщения\n");
                    Yii::error("Failed to publish message", __METHOD__);
                }

                sleep(5);

            } catch (\Exception $e) {
                $this->stderr("Ошибка: " . $e->getMessage() . "\n");
                Yii::error("Client error: " . $e->getMessage(), __METHOD__);
                sleep(5);
            }
        }
    }

    /**
     * @return array
     * @throws \Random\RandomException
     */
    private function generateMessage()
    {
        $actions = ['bulk_purchase', 'single_purchase', 'subscription', 'refund'];
        $cities = ['San Francisco', 'New York', 'Los Angeles', 'Chicago', 'Houston'];
        $countries = ['US', 'CA', 'UK', 'DE', 'FR'];

        $isInvalid = (rand(1, 100) <= 20);

        return [
            'request_id' => Uuid::uuid4()->toString(),
            'user' => [
                'id' => $isInvalid ? 'NOT_A_NUMBER' : rand(100000, 999999),
                'email' => $isInvalid ? 'invalid-email' : 'user' . rand(1, 1000) . '@example.com',
                'name' => $isInvalid ? '<script>alert("xss")</script>' : 'User ' . rand(1, 100),
                'roles' => ['user', 'beta_tester'],
                'is_active' => true,
                'profile' => [
                    'age' => rand(18, 65),
                    'gender' => rand(0, 1) ? 'male' : 'female',
                    'registered_at' => date('Y-m-d\TH:i:s\Z', strtotime('-' . rand(1, 1000) . ' days')),
                    'last_login' => date('Y-m-d\TH:i:s\Z', strtotime('-' . rand(1, 24) . ' hours')),
                    'preferences' => [
                        'theme' => rand(0, 1) ? 'dark' : 'light',
                        'language' => 'en',
                        'notifications' => [
                            'email' => true,
                            'sms' => false,
                            'push' => true,
                        ],
                    ],
                ],
            ],
            'action' => $isInvalid ? 'UNION SELECT * FROM users--' : $actions[array_rand($actions)],
            'timestamp' => date('Y-m-d\TH:i:s\Z'),
            'ip_address' => $isInvalid ? 'invalid.ip.address' : rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255),
            'payload' => [
                'total_amount' => round(rand(10, 500) + (rand(0, 99) / 100), 2),
                'currency' => 'USD',
                'items' => [
                    [
                        'sku' => 'A-' . rand(100, 999),
                        'name' => 'Product ' . rand(1, 50),
                        'price' => round(rand(10, 200) + (rand(0, 99) / 100), 2),
                        'quantity' => rand(1, 5),
                        'tags' => ['electronics', 'gadgets'],
                        'discount' => null,
                        'metadata' => [
                            'color' => 'black',
                            'warranty_months' => 12,
                            'warehouse_id' => rand(500, 600),
                        ],
                    ],
                ],
            ],
            'geo' => [
                'lat' => round(rand(-90, 90) + (rand(0, 9999) / 10000), 4),
                'lon' => round(rand(-180, 180) + (rand(0, 9999) / 10000), 4),
                'country' => $countries[array_rand($countries)],
                'region' => 'CA',
                'city' => $cities[array_rand($cities)],
            ],
            'security' => [
                'signature' => bin2hex(random_bytes(16)),
                'token_valid' => true,
                'source' => 'api_gateway',
                'headers' => [
                    'User-Agent' => 'TestClient/1.2.3',
                    'X-Forwarded-For' => rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255),
                    'Authorization' => 'Bearer Bearer fake_jwt_token_' . bin2hex(random_bytes(8)),
                ],
            ],
            'events' => [
                [
                    'type' => 'validation_start',
                    'timestamp' => date('Y-m-d\TH:i:s.v\Z'),
                ],
                [
                    'type' => 'save_to_db',
                    'timestamp' => date('Y-m-d\TH:i:s.v\Z'),
                    'status' => 'success',
                    'duration_ms' => rand(10, 50),
                ],
            ],
            'attachments' => [],
            'debug' => false,
        ];
    }
}