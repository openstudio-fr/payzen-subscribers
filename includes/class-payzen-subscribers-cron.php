<?php


class Payzen_Cron
{

    /**
     * Payzen_Cron constructor.
     */
    public function __construct()
    {
    }


    /**
     * Cron jobs
     * @param $schedules
     * @return mixed
     */
    function custom_cron_schedules($schedules) {
        if (!isset($schedules['1minute'])) {
            $schedules['1minute'] = array(
                'interval' => 60,
                'display' => __('Once every 1 minute','payzen-subscribers'));
        }
        if (!isset($schedules['10minutes'])) {
            $schedules['10minutes'] = array(
                'interval' => 10*60,
                'display' => __('Once every 10 minutes','payzen-subscribers'));
        }
        if (!isset($schedules['halfhour'])) {
            $schedules['halfhour'] = array(
                'interval' => 30*60,
                'display' => __('Once every 30 minutes','payzen-subscribers'));
        }
        if (!isset($schedules['1day'])) {
            $schedules['1day'] = array(
                'interval' => 60*60*24,
                'display' => __('Once every day','payzen-subscribers'));
        }
        return $schedules;
    }

    /**
     * Send email if is time to renew payment methode
     */
    public static function do_cron_action() {
        $mail = new Send_Mails();
        $wpUsers = get_users( array( 'role__in' => array( 'author', 'site_subscriber' ) ) );

        foreach ($wpUsers as $wpUser){
            wp_set_current_user($wpUser->ID);
            $planId = getUserMeta('paysubs_plan')[0];
            $plan = new Payzen_Subscribers_Plan($planId);
            if(isTimeToRenewPaymentMethode($plan)){
                $mail->generate_mail($plan);
                $mail->send_mail();
            }

        }
    }

}