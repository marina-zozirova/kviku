<?php

use yii\db\Migration;

class m251111_211125_create_messages_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%messages}}', [
            'id' => $this->primaryKey()->comment('ID записи'),
            'request_id' => $this->string(255)->notNull()->unique()->comment('UUID'),
            'user_id' => $this->integer()->notNull()->comment('ID пользователя'),
            'user_email' => $this->string(255)->notNull()->comment('email пользователя'),
            'user_name' => $this->string(255)->comment('Имя пользователя'),
            'action' => $this->string(100)->notNull()->comment('Тип действия'),
            'ip_address' => $this->string(45)->comment('IP адрес клиента'),
            'total_amount' => $this->decimal(10, 2)->comment('Общая сумма транзакции'),
            'currency' => $this->string(3)->comment('Код валюты'),
            'country' => $this->string(2)->comment('Код страны'),
            'city' => $this->string(100)->comment('Город'),
            'raw_data' => $this->text()->comment('Исходные данные JSON в полном виде'),
            'created_at' => $this->integer()->notNull()->comment('Время создания записи'),
            'updated_at' => $this->integer()->notNull()->comment('Время обновления записи')
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%messages}}');
    }
}