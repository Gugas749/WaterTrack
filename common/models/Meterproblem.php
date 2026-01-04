<?php

namespace common\models;

/**
 * This is the model class for table "meterproblem".
 *
 * @property int $id
 * @property int $meterID
 * @property int $userID
 * @property int|null $tecnicoID
 * @property int $problemState
 * @property string $description
 *
 * @property Meter $meter
 * @property User $tecnico
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
            [['tecnicoID'], 'default', 'value' => null],
            [['meterID', 'userID', 'problemState', 'description'], 'required'],
            [['meterID', 'userID', 'tecnicoID', 'problemState'], 'integer'],
            [['description'], 'string', 'max' => 100],
            [['meterID'], 'exist', 'skipOnError' => true, 'targetClass' => Meter::class, 'targetAttribute' => ['meterID' => 'id']],
            [['tecnicoID'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['tecnicoID' => 'id']],
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
            'tecnicoID' => 'Tecnico ID',
            'problemState' => 'Problem State',
            'description' => 'Description',
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
