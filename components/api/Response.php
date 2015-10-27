<?php

namespace mobidev\swagger\components\api;

use yii\base\Event;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Response event handler for standardization an API responses
 * @package mobidev\swagger\components\api
 */
class Response
{
    const STATUS_OK    = 'ok';
    const STATUS_ERROR = 'error';

    /**
     * Event handler for Response
     * @param Event $event
     */
    public static function beforeSend($event)
    {
        /** @var \yii\web\Response $response */
        $response = $event->sender;
        if ($response->isSuccessful) {
            $response->data = ArrayHelper::merge([
                'status' => self::STATUS_OK,
            ], $response->data);
        } else {
            $e = Yii::$app->getErrorHandler()->exception;
            $message = $e->getMessage();
            $response->format = \yii\web\Response::FORMAT_JSON;
            $response->data = [
                'status' => self::STATUS_ERROR,
                'code' => $response->getStatusCode(),
                'message' => !empty($message) ? $message : $response::$httpStatuses[$response->getStatusCode()],
            ];
            // Add validation errors to response
            if ($e instanceof DataValidationHttpException) {
                $response->data = array_merge($response->data, $e->validationErrors);
            }
        }
    }




}
