<?php

namespace common\models;

use common\mosquitto\phpMQTT;
use Yii;

/**
 * This is the model class for table "metertype".
 *
 * @property int $id
 * @property string $description
 *
 * @property Meter[] $meter
 */
class Metertype extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'metertype';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'required'],
            [['description'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[Meters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeters()
    {
        return $this->hasMany(Meter::class, ['meterTypeID' => 'id']);
    }

    //----------------------------------------------------------------------------------------
    //                         MOSQUITTO
    //----------------------------------------------------------------------------------------
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $myObj = new \stdClass();
        $myObj->id = $this->id;
        $myObj->description = $this->description;

        $myJSON = json_encode($myObj);

        if ($insert) {
            $this->fazPublishNoMosquitto("METERTYPE_INSERT", $myJSON);
        } else {
            $this->fazPublishNoMosquitto("METERTYPE_UPDATE", $myJSON);
        }
    }
    public function afterDelete()
    {
        parent::afterDelete();

        $myObj = new \stdClass();
        $myObj->id = $this->id;

        $myJSON = json_encode($myObj);

        $this->fazPublishNoMosquitto("METERTYPE_DELETE", $myJSON);
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
