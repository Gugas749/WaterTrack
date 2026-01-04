<?php

namespace backend\models;

use common\models\Meterreading;
use Yii;
use yii\base\Model;

class Addreadingform extends Model
{
    public $meterID;
    public $userID;
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
            [['meterID', 'userID', 'reading', 'accumulatedConsumption',
                'waterPressure', 'date', 'readingType'], 'required'],

            [['meterID', 'userID', 'readingType', 'problemID'], 'integer'],

            ['problemID', 'filter', 'filter' => function ($v) {
                return $v === '' ? null : (int)$v;
            }],

            [['reading', 'accumulatedConsumption', 'waterPressure'], 'number'],
            ['desc', 'string', 'max' => 255],
            ['date', 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'meterID' => 'Contador',
            'userID' => 'Utilizador',
            'reading' => 'Leitura',
            'accumulatedConsumption' => 'Consumo Acumulado',
            'waterPressure' => 'Pressão de Água',
            'desc' => 'Descrição',
            'date' => 'Data',
            'readingType' => 'Tipo de Leitura',
            'problemState' => 'Problema',
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
        $reading->userID = $this->userID;
        $reading->reading = $this->reading;
        $reading->accumulatedConsumption = $this->accumulatedConsumption;
        $reading->waterPressure = $this->waterPressure;
        $reading->desc = $this->desc;
        $reading->date = $this->date;
        $reading->readingType = $this->readingType;

        if ($this->problemID !== null) {
            $reading->problemState = 1;
            $reading->problemID = $this->problemID;
        } else {
            $reading->problemState = 0;
            $reading->problemID = null;
        }

        if (!$reading->save()) {
            Yii::error($reading->errors, __METHOD__);
            return false;
        }

        return true;
    }
}
