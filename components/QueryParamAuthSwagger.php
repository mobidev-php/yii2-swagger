<?php

namespace mobidev\swagger\components;

use yii\filters\auth\QueryParamAuth;

/**
 * @inheritdoc
 * This authentificator just adds support for hardcoded api_key on online.swagger.io
 * @package mobidev\swagger\components
 */
class QueryParamAuthSwagger extends QueryParamAuth
{
    public function authenticate($user, $request, $response)
    {
        $apiKey = $request->get('api_key');
        if (!is_null($apiKey)) {
            $_GET[$this->tokenParam] = $apiKey;
        }
        return parent::authenticate($user, $request, $response);
    }

}