<?php

require_once 'partials/payzen-subscribers-account.php';

class Payzen_Subscribers_Widget_Account_Pub
{
    /**
     * Prepare before display widget
     * No header send
     */
    public static function submittedFormsActions() {
        if(Account_Form::isActionForm('paysubs_login')){
            Account_Form::loginUserValidation($_POST['login'], $_POST['password']);

        }elseif(Account_Form::isActionForm('paysubs_logout')){
            wp_logout();

        }elseif (Account_Form::isActionForm('paysubs_remove_wp_account')){
            require_once( ABSPATH.'wp-admin/includes/user.php' );
            $success = wp_delete_user( wp_get_current_user()->ID );
            if($success){
                wp_logout();
            }

        }elseif(Account_Form::isActionForm('paysubs_get_invoice')) {
            echo prepareInvoiceByDate($_REQUEST['invoice_date']);

        }elseif(Account_Form::isActionForm('paysubs_change_payment')){
            add_action('wp_head', function () {
                addScriptsPayzenForm();
            });
        }

    }

    /**
     * All actions form
     */
    public function accountFormActions(){
        $isDisplayed = false;
        if(is_user_logged_in()){
            if(Account_Form::isActionForm('paysubs_change_payment')){
                $isDisplayed = true;
                $this->displayUpdateToken();
            }elseif(Account_Form::isActionForm('paysubs_display_invoices')) {
                $isDisplayed = true;
                $this->template(
                    $this->getLinkInvoices() .
                           $this->goBack()
                );

            }elseif(Account_Form::isActionForm('paysubs_close_account')){
                $subscriptionId = getUserMeta('paysubs_subscriptionId')[0];
                $token = getUserMeta('paysubs_token')[0];
                $response = Payzen_Subscribers_Plan::cancelSubscriptionPlan($subscriptionId, $token);
                if($response['status'] === "SUCCESS"){
                    removeUserPlan(true);
                }else{
                    global $informationAccountErrors;
                    $informationAccountErrors->add('apiErrorMessage', $response['answer']['errorMessage']);
                }
            }elseif(Payzen_API::isPayzenApiResponse()){
                /**
                 * Step 3 : display success or error response of payzen API
                 */
                if(callbackTokenIsPaid()){
                    try {
                        storeUserTransaction();
                    } catch (\Lyra\Exceptions\LyraException $e) {
                    }
                }
            }
        }

        if(!$isDisplayed) {
            if(is_user_logged_in()){
                $this->template($this->displayFirstStepHtml());
            }else{
                $this->template($this->displayLogin());
            }

        }
    }

    public function displayLogin(): string
    {
        global $loginErrors;
        return '
        <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
            <label class="form-field">'. __('Username / email', 'payzen-subscribers') .'</label>
            <input type="text" name="login" value="'.(isset($_POST['login']) ? $_POST['login'] : '') . '">
            '.loginErrorAccount($loginErrors, 'login').'
            <br>
            <label class="form-field">'. __('Password', 'payzen-subscribers') .'</label>
            <input type="password" name="password" value="'.(isset($_POST['password']) ? $_POST['password'] : '') . '">
            '.loginErrorAccount($loginErrors, 'password').'
            <br>
            <input type="hidden" name="paysubs_action" value="paysubs_login">
            <button>' . __('Log in','payzen-subscribers') . '</button>
        </form>
        ';
    }

    public function displayFirstStepHtml(): string
    {
        return
            Payzen_Subscribers_Widget_Account_Pub::displayMethodPayment() .
            $this->getActionsPlan() . '
            <div class="logout">
                ' . $this->logout() . '
            </div>';
    }

    public function getActionsPlan(): string
    {
        if(isValidPlan()) {
            return '
                <div class="change_payment">
            ' . $this->changePaymentForm() . '
                </div>
                <div class="close_account">
            ' . $this->closeAccount() . '
                </div>
                <div class="get_invoices">
            ' . $this->goToLinkInvoices() . '
                </div>';
        }else{
            if(getUserMeta('paysubs_token')[0] !== '') {
                return '
                     <div class="change_payment">
                ' . $this->changePaymentForm() . '
                    </div>
                    <div class="remove_wp_account">
                ' . $this->removeWpAccount() . '
                    </div>
                ';
            }
            else{
                return '
                    <div class="remove_wp_account">
                        ' . $this->removeWpAccount() . '
                    </div>
                ';
            }
        }
    }

    public function displayUpdateToken(){
        $plan = new Payzen_Subscribers_Plan(getUserMeta('paysubs_plan')[0]);
        $token = getUserMeta('paysubs_token')[0];
        $responseUpdateToken = $plan->updateTokenPlan($token, 'EUR', 'MyOrderId', wp_get_current_user()->get('user_email'));
        try {
            $this->template(
                $this->changePaymentStep2($responseUpdateToken) .
                $this->goBack()
            );
        } catch (Exception $e) {

        }
    }

    /**
     * @param $response
     * @return string
     * @throws Exception
     */
    public function changePaymentStep2($response): string
    {
        return '
            ' . displayApiForm($response) . '
            ';
    }

    /**
     * @param bool $back
     * @return string
     */
    public static function displayMethodPayment(): string
    {
        global $informationAccountErrors;
        $cardBrand = getUserMeta('paysubs_card_effectiveBrand')[0];
        $cardPan = getUserMeta('paysubs_card_pan')[0];
        $planId = getUserMeta('paysubs_plan')[0];
        $subscribeDate = date('d/m/Y', strtotime(getUserMeta('paysubs_subscriptionDate')[0]));
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
                    '. loginErrorAccount($informationAccountErrors, 'subscriberId') . '
                    '. __('Subscription plan','payzen-subscribers') . ' : ' . $plan->getTitle() . '<br>
                    '. __('Subscription date','payzen-subscribers') . ' : ' . $subscribeDate . '<br>
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

    /**
     * @return string
     */
    private function changePaymentForm(): string
    {
        return '
            <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
                <input type="hidden" name="paysubs_action" value="paysubs_change_payment">
                <button>' . __('Update my bank card','payzen-subscribers') . '</button>
            </form>
            
        ';
    }

    public function logout(): string
    {
        return '
            <form action="'. get_home_url().'" method="post">
                <input type="hidden" name="paysubs_action" value="paysubs_logout">
                <button>' . __('Logout','payzen-subscribers') . '</button>
            </form>
            ';
    }

    public function goToLinkInvoices(): string
    {
        return '
            <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
                <input type="hidden" name="paysubs_action" value="paysubs_display_invoices">
                <button>' . __('Get invoices','payzen-subscribers') . '</button>
            </form>
            ';
    }

    public function getLinkInvoices(): string
    {
        $links = '';
        foreach (getUserInvoices() as $key => $invoice){
            $separator = strpos($_SERVER['REQUEST_URI'], '?') > 0 ? '&' : '?';
            $links .= '<a target="_blank" href="'.$_SERVER['REQUEST_URI'] . $separator .'paysubs_action=paysubs_get_invoice&invoice_date='.$key.'">' . __('Invoice','payzen-subscribers') . '-' . date('Y-m-d', strtotime($key)).'</a><br>';
        }
        return $links;
    }

    /**
     * Close PayZen subscription
     * @return string
     */
    private function closeAccount(): string
    {
        return
            '
            <form action="'. get_home_url() . '" method="post">
                <input type="hidden" name="paysubs_action" value="paysubs_close_account">
                <button>' . __('Close my account','payzen-subscribers') . '</button>
            </form>
            ';
    }

    /**
     * @return string
     */
    private function removeWpAccount(): string
    {
        return '
            <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
                <input type="hidden" name="paysubs_action" value="paysubs_remove_wp_account">
                <button>' . __('Remove my account','payzen-subscribers') . '</button>
            </form>
        ';
    }

    /**
     * @return string
     */
    private function header(): string
    {
        return '
            <li class="widget widget_links widget-group layout-2">
                <h2>' . __('Payzen account', 'payzen-subscribers') . '</h2>
                <div class="widget_user_account">
        ';
    }

    /**
     * @return string
     */
    private function footer(): string
    {
        return '
                </div>
            </li>';
    }

    /**
     * Template
     * @param $display
     */
    private function template($display)
    {
        echo
            $this->header() .
            $display .
            $this->footer();
    }

    private function goBack(): string
    {
        return '<a href="javascript:window.history.go(-1)">[ '. __('Go back','payzen-subscribers') .' ]</a>';
    }

}
