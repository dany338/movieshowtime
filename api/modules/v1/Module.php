<?php

namespace api\modules\v1;

class Module extends \yii\base\Module
{
    //public $enableCsrfValidation = false;
    public $controllerNamespace = 'api\modules\v1\controllers';

    public function init()
    {
        parent::init();
        \Yii::$app->user->enableSession = false;
    }
}
