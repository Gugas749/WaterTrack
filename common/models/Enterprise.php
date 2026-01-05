<?php

namespace common\models;

use app\models\Meter;
use common\mosquitto\phpMQTT;
use Yii;

/**
 * This is the model class for table "enterprise".
 *
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string|null $contactNumber
 * @property string|null $contactEmail
 * @property string|null $website
 *
 * @property Meter[] $meter
 * @property Technicianinfo[] $technicianinfos
 */
class Enterprise extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'enterprise';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contactNumber', 'contactEmail', 'website'], 'default', 'value' => null],
            [['name', 'address'], 'required'],
            [['name', 'address', 'contactNumber', 'contactEmail', 'website'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'address' => 'Address',
            'contactNumber' => 'Contact Number',
            'contactEmail' => 'Contact Email',
            'website' => 'Website',
        ];
    }

    /**
     * Gets query for [[Meters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeters()
    {
        return $this->hasMany(Meter::class, ['enterpriseID' => 'id']);
    }

    /**
     * Gets query for [[Technicianinfos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTechnicianinfos()
    {
        return $this->hasMany(Technicianinfo::class, ['enterpriseID' => 'id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $myObj = new \stdClass();
        $myObj->id = $this->id;
        $myObj->name = $this->name;
        $myObj->address = $this->address;
        $myObj->contactNumber = $this->contactNumber;
        $myObj->contactEmail = $this->contactEmail;
        $myObj->website = $this->website;

        $myJSON = json_encode($myObj);

        if ($insert) {
            $this->fazPublishNoMosquitto("ENTERPRISE_INSERT", $myJSON);
        } else {
            $this->fazPublishNoMosquitto("ENTERPRISE_UPDATE", $myJSON);
        }
    }
    public function afterDelete()
    {
        parent::afterDelete();

        $myObj = new \stdClass();
        $myObj->id = $this->id;

        $myJSON = json_encode($myObj);

        $this->fazPublishNoMosquitto("ENTERPRISE_DELETE", $myJSON);
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
