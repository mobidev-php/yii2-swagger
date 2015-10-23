<?php

namespace mobidev\swagger\components;

use yii\base\ActionFilter;
use Yii;

/**
 * Class OptionsRequestFilter
 * @package mobidev\swagger\components
 * This behavior just return response HTTP code 200 and break executing action. Its need for correct work a swagger.
 * So if you really use OPTIONS requests in your actions do not use this behavior
 */
class OptionsRequestFilter extends ActionFilter
{
    public function beforeAction($action)
    {
        return !Yii::$app->request->isOptions;
    }
}