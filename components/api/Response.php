<?php

namespace mobidev\swagger\components\api;

use yii\helpers\ArrayHelper;

/**
 * It's a helper for standardization an API responses
 * @package mobidev\swagger\components\api
 */
class Response extends \yii\web\Response
{
    /**
     * Returns success response
     * @param array $data
     * @return \yii\web\Response
     */
    public static function success(array $data = [])
    {
        $resp = \Yii::$app->response;
        $resp->setStatusCode(200);
        $resp->data = ArrayHelper::merge([
            'status' => 'ok',
        ], $data);
        return $resp;
    }

    /**
     * Returns fail response
     * @param integer $code
     * @param array $data
     * @return \yii\web\Response
     */
    public static function fail($code = 400, array $data = [])
    {
        $resp = \Yii::$app->response;
        $resp->setStatusCode($code);
        $resp->data = ArrayHelper::merge([
            'status' => 'error',
            'message' => static::$httpStatuses[$code],
        ], $data);
        return $resp;
    }

}
