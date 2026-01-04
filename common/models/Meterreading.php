<?php

namespace common\models;

/**
 * This is the model class for table "meterreading".
 *
 * @property int $id
 * @property int $tecnicoID
 * @property int $meterID
 * @property string $reading
 * @property string $accumulatedConsumption
 * @property string $date
 * @property string $waterPressure
 *
 * @property Meter $meter
 * @property User $tecnico
 */
class Meterreading extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meterreading';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tecnicoID', 'meterID', 'reading', 'accumulatedConsumption', 'date', 'waterPressure'], 'required'],
            [['tecnicoID', 'meterID'], 'integer'],
            [['date'], 'safe'],
            [['reading', 'accumulatedConsumption', 'waterPressure'], 'string', 'max' => 100],
            [['meterID'], 'exist', 'skipOnError' => true, 'targetClass' => Meter::class, 'targetAttribute' => ['meterID' => 'id']],
            [['tecnicoID'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['tecnicoID' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tecnicoID' => 'Tecnico ID',
            'meterID' => 'Meter ID',
            'reading' => 'Reading',
            'accumulatedConsumption' => 'Accumulated Consumption',
            'date' => 'Date',
            'waterPressure' => 'Water Pressure',
        ];
    }

    /**
     * Gets query for [[Meter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeter()
    {
        return $this->hasOne(Meter::class, ['id' => 'meterID']);
    }

    /**
     * Gets query for [[Tecnico]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTecnico()
    {
        return $this->hasOne(User::class, ['id' => 'tecnicoID']);
    }

}
