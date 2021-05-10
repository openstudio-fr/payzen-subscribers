<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'partials/payzen-subscribers-public-display.php';
require_once 'partials/payzen-subscribers-form-registration.php';

class Payzen_Subscribers_Widget_Subscribe_Form_Pub
{
    /**
     * Prepare before display widget
     */
    public static function submittedFormsActions() {
        /**
         * If submitted paysubs_payment form
         */
        if(Payment_Form::isActionForm('paysubs_payment_cb_step_2') || Payment_Form::isActionForm('paysubs_payment_sdd_step_2')){
            $paymentForm = new Payment_Form();
            if($paymentForm->registration_validation() || is_user_logged_in()){
                /**
                 * If isValidSubmittedForm is true
                 * insert and login user
                 * prepare to display payzen API form (payment/subscription)
                 * Add user to wp and Login user
                 * custom_registration_function() is here for login user, is not possible after
                 */
                if(!is_user_logged_in()) {
                    custom_registration_function();
                    isset($_POST['receive_newsletter']) ?
                        storeUserMeta('paysubs_receive_newsletter',$_POST['receive_newsletter']) :
                        storeUserMeta('paysubs_receive_newsletter',null);
                }

                /**
                 * Store specific plan option user
                 */
                if(isset($_POST['paysubs_plan'])){
                    storeUserMeta('paysubs_plan', $_POST['paysubs_plan']);
                }
                /**
                 * Store vads payzen uuid
                 */
                if(empty(getUserMeta('paysubs_vads_uuid')[0])){
                    storeUserMeta('paysubs_vads_uuid', createVadsUuid(wp_get_current_user()->ID));
                }

                /**
                 * Add scripts for display payzen API form
                 */
                if(Payment_Form::isActionForm('paysubs_payment_cb_step_2')){
                    add_action('wp_head', function () {
                        addScriptsPayzenForm();
                    });
                }
            }
        }elseif(Payzen_API::isPayzenApiResponse()){
            /**
             * Step 3 : display success or error response of payzen API
             * And get a subscription to Payzen API
             * SDD is here juste after payment
             */
            $krAnswer = json_decode(stripslashes($_POST['kr-answer']), true);
            $email = $krAnswer['customer']['email'];
            if(!is_user_logged_in()) {
                /**
                 * Step 3 : is response API IPN
                 */
                $wp_user = get_user_by('email',$email);
                /**
                 * Login user
                 */
                if ($wp_user) {
                    wp_set_current_user($wp_user->ID, $wp_user->user_login);
                }
            }
            if(callbackTokenIsPaid()){

                try {
                    storeUserTransaction();

                    /* if update methode payment => stop action here */
                    if(isValidPlan()){
                        throw new Exception(__('They are already plan for this user','payzen-subscribers'));
                    }

                    $response = Payzen_Subscribers_Widget_Subscribe_Form_Pub::getSubscription();
                    if ($response['status'] === 'SUCCESS') {
                        //is PAID so create invoice
                        $krAnswer = json_decode(stripslashes($_POST['kr-answer']), true);
                        plugin_log(['storeUserInvoices' =>
                            [
                                'user_email'    => wp_get_current_user()->user_email,
                                'userId'        => get_current_user_id(),
                                'orderDetails'  => $krAnswer['orderDetails'],
                                'Response'      => 'isPayzenApiResponse'
                            ]
                        ]);
                        storeUserInvoices($krAnswer['orderDetails']);

                        $subscriptionId = $response['answer']['subscriptionId'];
                        /** Store subscriptionId into wp user meta */
                        storeUserMeta('paysubs_subscriptionId', $subscriptionId);
                        storeUserMeta('paysubs_subscriptionDate', getDate8601());
                    }else{
                        plugin_log(['GetSubscription_not_success' =>
                            [
                                'user_email'    => wp_get_current_user()->user_email,
                                'user_id'       => get_current_user_id(),
                                'Response'  => $response
                            ]
                        ]);
                    }
                } catch (\Lyra\Exceptions\LyraException $e) {
                    if(isApiTest()){
                        echo 'LyraException : ' . $e->getMessage();
                    }
                    plugin_log(['POST_Hash_error' =>
                        [
                            'user_email'    => wp_get_current_user()->user_email,
                            'user_id'       => get_current_user_id(),
                            'POST'  => $_POST
                        ]
                    ]);
                } catch (Exception $e) { //Redirect
                    plugin_log(['PLAN_ERROR' =>
                        [
                            'user_email'    => wp_get_current_user()->user_email,
                            'user_id'       => get_current_user_id(),
                            'message'       => $e->getMessage()
                        ]
                    ]);
                    if(get_site_url().add_query_arg( NULL, NULL ) === get_site_url()."/registration/") {
                        /* if registration => redirect */
                        $options = get_option('paysubs_api_settings');
                        $monUrl = $options['paysubs_field_redirect_sdd'];
                        wp_redirect($monUrl);
                        exit();
                    }
                }
            }else{
                plugin_log(['isPayzenApiResponse' =>
                    [
                        'user_email'    => wp_get_current_user()->user_email,
                        'user_id'       => get_current_user_id(),
                        'Status'        => callbackTokenIs(),
                    ]
                ]);

                if(callbackTokenIs() === 'UNPAID'){
                    //TODO - remove payzen subscription plan
                    $subscriptionId = getUserMeta('paysubs_subscriptionId')[0];
                    $token = getUserMeta('paysubs_token')[0];
                    try {
                        $response = Payzen_Subscribers_Plan::cancelSubscriptionPlan($subscriptionId, $token);
                        if ($response['status'] === "SUCCESS"){
                            removeUserPlan(true);
                            //TODO send email
                        } else {
                            plugin_log(['cancelSubscriptionPlan' =>
                                [
                                    'user_email'    => wp_get_current_user()->user_email,
                                    'user_id'       => get_current_user_id(),
                                    'status'        => $response['status']
                                ]
                            ]);
                        }
                    } catch (\Lyra\Exceptions\LyraException $e) {
                        plugin_log(['Lyra_exception' =>
                            [
                                'user_email'    => wp_get_current_user()->user_email,
                                'user_id'       => get_current_user_id(),
                                'message'       => $e->getMessage()
                            ]
                        ]);
                    }

                }
            }
        }elseif (Payzen_API::isIpnSddResponse()){
            /**
             * Step recurrence IPN response
             */
            /* Log ipn status response */
            plugin_log(['IPN_response' =>
                [
                    'user_email'        => $_POST['vads_cust_email'],
                    'vads_trans_status' => $_POST['vads_trans_status'],
                    'uuid'              => $_POST['vads_trans_uuid']
                ]
            ]);

            try {
                $options = get_option('paysubs_api_settings');
                if(isApiTest()){
                    $formApiKey = $options['paysubs_field_test_api_key'];
                }else{
                    $formApiKey = $options['paysubs_field_api_key'];
                }
                if ($_POST['signature'] == getSignature(postVadsParams(),$formApiKey, '256')){
                    $wp_user = get_user_by('email',$_POST['vads_cust_email']);
                    if ($wp_user) {
                        wp_set_current_user($wp_user->ID, $wp_user->user_login);
                    }
                    if( $_POST['vads_trans_status'] === 'CANCELLED' ||
                        $_POST['vads_trans_status'] === 'ABANDONED' ||
                        $_POST['vads_trans_status'] === 'REFUSED')
                    { //Remove payment
                        //Remove payzen subscription
                        if(!empty($_POST['vads_subscription'])){
                            $token = $_POST['vads_identifier'];
                            $subscriptionId = $_POST['vads_subscription'];
                            $response = Payzen_Subscribers_Plan::cancelSubscriptionPlan($subscriptionId, $token);
                            plugin_log(['IPN_REMOVE_PAYZEN_ACCOUNT' =>
                                [
                                    'user_id'   => get_current_user_id(),
                                    'response_status'   => $response['status'],
                                    'alias'     => $token,
                                    'reference' => $subscriptionId
                                ]
                            ]);
                            //Remove WP plan if is same payzen subscription
                            if( $_POST['vads_subscription'] == getUserMeta('paysubs_subscriptionId')[0] &&
                                $_POST['vads_identifier'] == getUserMeta('paysubs_token')[0])
                            {
                                removeUserPlan(true);
                                plugin_log(['IPN_REMOVE_WP_ACCOUNT' =>
                                    [
                                        'user_id'           => get_current_user_id(),
                                        'user_email'        => $_POST['vads_cust_email'],
                                        'POST'              => $_POST
                                    ]
                                ]);
                            }
                        }else{
                            removeUserPlan(true);
                            plugin_log(['IPN_'.$_POST['vads_trans_status'] =>
                                [
                                    'user_id'           => get_current_user_id(),
                                    'user_email'        => $_POST['vads_cust_email'],
                                    'vads_subscription' => 'IS_EMPTY',
                                    'POST'              => $_POST
                                ]
                            ]);
                        }
                    } elseif ($_POST['vads_trans_status'] === 'AUTHORISED') {
                        storeUserTransaction($_POST['vads_trans_uuid']);
                        $response = Payzen_Subscribers_Widget_Subscribe_Form_Pub::getSubscription();
                        if ($response['status'] === 'SUCCESS') {
                            //is PAID so create invoice
                            $transaction = Payzen_API::getTransaction($_POST['vads_trans_uuid']);
                            $krAnswer = $transaction['answer'];
                            plugin_log(['storeUserInvoices_IPN' =>
                                [
                                    'userId'        => get_current_user_id(),
                                    'orderDetails'  => $krAnswer['orderDetails'],
                                    'Response'      => 'isIpnSddResponse'
                                ]
                            ]);
                            storeUserInvoices($krAnswer['orderDetails']);
                            $subscriptionId = $response['answer']['subscriptionId'];
                            /** Store subscriptionId into wp user meta */
                            storeUserMeta('paysubs_subscriptionId', $subscriptionId);
                            storeUserMeta('paysubs_subscriptionDate', getDate8601());
                        }else{
                            plugin_log(['ERROR_STATUS_IPN' =>
                                [
                                    'userId'        => get_current_user_id(),
                                    'Response'      => 'isIpnResponse',
                                    'ERROR_STATUS'  => $_POST['vads_trans_status']
                                ]
                            ]);
                        }
                    }

                }else{
                    throw new Exception('An error occurred while computing the signature');
                }
            } catch (\Lyra\Exceptions\LyraException $e) {
                if(isApiTest()){
                    echo $e->getMessage();
                }
            } catch (Exception $e) {
                if(isApiTest()){
                    echo $e->getMessage();
                }
                plugin_log(['IPN_ERROR_SIGNATURE' =>
                    [
                        'user_email'        => $_POST['vads_cust_email'],
                        'message'           => $e->getMessage(),
                        'POST'              => $_POST
                    ]
                ]);
            }
        }
    }

    /**
     * Display Widget subscribe form
     * Treatment form submissions
     * @param $args
     * @param $plans
     * @throws Exception
     */
    public function subscribeFormActions($args, $plans){
        $isDisplayed = false;

        if(Payment_Form::isActionForm('paysubs_payment_sdd_step_2')){
            if(is_user_logged_in()) {
                /**
                 * Step 2 : display payzen payment form SDD
                 */
                redirectionPayzenApiSdd();
                $isDisplayed = true;
            }

        }elseif(Payment_Form::isActionForm('paysubs_payment_cb_step_2')){
            //Dont forget to look for treatment => Payzen_Subscribers_widgets::submittedFormsActions()
            if(is_user_logged_in()) {
                /**
                 * Step 2 : display payzen payment form CB
                 */
                displayPaymentForm();
                $isDisplayed = true;
            }else{

            }
        }/*elseif(Payzen_API::isPayzenApiResponse()) {}*/

        if(!$isDisplayed){
            if(!isValidPlan()){
                /**
                 * Step 1 : display init Form with registration
                 */
                getFirstStepPaymentForm($plans);
            }else{
                $this->template(isAllReadyActivePlan());
            }
        }
    }

    /**
     * @return array
     * @throws \Lyra\Exceptions\LyraException
     */
    public static function getSubscription(): array
    {
        $plan = new Payzen_Subscribers_Plan(getUserMeta('paysubs_plan')[0]);
        return $plan->createSubscriptionPlan(
            "EUR",
            "MyOrderId",
            1
        );
    }

    private function template($display){
        echo
            $display;
    }

}
