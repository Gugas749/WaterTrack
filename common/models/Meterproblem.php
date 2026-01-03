<?php

namespace common\models;

use common\models\User;

/**
 * This is the model class for table "meterproblem".
 *
 * @property int $id
 * @property int $meterID
 * @property int $userID
 * @property string $problemType
 * @property string $desc
 *
 * @property Meter $meter
 * @property User $user
 */
class Meterproblem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meterproblem';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meterID', 'userID', 'problemType', 'desc'], 'required'],
            [['meterID', 'userID'], 'integer'],
            [['problemType', 'desc'], 'string', 'max' => 100],
            [['meterID'], 'exist', 'skipOnError' => true, 'targetClass' => Meter::class, 'targetAttribute' => ['meterID' => 'id']],
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
            'meterID' => 'Meter ID',
            'userID' => 'User ID',
            'problemType' => 'Problem Type',
            'desc' => 'Desc',
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
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'userID']);
    }

}
