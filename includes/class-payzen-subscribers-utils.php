<?php

/**
 * Utilities
 */

use Lyra\Client as Client;


/**
 * Author: Agbonghama Collins - BERARD Adrien
 * Author URI: http://tech4sky.com
 */
function custom_registration_function()
{
    // sanitize user form input

    $user['username']   = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
    $user['password']   = isset($_POST['password']) ? esc_attr($_POST['password']) : '';
    $user['email']      = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $user['website']    = isset($_POST['website']) ? esc_url($_POST['website']) : '';
    $user['first_name'] = isset($_POST['fname']) ? sanitize_text_field($_POST['fname']) : '';
    $user['last_name']  = isset($_POST['lname']) ? sanitize_text_field($_POST['lname']) : '';
    $user['nickname']   = isset($_POST['nickname']) ? sanitize_text_field($_POST['nickname']) : '';
    $user['bio']        = isset($_POST['bio']) ? esc_textarea($_POST['bio']) : '';

    // call @function complete_registration to create the user
    // only when no WP_error is found
    complete_registration($user);

}

/**
 * Create wp user and
 * Login this new user
 * @param $user
 */
function complete_registration($user)
{
    if (!is_user_logged_in()) {
        $userdata = array(
            'user_login' => $user['username'],
            'user_email' => $user['email'],
            'user_pass' => $user['password'],
            'user_url' => $user['website'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'nickname' => $user['nickname'],
            'description' => $user['bio'],
            'role' => 'site_subscriber' //TODO get role by global params
        );
        /**
         * Add wp user
         */
        $wpUserId = wp_insert_user($userdata);
        $wp_user = get_user_by('id', $wpUserId);

        /**
         * Login user
         */
        if ($wp_user) {
            wp_set_current_user($wpUserId, $wp_user->user_login);
            wp_set_auth_cookie( $wpUserId );
            do_action('wp_login', $wp_user->user_login, $wp_user);
        }
    }
}

/**
 * is Payzen API test
 */
function isApiTest(): bool
{
    $options = get_option('paysubs_api_settings');
    return $options['paysubs_field_mode'] === 'TEST';
}

/**
 * Is possible to add x minutes
 * @param string $moreTime  '+ x'
 * @return string
 */
function getDate8601($moreTime = '+0 min'): string
{
    return current_datetime()->modify( $moreTime )->format( 'c' );
}

function getStartSubscriptionPlan($frequency){
    return getDate8601(getDelay($frequency));
}

/**
 * @param $frequency
 * @return string
 */
function getDelay($frequency): string
{
    switch ($frequency){
        case 'YEARLY' :
            $delay = '+1 year';
            break;
        case 'MONTHLY' :
            $delay = '+1 month';
            break;
        case 'WEEKLY' :
            $delay = '+7 days';
            break;
        default :
            $delay = '+0 min';
    }
    return $delay;
}

/**
 * Is possible to add x minutes
 * @return string
 */
function getDateVadsNow(): string
{
    return gmdate('YmdHis');;
}

/**
 * Store options in wp_user
 * @param $metaKey
 * @param $newValue
 * @return bool|int
 */
function storeUserMeta($metaKey, $newValue){
    $wpUser = wp_get_current_user();
    return update_user_meta($wpUser->ID, $metaKey, $newValue);
}

/**
 * @param $metaKey
 * @return array
 */
function getUserMeta($metaKey): array
{
    $wpUser = wp_get_current_user();
    return !empty(get_user_meta($wpUser->ID, $metaKey)) ? get_user_meta($wpUser->ID, $metaKey) : array('');
}

/**
 * @param $metaKey
 * @return bool
 */
function deleteUserMeta($metaKey): bool
{
    $wpUser = wp_get_current_user();
    return delete_user_meta($wpUser->ID, $metaKey);
}

/**
 * @return array
 */
function getRrulesFrequencies(): array
{
    return array(
        'WEEKLY',
        'MONTHLY',
        'YEARLY'
    );
}

/**
 * @return array
 */
function getPaymentMethods(): array
{
    return array(
        'CB',
        'AMEX',
        'SDD',
    );
}

/**
 * @param $frequency
 * @param int $period
 * @return string
 */
function createRrule($frequency, $period = 0): string
{
    switch ($frequency) {
        case 'YEARLY' :
            $rrulePeriod = $period === 1 ? ";BYMONTH=1;BYMONTHDAY=1" : '';
            break;
        case 'MONTHLY' :
            $rrulePeriod = $period === 1 ? ";BYMONTHDAY=1" : '';
            break;
        case 'WEEKLY' :
            $rrulePeriod = $period === 1 ? ";BYDAY=MO" : '';
            break;
        default :
            $rrulePeriod = '';
    }
    return "RRULE:FREQ=".$frequency . $rrulePeriod . ";INTERVAL=1";
}

/**
 * @param $params
 * @param $key
 * @param string $sha
 * @return string
 */
function getSignature ($params, $key, $sha = '256'): string
{
    switch ($sha){
        case '1' :
            return getSignatureSha1($params, $key);
        default :
            return getSignatureSha256($params, $key);
    }
}

/**
 * @param $params
 * @param $key
 * @return string
 */
function getSignatureSha256 ($params, $key): string
{
    /**
     * Fonction qui calcule la signature.
     * $params : tableau contenant les champs à envoyer dans le formulaire.
     * $key : clé de TEST ou de PRODUCTION
     */
    //Initialisation de la variable qui contiendra la chaine à chiffrer
    $contenu_signature = "";

    //Tri des champs par ordre alphabétique
    ksort($params);
    foreach($params as $nom=>$valeur){

        //Récupération des champs vads_
        if (substr($nom,0,5)=='vads_'){

            //Concaténation avec le séparateur "+"
            $contenu_signature .= $valeur."+";
        }
    }
    //Ajout de la clé en fin de chaine
    $contenu_signature .= $key;

    //Encodage base64 de la chaine chiffrée avec l'algorithme HMAC-SHA-256
    $signature = base64_encode(hash_hmac('sha256',$contenu_signature, $key, true));
    return $signature;
}

/**
 * @param $params
 * @param $key
 * @return string
 */
function getSignatureSha1($params, $key): string
{
    /**
     * Fonction qui calcule la signature.
     * $params : tableau contenant les champs à envoyer dans le formulaire.
     * $key : clé de TEST ou de PRODUCTION
     */
    //Initialisation de la variable qui contiendra la chaine à chiffrer
    $contenu_signature = "" ;

    // Tri des champs par ordre alphabétique
    ksort($params);
    foreach ($params as $nom =>$valeur){

        // Récupération des champs vads_
        if (substr($nom,0,5)=='vads_') {

            // Concaténation avec le séparateur "+"
            $contenu_signature .= $valeur."+";
        }
    }
    // Ajout de la clé à la fin
    $contenu_signature .= $key;

    // Application de l’algorythme SHA-1
    return sha1($contenu_signature);
}

/**
 * filter vads params
 * @return array
 */
function postVadsParams(): array
{
    $params = array();
    foreach ($_POST as $key => $param){
        if(strpos( $key, 'vads' ) !== false ){
            $params[$key] = $param;
        }
    }
    return $params;
}

/**
 * @param $wpUser
 * @param $options
 * @param string $sha
 * @return string[]
 * @throws Exception
 */
function getVadsParams($wpUser, $options, string $sha): array
{
    global $wp;
    $current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
    $userPlan = new Payzen_Subscribers_Plan(get_user_meta($wpUser->ID, 'paysubs_plan')[0]);

    try {
        $vads = array(
            "vads_action_mode"          => "INTERACTIVE",
            "vads_amount"               => $userPlan->getAmount(),
            "vads_ctx_mode"             => strtoupper($options['paysubs_field_mode']),
            "vads_currency"             => "978", //Euro
            "vads_page_action"          => "REGISTER_PAY",
            "vads_payment_config"       => "SINGLE",
            "vads_site_id"              => $options['paysubs_field_user'],
            "vads_trans_date"           => getDateVadsNow(),
            "vads_trans_id"             => getRandomString(6),
            "vads_version"              => "V2",
            "vads_payment_cards"        => "SDD",
            'vads_url_cancel'           => $current_url,
            'vads_url_error'            => $current_url,
            'vads_url_refused'          => $current_url,
            'vads_url_success'          => $options['paysubs_field_redirect_sdd'],
            "vads_cust_email"           => $wpUser->user_email,
            "vads_cust_first_name"      => $wpUser->user_firstname,
            "vads_cust_last_name"       => $wpUser->user_lastname,
            'vads_cust_id'              => get_user_meta($wpUser->ID, 'paysubs_vads_uuid')[0]
        );
    } catch (Exception $e) {
    }
    if(isApiTest()){
        $vads['signature'] = getSignature($vads,$options['paysubs_field_test_api_key'], $sha);
    }else{
        $vads['signature'] = getSignature($vads,$options['paysubs_field_api_key'], $sha);
    }
    return $vads;
}

function getRandomString($n) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}

/**
 * Valid plan if subscription and valid date payment
 */
function isValidPlan(): bool
{
    convertOldGuyawebAccount();
    global $informationAccountErrors;

    if(isCardExpired()){
        $informationAccountErrors->add('cardExpired', __('Card expired', 'payzen-subscribers'));
    }
    if(getUserMeta('paysubs_subscriptionId')[0] === ''){
        $informationAccountErrors->add('subscriberId', __('No subscriber ID', 'payzen-subscribers'));
    }

    //Test if CB
    if(getUserMeta('paysubs_plan')[0] !== '' &&
        (getUserMeta('paysubs_card_effectiveBrand')[0]  === 'CB' ||
            getUserMeta('paysubs_card_effectiveBrand')[0]   === 'VISA' ||
            getUserMeta('paysubs_card_effectiveBrand')[0]   === 'AMEX' ||
            getUserMeta('paysubs_card_effectiveBrand')[0]   === 'MASTERCARD' ||
            getUserMeta('paysubs_card_effectiveBrand')[0]   === 'E-CARTEBLEUE' ||
            getUserMeta('paysubs_card_effectiveBrand')[0]   === 'SDD') &&
        getUserMeta('paysubs_subscriptionId')[0] !== '' &&
        !isCardExpired()
    ){
        return true;
    }
    return false;
}

function isCardExpired(){
    if(getUserMeta('paysubs_card_expiryMonth')[0] === null){
        return false;
    }
    $cardExpiry = (int) (getUserMeta('paysubs_card_expiryYear')[0] . getCardExpiryMonth(getUserMeta('paysubs_card_expiryMonth')[0]));
    if($cardExpiry < (int) current_datetime()->format( 'Ym' )){
        return true;
    }
    return false;
}

/**
 * @param $month
 * @return string
 */
function getCardExpiryMonth($month): string
{
    if(strlen($month) === 1){
        $month = '0'.$month;
    }
    return $month;
}

function convertOldGuyawebAccount(){
    /* is old valid plan and not already convert */
    if(getUserMeta('subscriber_subscription_status')[0] === 'active' &&
        getUserMeta('paysubs_old_account_status')[0] !== 'active')
    {
        $oldToken = getUserMeta('_payzen_payment_token')[0];
        $oldSubscriptionId = getUserMeta('_payzen_subscription_id')[0];
        try {
            $responseTokenPlan = Payzen_Subscribers_Plan::getTokenPlan($oldToken);
            if($responseTokenPlan['status'] === 'SUCCESS' && $responseTokenPlan['answer']['cancellationDate'] === NULL){
                storeUserMeta('paysubs_old_account_status','active');
                storeUserMeta('paysubs_card_expiryMonth', getCardExpiryMonth($responseTokenPlan['answer']['tokenDetails']['expiryMonth']));
                storeUserMeta('paysubs_card_expiryYear', $responseTokenPlan['answer']['tokenDetails']['expiryYear']);
                storeUserMeta('paysubs_card_effectiveBrand', $responseTokenPlan['answer']['tokenDetails']['effectiveBrand']);
                storeUserMeta('paysubs_card_pan', $responseTokenPlan['answer']['tokenDetails']['pan']);
                if(empty(getUserMeta(paysubs_subscriptionId)[0])){
                    storeUserMeta('paysubs_subscriptionId', $oldSubscriptionId);
                }
                if(empty(getUserMeta(paysubs_token)[0])){
                    storeUserMeta('paysubs_token', $oldToken);
                }
                storeUserMeta('paysubs_subscriptionDate', $responseTokenPlan['answer']['creationDate']);
                storeUserMeta('paysubs_receive_newsletter', (getUserMeta('subscriber_newsletter')[0] == 1 ? 'true' : NULL));
                storeUserMeta('paysubs_plan', (getUserMeta('_subscriber_plan_id')[0] == 160455 ? '1' : '2'));
                plugin_log(['convertOldGuyawebAccount' =>
                    [
                        'user_nickname' => wp_get_current_user()->nickname,
                        'user_ID' => wp_get_current_user()->ID,
                        'convert' => 'OK'
                    ]
                ]);
            }
        } catch (\Lyra\Exceptions\LyraException $e) {
            plugin_log(['convertOldGuyawebAccount' =>
                [
                    'user_nickname' => wp_get_current_user()->nickname,
                    'user_ID' => wp_get_current_user()->ID,
                    'convert' => 'NOT OK'
                ]
            ]);
        }catch (Exception $e) {
            if(getUserMeta('paysubs_old_account_status')[0] === ''){
                plugin_log(['convertOldGuyawebAccount' =>
                    [
                        'user_nickname' => wp_get_current_user()->nickname,
                        'user_ID' => wp_get_current_user()->ID,
                        'oldToken' => 'EMPTY',
                        'error_message' => $e->getMessage()
                    ]
                ]);
                storeUserMeta('paysubs_old_account_status', 'empty');
            }
        }

    }
}

/**
 * @param $entry
 * @param string $mode
 * @param string $file
 * @return false|int
 */
function plugin_log( $entry, $mode = 'a', $file = 'payzen-subscribers' ) {
    // Get WordPress uploads directory.
    $upload_dir = plugin_dir_path(__DIR__).'logs';

    // If the entry is array, json_encode.
    if ( is_array( $entry ) ) {
        $entry = json_encode( $entry );
    }

    // Write the log file.
    $file  = $upload_dir . '/' . $file . '-' . current_datetime()->format('Y-m') . '.log';
    $file  = fopen( $file, $mode );
    $bytes = fwrite( $file, current_time( 'mysql' ) . "::" . $entry . "\n" );
    fclose( $file );

    return $bytes;
}

/**
 * @param Payzen_Subscribers_Plan $plan
 * @return bool
 */
function isTimeToRenewPaymentMethode(Payzen_Subscribers_Plan $plan): bool
{
    $dateToSend = current_datetime()->modify('+'.$plan->getEmailDays().' day')->format('d');
    if(isValidPlan() && $plan->getId() !== false &&
        (int) getUserMeta('paysubs_card_expiryMonth')[0] === (int) current_datetime()->format( 'm' ) &&
        (int) getUserMeta('paysubs_card_expiryYear')[0] === (int) current_datetime()->format( 'Y' ) &&
        $dateToSend === date('t')
    ){
        return true;
    }
    return false;
}

function removeUserPlan($withSubscription = false){
    $metas = array(
        "paysubs_card_effectiveBrand",
        "paysubs_card_pan",
        "paysubs_card_expiryMonth",
        "paysubs_card_expiryYear"
    );
    if($withSubscription){
        array_push($metas,"paysubs_subscriptionId", "paysubs_token", "paysubs_plan", "paysubs_subscriptionDate");
    }

    foreach ($metas as $meta){
        deleteUserMeta($meta);
    }

}

function displayApiForm($response): string
{
    /* I check if there are some errors */
    if ($response['status'] != 'SUCCESS') {
        /* an error occurs, I throw an exception */
        payzen_display_error($response);
        $error = $response['answer'];
        throw new Exception("error " . $error['errorCode'] . ": " . $error['errorMessage'] );
    }

    /* everything is fine, I extract the formToken */
    $formToken = $response["answer"]["formToken"];
    return '
    <!-- payment form -->
    <div id="paysubs_payment_anchor_form" class="kr-embedded"
         kr-form-token="' . $formToken . '">

        <!-- payment form fields -->
        <div class="kr-pan"></div>
        <div class="kr-expiry"></div>
        <div class="kr-security-code"></div>

        <!-- payment form submit button -->
        <button class="kr-payment-button"></button>

        <!-- error zone -->
        <div class="kr-form-error"></div>
        <div class="logo_payzen">
            <img src="'. plugin_dir_url(dirname(__FILE__)) .'public/assets/payzen-secure-2017-card-300x107.png">
        </div>
    </div>';
}

/**
 * Use if isPayzenApiResponse
 * @return string
 */
function callbackTokenIs(): string
{
    $client = Payzen_API::getPayzenClient();
    try {
        /* No POST data ? paid page in not called after a payment form */
        if (empty($_POST)) {
            throw new Exception("no post data received!");
        }

        //Widget add slashes in json POST
        //Remove slashes
        $_POST['kr-answer'] = stripslashes($_POST['kr-answer']);
        $formAnswer = $client->getParsedFormAnswer();

        /* Check the signature */
        if (!$client->checkHash()) {
            //something wrong, probably a fraud ....
            signature_error($formAnswer['kr-answer']['transactions'][0]['uuid'], $hashKey,
                $client->getLastCalculatedHash(), $_POST['kr-hash']);
            throw new Exception("invalid signature");
        }

        return $formAnswer['kr-answer']['orderStatus'];

    } catch (\Lyra\Exceptions\LyraException $e) {
        if(isApiTest()){
            echo $e->getMessage();
        }
    } catch (Exception $e) {
        if(isApiTest()){
            echo $e->getMessage();
        }
    }
}

/**
 * @return bool
 */
function callbackTokenIsPaid(): bool
{
    return callbackTokenIs() === 'PAID';
}

/**
 * Use it if isPayzenApiResponse
 * @param $uuid
 */
function storeUserTransaction($uuid = null)
{
    $response = Payzen_API::getTransaction($uuid);
    $expireMonth = (strlen($response['answer']['transactionDetails']['cardDetails']['expiryMonth']) === 1 ?
        '0' . $response['answer']['transactionDetails']['cardDetails']['expiryMonth'] :
        $response['answer']['transactionDetails']['cardDetails']['expiryMonth'] );
    storeUserMeta('paysubs_card_effectiveBrand',$response['answer']['transactionDetails']['cardDetails']['effectiveBrand']);
    storeUserMeta('paysubs_card_pan',$response['answer']['transactionDetails']['cardDetails']['pan']);
    storeUserMeta('paysubs_card_expiryMonth',$expireMonth);
    storeUserMeta('paysubs_card_expiryYear',$response['answer']['transactionDetails']['cardDetails']['expiryYear']);
    storeUserMeta('paysubs_token',$response['answer']['paymentMethodToken']);
}

/**
 * Display wp errors
 * @param $accountErrors
 * @param $message
 * @return string
 */
function loginErrorAccount($accountErrors, $message): string
{
    if($accountErrors->has_errors()) {
        $error = $accountErrors->get_error_message($message);
        if (null !== $error) {
            return '<span class="error-message">' . $error . '</span>';
        }
    }
    return '';
}

/**
 * Store invoice(s) in user meta
 * @param $invoice
 */
function storeUserInvoices($invoice){

    /* add plan to invoice(s) */
    $planId = getUserMeta('paysubs_plan')[0];
    try {
        $plan = new Payzen_Subscribers_Plan($planId);
        $invoice['planId'] = $plan->getId();
        $invoice['planFrequency'] = $plan->getFrequency();
    } catch (Exception $e) {
        $invoice['planId'] = null;
    }

    /* Store invoice(s) in user meta */
    if(!empty(getUserMeta('paysubs-invoices')[0])){
        $invoices = json_decode(getUserMeta('paysubs-invoices')[0], true);
        $invoices[getDateVadsNow()] = $invoice;
        storeUserMeta('paysubs-invoices', json_encode($invoices));
    }else{
        storeUserMeta('paysubs-invoices',json_encode([getDateVadsNow() => $invoice]));
    }
}

function getUserInvoices()
{
    return json_decode(getUserMeta('paysubs-invoices')[0], true);
}

function getFrequencyTranslate($freq): string
{
    switch ($freq) {
        case 'YEARLY' :
            return __('YEARLY','payzen-subscribers');
        case 'MONTHLY' :
            return __('MONTHLY','payzen-subscribers');
        case 'WEEKLY' :
            return __('WHEEKLY','payzen-subscribers');
        default :
            return '';
    }
}

/**
 * @param $file
 * @return string
 */
function getOverridePublicPath($file): string
{

    $publicPluginPath = plugin_dir_path(dirname(__FILE__)).'public/'.$file;
    $publicTemplatePath = get_template_directory() . '/payzen-subscribers/public/'.$file;
    $publicPluginUrl = plugin_dir_url(dirname(__FILE__)).'public/'.$file;
    $publicTemplateUrl = get_template_directory_uri() . '/payzen-subscribers/public/'.$file;

    if(file_exists($publicTemplatePath)){
        return $publicTemplateUrl;
    }elseif(file_exists($publicPluginPath)){
        return $publicPluginUrl;
    }
    return '';

}

function addScriptsPayzenForm(){
    $client = Payzen_API::getPayzenClient();
    ?>
    <script
        src="<?php echo $client->getClientEndpoint(); ?>/static/js/krypton-client/V4.0/stable/kr-payment-form.min.js"
        kr-public-key="<?php echo $client->getPublicKey(); ?>"
        kr-post-url-success="<?php echo get_permalink(get_the_id()); ?>">
    </script>

    <link rel="stylesheet"
          href="<?php echo $client->getClientEndpoint(); ?>/static/js/krypton-client/V4.0/ext/classic-reset.css">
    <script
        src="<?php echo $client->getClientEndpoint(); ?>/static/js/krypton-client/V4.0/ext/classic.js">
    </script>
    <?php
}

/**
 * Create vads UUID
 */
function createVadsUuid($userId){
   return md5(site_url().'-'.$userId);
}