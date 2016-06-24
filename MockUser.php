<?php

namespace mobidev\swagger;

use yii\web\IdentityInterface;

class MockUser implements IdentityInterface
{
    public static function findIdentity($id)
    {
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
    }

    public function getId()
    {
    }

    public function getAuthKey()
    {
    }

    public function validateAuthKey($authKey)
    {
    }

}