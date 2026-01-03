<?php

namespace common\models;



use common\models\Meter;
use common\models\Meterproblem;
use common\models\User;

/**
 * This is the model class for table "meterreading".
 *
 * @property int $id
 * @property int $userID
 * @property int $meterID
 * @property int|null $problemID
 * @property string $reading
 * @property string $accumulatedConsumption
 * @property string $date
 * @property string $waterPressure
 * @property string $desc
 * @property int $readingType
 * @property int $problemState
 *
 * @property Meter $meter
 * @property Meterproblem $problem
 * @property User $user
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
            [['problemID'], 'default', 'value' => null],
            [['userID', 'meterID', 'reading', 'accumulatedConsumption', 'date', 'waterPressure', 'desc', 'readingType', 'problemState'], 'required'],
            [['userID', 'meterID', 'problemID', 'readingType', 'problemState'], 'integer'],
            [['date'], 'safe'],
            [['reading', 'accumulatedConsumption', 'waterPressure', 'desc'], 'string', 'max' => 100],
            [['meterID'], 'exist', 'skipOnError' => true, 'targetClass' => Meter::class, 'targetAttribute' => ['meterID' => 'id']],
            [['problemID'], 'exist', 'skipOnError' => true, 'targetClass' => Meterproblem::class, 'targetAttribute' => ['problemID' => 'id']],
            [['userID'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['userID' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'userID' => 'User ID',
            'meterID' => 'Meter ID',
            'problemID' => 'Problem ID',
            'reading' => 'Reading',
            'accumulatedConsumption' => 'Accumulated Consumption',
            'date' => 'Date',
            'waterPressure' => 'Water Pressure',
            'desc' => 'Desc',
            'readingType' => 'Reading Type',
            'problemState' => 'Problem State',
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
     * Gets query for [[Problem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProblem()
    {
        return $this->hasOne(Meterproblem::class, ['id' => 'problemID']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'userID']);
    }

}
