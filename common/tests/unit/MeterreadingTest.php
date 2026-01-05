<?php

namespace common\tests\unit;

use common\models\Meterreading;
use common\models\Meter;
use common\models\User;

class MeterreadingTest extends \Codeception\Test\Unit
{
    /**
     * Teste: falha quando os campos obrigatórios não são preenchidos
     */
    public function testMeterreadingValidationFail()
    {
        $model = new Meterreading();

        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('tecnicoID', $model->errors);
        $this->assertArrayHasKey('meterID', $model->errors);
        $this->assertArrayHasKey('reading', $model->errors);
        $this->assertArrayHasKey('accumulatedConsumption', $model->errors);
        $this->assertArrayHasKey('date', $model->errors);
        $this->assertArrayHasKey('waterPressure', $model->errors);
    }

    /**
     * Teste: criação de um meterreading válido
     */


    /**
     * Teste: tipos inválidos (tecnicoID e meterID não inteiros)
     */
    public function testInvalidIntegerFields()
    {
        $model = new Meterreading([
            'tecnicoID' => 'abc',
            'meterID' => 'xyz',
        ]);

        $this->assertFalse($model->validate(['tecnicoID', 'meterID']));
        $this->assertArrayHasKey('tecnicoID', $model->errors);
        $this->assertArrayHasKey('meterID', $model->errors);
    }

    /**
     * Teste: relação com Meter
     */
    public function testRelationWithMeter()
    {
        $model = new Meterreading();
        $this->assertInstanceOf(\yii\db\ActiveQuery::class, $model->getMeter());
    }

    /**
     * Teste: relação com Tecnico (User)
     */
    public function testRelationWithTecnico()
    {
        $model = new Meterreading();
        $this->assertInstanceOf(\yii\db\ActiveQuery::class, $model->getTecnico());
    }
}
