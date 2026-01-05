<?php
return [
    [
        'id' => 1,
        'username' => 'admin',
        //'auth_key' => 'testkey',
        'password_hash' => Yii::$app->security->generatePasswordHash('12345678'),
        //'email' => 'admin@test.com',
        'status' => 10,

    ],
];