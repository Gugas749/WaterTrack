<?php

namespace backend\models;

use common\models\Meterreading;
use Yii;
use yii\base\Model;

class Addreadingform extends Model
{
    public $meterID;
    public $tecnicoID;
    public $reading;
    public $accumulatedConsumption;
    public $waterPressure;
    public $desc;
    public $date;
    public $readingType;
    public $problemID;

    public function rules()
    {
        return [
            [['meterID', 'tecnicoID', 'reading', 'accumulatedConsumption', 'waterPressure', 'date'], 'required'],

            [['meterID', 'tecnicoID'], 'integer'],

            [['reading', 'accumulatedConsumption', 'waterPressure'], 'number'],
            ['date', 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'meterID' => 'Contador',
            'tecnicoID' => 'Utilizador',
            'reading' => 'Leitura',
            'accumulatedConsumption' => 'Consumo Acumulado',
            'waterPressure' => 'Pressão de Água',
            'date' => 'Data',
        ];
    }

    public function createReading()
    {
        if (!$this->validate()) {
            Yii::error(json_encode($this->errors), __METHOD__);
            return false;
        }

        $reading = new Meterreading();
        $reading->meterID = $this->meterID;
        $reading->tecnicoID = $this->tecnicoID;
        $reading->reading = $this->reading;
        $reading->accumulatedConsumption = $this->accumulatedConsumption;
        $reading->waterPressure = $this->waterPressure;
        $reading->date = $this->date;

        if (!$reading->save()) {
            Yii::error($reading->errors, __METHOD__);
            return false;
        }

        return true;
    }
}
