<?php

namespace app\helpers;

use yii\base\Model;

class ErrorHelper
{
    /**
     * форматирование ошибок
     * @param Model $model
     * @return string
     */
    public static function formatModelErrors(Model $model)
    {
        $errorMessages = [];
        foreach ($model->getErrors() as $field => $errors) {
            $errorMessages[] = "$field: " . implode(', ', $errors);
        }
        return implode('; ', $errorMessages);
    }
}