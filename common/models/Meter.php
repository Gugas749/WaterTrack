<?php

namespace common\models;

use common\mosquitto\phpMQTT;
use Yii;

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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $myObj = new \stdClass();
        $myObj->id = $this->id;
        $myObj->address = $this->address;
        $myObj->userID = $this->userID;
        $myObj->meterTypeID = $this->meterTypeID;
        $myObj->enterpriseID = $this->enterpriseID;
        $myObj->class = $this->class;
        $myObj->instalationDate = $this->instalationDate;
        $myObj->shutdownDate = $this->shutdownDate;
        $myObj->maxCapacity = $this->maxCapacity;
        $myObj->measureUnity = $this->measureUnity;
        $myObj->supportedTemperature = $this->supportedTemperature;
        $myObj->state = $this->state;

        $myJSON = json_encode($myObj);

        if ($insert) {
            $this->fazPublishNoMosquitto("METER_INSERT", $myJSON);
        } else {
            $this->fazPublishNoMosquitto("METER_UPDATE", $myJSON);
        }
    }
    public function afterDelete()
    {
        parent::afterDelete();

        $myObj = new \stdClass();
        $myObj->id = $this->id;

        $myJSON = json_encode($myObj);

        $this->fazPublishNoMosquitto("METER_DELETE", $myJSON);
    }
    public function fazPublishNoMosquitto($canal, $msg)
    {
        $server = "127.0.0.1";
        $port = 1883;
        $client_id = "yii2-user-" . uniqid();

        $mqtt = new phpMQTT($server, $port, $client_id);

        if ($mqtt->connect()) {
            $mqtt->publish($canal, $msg, 0);
            $mqtt->close();
        } else {
            file_put_contents(
                Yii::getAlias('@runtime') . '/mqtt_error.log',
                "Erro MQTT\n",
                FILE_APPEND
            );
        }
    }
}
