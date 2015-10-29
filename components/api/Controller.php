<?php

namespace mobidev\swagger\components\api;

use mobidev\swagger\components\OptionsRequestFilter;
use mobidev\swagger\components\QueryParamAuthSwagger;
use yii\base\NotSupportedException;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\web\Response;

class Controller extends \yii\rest\Controller
{
    public function behaviors()
    {
        // Options Request Behavior must going at first because swagger makes OPTIONS requests before POST
        // and this behavior must run early than VerbsFilter
        $behaviors = [
            'optionsRequestFilter' => OptionsRequestFilter::className(),
        ];
        $behaviors = array_merge($behaviors, parent::behaviors(), [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'corsFilter' => Cors::className(),
            'authenticator' => [
                'class' => QueryParamAuthSwagger::className(),
            ],
        ]);
        return $behaviors;
    }

    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    protected function verbs()
    {
        throw new NotSupportedException('You should override this method for correct work of mobidev/yii2-swagger');
    }

}