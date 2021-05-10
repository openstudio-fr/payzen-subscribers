<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Payzen_Subscribers_Widget_Account extends WP_Widget {

    /**
     * Formulaire public du widget basic
     * @var type Payzen_Subscribers_Widget_Subscribe_Form_Pub
     */
    protected $widgetAccount;

    public function __construct() {
        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        parent::__construct('payzen_subscribers_account', 'Payzen account', array('description' => __('Payzen account','payzen-subscribers')));
        /**
         * Load widget public display
         */
        $this->widgetAccount = new Payzen_Subscribers_Widget_Account_Pub();
    }

    /**
     * Display widget admin form
     * @param $instance
     * @return string
     */
    public function form($instance)
    {
        ?>
            <p><?php _e('To display widget by schortcode', 'payzen-subscribers') ?> [paysubs_account /]</p>
        <?php
    }

    /**
     * Display frontend form
     * public/class-payzen-subscribers-widget-basic.php
     * @param $args
     * @param $instance
     * @throws Exception
     */
    public function widget($args, $instance) {
        /**
         * short code : [paysubs_account /]
         *
         */
        $this->widgetAccount->accountFormActions();

    }

    /**
     *
     */
    public static function editMethodPayment(): string
    {
        global $informationAccountErrors;
        $cardBrand = getUserMeta('paysubs_card_effectiveBrand')[0];
        $cardPan = getUserMeta('paysubs_card_pan')[0];
        $planId = getUserMeta('paysubs_plan')[0];
        try {
            $plan = new Payzen_Subscribers_Plan($planId);
            return '
            <div class="informations_plan">
                <label for="title1">'. __('Your informations plan','payzen-subscribers') .'</label>
                <input type="checkbox" id="title1" />
                <div class="content">
                    '. (isValidPlan() ? __('Is valid plan','payzen-subscribers') .'<br>' : '<span class="error-message">'.__('Is not valid plan','payzen-subscribers') . '</span><br>' ) . '
                    '. loginErrorAccount($informationAccountErrors, 'apiErrorMessage') . '
                    '. loginErrorAccount($informationAccountErrors, 'cardExpired') . '
                    '. __('Subscription plan','payzen-subscribers') . ' : ' . $plan->getTitle() . '<br>
                    '. __('Subscription date','payzen-subscribers') . ' : ' . getUserMeta('paysubs_subscriptionDate')[0] . '<br>
                    '. __('Payzen subscription ID','payzen-subscribers') . ' : ' . getUserMeta('paysubs_subscriptionId')[0] . '<br>
                    '. __('Token ID','payzen-subscribers') . ' : ' . getUserMeta('paysubs_token')[0] . '<br>
                    '. __('Card brand','payzen-subscribers') . ' : ' . $cardBrand . '<br>
                    '. ($cardPan != '' ? __('Card number','payzen-subscribers') . ' : ' . $cardPan . '<br>' : '') . '
                    '. __('Card expiration month','payzen-subscribers') . ' : ' . getUserMeta('paysubs_card_expiryMonth')[0] . '<br>
                    '. __('Card expiration year','payzen-subscribers') . ' : ' . getUserMeta('paysubs_card_expiryYear')[0] . '
                </div>
            </div>
        ';
        } catch (Exception $e) {
            return '
            <div class="informations_plan">
                <label for="title1">'. __('Your informations plan','payzen-subscribers') .'</label>
                <input type="checkbox" id="title1" />
                <div class="content">
                    '. (isValidPlan() ? __('Is valid plan','payzen-subscribers') .'<br>' : '<span class="error-message">'.__('Is not valid plan','payzen-subscribers') . '</span>' ) . '
                </div>
            </div>
            ';
        }
    }

}
