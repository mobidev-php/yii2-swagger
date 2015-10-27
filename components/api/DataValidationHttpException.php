<?php

namespace mobidev\swagger\components\api;

use yii\web\HttpException;

class DataValidationHttpException extends HttpException
{
    public $validationErrors = [];

    /**
     * Constructor.
     * @param array $errors error messages
     * @param integer $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($errors = [], $code = 0, \Exception $previous = null)
    {
        $this->validationErrors = $errors;
        parent::__construct(422, null, $code, $previous);
    }
}