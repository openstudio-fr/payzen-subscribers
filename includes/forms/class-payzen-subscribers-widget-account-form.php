<?php


class Account_Form
{
    /**
     * Account_Form constructor.
     */
    public function __construct()
    {

    }

    /**
     * Test if is this action
     * @param string $name
     * @return bool
     */
    public static function isActionForm(string $name): bool
    {
        return isset($_REQUEST['paysubs_action']) && $_REQUEST['paysubs_action'] === $name;
    }

    public static function loginUserValidation($login, $password)
    {
        global $loginErrors;
        $wp_user = get_user_by('login', $login);
        //if false try with email
        if(!$wp_user){
            $wp_user = get_user_by('email', $login);
        }

        if(!$wp_user){
            $loginErrors->add('login', __('No user match','payzen-subscribers'));
        } elseif ( !wp_check_password( $password, $wp_user->data->user_pass, $wp_user->ID ) ) {
            $loginErrors->add('password', __('Password does not match','payzen-subscribers'));
        }

        if(!$loginErrors->has_errors()){
            /**
             * Login user
             */
            if ($wp_user) {
                wp_set_current_user($wp_user->ID, $wp_user->user_login);
                wp_set_auth_cookie( $wp_user->ID );
                do_action('wp_login', $wp_user->user_login, $wp_user);
            }
        }

    }

}