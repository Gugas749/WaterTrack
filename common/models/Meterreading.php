<?php

namespace common\models;

use common\mosquitto\phpMQTT;
use Yii;

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

    //----------------------------------------------------------------------------------------
    //                         MOSQUITTO
    //----------------------------------------------------------------------------------------
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $myObj = new \stdClass();
        $myObj->id = $this->id;
        $myObj->reading = $this->reading;
        $myObj->waterPressure = $this->waterPressure;
        $myObj->accumulatedConsumption = $this->accumulatedConsumption;

        $myJSON = json_encode($myObj);

        if ($insert) {
            $this->fazPublishNoMosquitto("METERREADING_INSERT", $myJSON);
        } else {
            $this->fazPublishNoMosquitto("METERREADING_UPDATE", $myJSON);
        }
    }
    public function afterDelete()
    {
        parent::afterDelete();

        $myObj = new \stdClass();
        $myObj->id = $this->id;

        $myJSON = json_encode($myObj);

        $this->fazPublishNoMosquitto("METERREADING_DELETE", $myJSON);
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
