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
     * @param int $id
     * @param int $userID
     * @param int $meterID
     * @param int|null $problemID
     * @param string $reading
     * @param string $accumulatedConsumption
     * @param string $date
     * @param string $waterPressure
     * @param string $desc
     * @param int $readingType
     * @param int|null $problemState
     * @param \common\models\Meter $meter
     * @param \common\models\Meterproblem $problem
     * @param \common\models\User $user
     */


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

            [['userID', 'meterID', 'reading', 'accumulatedConsumption', 'date',
                'waterPressure', 'desc', 'readingType'], 'required'],

            [['userID', 'meterID', 'problemID', 'readingType', 'problemState'], 'integer'],

            // normalize empty form input
            ['problemState', 'filter', 'filter' => function ($value) {
                return $value === '' ? null : $value;
            }],

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


    //--------------------MOSQUITTO--------------------
    /*public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        //Obter dados do registo em causa

        $myObj = new \stdClass();
        $myObj->id = $this->id;
        $myObj->userID =  $this->userID;
        $myObj->meterID = $this->meterID;
        $myObj->problemID = $this->problemID;
        $myObj->reading = $this->reading;
        $myObj->accumulatedConsumption = $this->accumulatedConsumption;
        $myObj->date = $this->date;
        $myObj->waterPressure = $this->waterPressure;
        $myObj->desc = $this->desc;
        $myObj->readingType = $this->readingType;
        $myObj->problemState = $this->problemState;
        $myJSON = json_encode($myObj);

        if($insert)
            $this->FazPublishNoMosquitto("INSERT",$myJSON);
        else
            $this->FazPublishNoMosquitto("UPDATE",$myJSON);
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $myObj=new \stdClass();
        $myObj->id = $this->id;
        $myJSON = json_encode($myObj);

        $this->FazPublishNoMosquitto("DELETE",$myJSON);
    }

    public function FazPublishNoMosquitto($canal, $msg)
    {
        $server = "127.0.0.1";
        $port = 1883;
        $username = ""; // set your username
        $password = ""; // set your password
        $client_id = "phpMQTT-publisher"; // unique!
        $mqtt = new \app\mosquitto\phpMQTT($server, $port, $client_id);
        if ($mqtt->connect(true, NULL, $username, $password)) {
            $mqtt->publish($canal, $msg, 0);
            $mqtt->close();
        }
        else {
            file_put_contents("debug.output","Time out!");
        }
    }*/
}
