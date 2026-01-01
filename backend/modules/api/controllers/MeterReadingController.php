<?php

namespace backend\modules\api\controllers;

use yii\rest\ActiveController;

class MeterReadingController extends ActiveController
{
    public $modelClass = 'common\models\Meterreading';

    public function actionFromuser($id)
    {
        $model = new $this->modelClass;
        $recs = $model::find()->where(['userID' => $id])->one();

        if (!$recs) {
            return ['error' => 'Readings not found'];
        }

        return $recs;
    }

    public function actionFrommeter($id)
    {
        $model = new $this->modelClass;
        $recs = $model::find()->where(['meterID' => $id])->all();

        if (!$recs) {
            return ['error' => 'Readings not found'];
        }

        return $recs;
    }

    public function actionProblem($id)
    {
        $model = new $this->modelClass;
        $recs = $model::find()->where(['problemID' => $id])->one();

        if (!$recs) {
            return ['error' => 'Readings not found'];
        }

        return $recs;
    }
}
