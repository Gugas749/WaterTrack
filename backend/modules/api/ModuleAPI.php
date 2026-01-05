<?php

namespace backend\modules\api;

use Yii;

/**
 * api module definition class
 */
class ModuleAPI extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'backend\modules\api\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        // BASIC AUTH
        Yii::$app->user->enableSession = false;
        // custom initialization code goes here
    }
}
