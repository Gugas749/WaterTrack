<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Reset Password form
 */
class Resetpasswordform extends Model
{
    public $username;
    public $newPassword;
    public $newPasswordRepeat;
    public $oldPassword;

    private $_user;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'oldPassword', 'newPassword', 'newPasswordRepeat'], 'required'],
            ['oldPassword', 'validateOldPassword'],
            ['newPasswordRepeat', 'compare', 'compareAttribute' => 'newPassword',
                'message' => 'The passwords do not match.'
            ],
            ['newPassword', 'string', 'min' => 8],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateOldPassword($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->getUser();
        if (!$user || !$user->validatePassword($this->oldPassword)) {
            $this->addError($attribute, 'Incorrect old password.');
        }
    }


    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function resetPassword()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->getUser();
        if (!$user) {
            return false;
        }

        // set and hash new password
        $user->setPassword($this->newPassword);

        // regenerate auth key
        $user->generateAuthKey();

        return $user->save(false);
    }


    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
