<?php

namespace common\models;

/**
 * This is the model class for table "meter".
 *
 * @property int $id
 * @property string $address
 * @property int $userID
 * @property int $meterTypeID
 * @property int $enterpriseID
 * @property string $class
 * @property string $instalationDate
 * @property string|null $shutdownDate
 * @property float $maxCapacity
 * @property string $measureUnity
 * @property float $supportedTemperature
 * @property int $state
 *
 * @property Enterprise $enterprise
 * @property Metertype $meterType
 * @property Meterproblem[] $meterproblems
 * @property Meterreading[] $meterreadings
 * @property User $user
 */
class Meter extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['shutdownDate'], 'default', 'value' => null],
            [['address', 'userID', 'meterTypeID', 'enterpriseID', 'class', 'instalationDate', 'maxCapacity', 'measureUnity', 'supportedTemperature', 'state'], 'required'],
            [['userID', 'meterTypeID', 'enterpriseID', 'state'], 'integer'],
            [['instalationDate', 'shutdownDate'], 'safe'],
            [['maxCapacity', 'supportedTemperature'], 'number'],
            [['address'], 'string', 'max' => 100],
            [['class', 'measureUnity'], 'string', 'max' => 10],
            [['enterpriseID'], 'exist', 'skipOnError' => true, 'targetClass' => Enterprise::class, 'targetAttribute' => ['enterpriseID' => 'id']],
            [['meterTypeID'], 'exist', 'skipOnError' => true, 'targetClass' => Metertype::class, 'targetAttribute' => ['meterTypeID' => 'id']],
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
            'address' => 'Address',
            'userID' => 'User ID',
            'meterTypeID' => 'Meter Type ID',
            'enterpriseID' => 'Enterprise ID',
            'class' => 'Class',
            'instalationDate' => 'Instalation Date',
            'shutdownDate' => 'Shutdown Date',
            'maxCapacity' => 'Max Capacity',
            'measureUnity' => 'Measure Unity',
            'supportedTemperature' => 'Supported Temperature',
            'state' => 'State',
        ];
    }

    /**
     * Gets query for [[Enterprise]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEnterprise()
    {
        return $this->hasOne(Enterprise::class, ['id' => 'enterpriseID']);
    }

    /**
     * Gets query for [[Metertype]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeterType()
    {
        return $this->hasOne(Metertype::class, ['id' => 'meterTypeID']);
    }

    /**
     * Gets query for [[Meterproblems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeterproblems()
    {
        return $this->hasMany(Meterproblem::class, ['meterID' => 'id']);
    }

    /**
     * Gets query for [[Meterreadings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeterreadings()
    {
        return $this->hasMany(Meterreading::class, ['meterID' => 'id']);
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
