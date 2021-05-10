<?php


class User_Settings
{

    /**
     * User_Settings constructor.
     */
    public function __construct()
    {
    }

    /**
     * Display new settings of user in wordpress users area
     * @param $user
     */
    public function show_extra_profile_fields($user) {
        $wpCurrentUser = wp_get_current_user();
        wp_set_current_user($user->ID, $user->user_login);
        echo $this->YourInformations();
        echo Payzen_Subscribers_Widget_Account::editMethodPayment();
        wp_set_current_user($wpCurrentUser->ID, $wpCurrentUser->user_login);
    }

    /**
     * Display new settings of user in wordpress users area
     * @param $user
     */
    public function edit_extra_profile_fields($user) {
        $wpCurrentUser = wp_get_current_user();
        wp_set_current_user($user->ID, $user->user_login);
        echo $this->YourInformations();
        echo Payzen_Subscribers_Widget_Account::editMethodPayment();
        wp_set_current_user($wpCurrentUser->ID, $wpCurrentUser->user_login);
    }

    public function YourInformations(): string
    {
        return '
            <div class="informations_user">
                <h3>'. __('Your informations','payzen-subscribers') .'</h3><br>
                ' . (getUserMeta('paysubs_receive_newsletter')[0] !== null ? __('Receive newsletter','payzen-subscribers') : '') . '
            </div>
        ';
    }

}