<?php

/**
 * For all payzen payment form
 * Class Payzen_Subscribers_Payment_Form
 */
class Payment_Form
{

    private $userName;
    private $password;
    private $passwordConfirm;
    private $email;
    private $emailConfirm;
    private $fistName;
    private $lastName;

    /**
     * Payzen_subscribers_payment_form constructor.
     * @param $userName
     * @param $password
     * @param $email
     */
    public function __construct()
    {
        if(is_user_logged_in()){
            $user = wp_get_current_user();
            $this->userName = $user->username;
            $this->password = $user->password;
            $this->email    = $user->email;
        }else {
            $this->userName         = $_POST['username'];
            $this->password         = $_POST['password'];
            $this->passwordConfirm  = $_POST['password_confirm'];
            $this->email            = $_POST['email'];
            $this->emailConfirm     = $_POST['email_confirm'];
        }
    }

    /**
     * @param $user
     * @return Payment_Form
     */
    public static function withUser($user): Payment_Form
    {
        return new self($user->username, $user->password, $user->email);
    }

    /**
     * @return bool
     */
    public function registration_validation(): bool
    {
        global $registrationErrors;

        if (empty($this->userName) || empty($this->password) || empty($this->email)) {
            $registrationErrors->add('field', __('Required form field is missing', 'payzen-subscribers'));
        }

        if (strlen($this->userName) < 4) {
            $registrationErrors->add('username_length', __('Username too short. At least 4 characters is required', 'payzen-subscribers'));
        }

        if (username_exists($this->userName))
            $registrationErrors->add('username', __('Sorry, that login already exists!', 'payzen-subscribers'));

        if (!validate_username($this->userName)) {
            $registrationErrors->add('username_invalid', __('Sorry, the username you entered is not valid', 'payzen-subscribers'));
        }

        if (strlen($this->password) < 5) {
            $registrationErrors->add('password', __('Password length must be greater than 5', 'payzen-subscribers'));
        }

        if ($this->passwordConfirm !== $this->password) {
            $registrationErrors->add('password_confirm', __('Password does not match', 'payzen-subscribers'));
        }

        if (!is_email($this->email)) {
            $registrationErrors->add('email_invalid', __('Email is not valid', 'payzen-subscribers'));
        }

        if (email_exists($this->email)) {
            $registrationErrors->add('email', __('Email Already in use', 'payzen-subscribers'));
        }

        if ($this->emailConfirm !== $this->email) {
            $registrationErrors->add('email_confirm', __('Emails do not match', 'payzen-subscribers'));
        }


        if ( $registrationErrors->has_errors()) {
            return false;
        }
        return true;

    }


    /**
     * Test if is this action
     * @param string $name
     * @return bool
     */
    public static function isActionForm(string $name): bool
    {
        return isset($_POST['paysubs_action']) && $_POST['paysubs_action'] === $name;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getFistName()
    {
        return $this->fistName;
    }

    /**
     * @param mixed $fistName
     */
    public function setFistName($fistName)
    {
        $this->fistName = $fistName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param mixed $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }


}