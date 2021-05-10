<?php

use Lyra\Exceptions\LyraException;

/**
 * Get Plan
 * Class Payzen_Subscribers_Plan
 */
class Payzen_Subscribers_Plan
{

    private $id;
    private $title;
    private $description;
    private $amount;
    private $frequency;
    private $period;
    private $paymentMethod;
    private $initialAmount;
    private $initialAmountNumber;
    private $dateFirstPayment;
    private $prefixOrderId;
    private $currency;
    private $emailDays;
    private $emailSender;
    private $emailSubject;
    private $emailMessage;

    /**
     * I create a formToken
     */
    /**
     * Payzen_suscribers_plans constructor.
     * @param $id
     * @throws Exception
     */
    public function __construct($id)
    {
        if($id === ''){
            throw new Exception('Plan id is empty');
        }
        $db = new Payzen_DB();
        $dbPlan = $db->getPlanById($id)[0];
        $this->id = $id;
        $this->title = $dbPlan->title;
        $this->description = $dbPlan->description;
        $this->amount = $dbPlan->amount;
        $this->frequency = $dbPlan->frequency;
        $this->period = $dbPlan->period;
        $this->paymentMethod = $dbPlan->paymentMethod;
        $this->emailDays = $dbPlan->email_days;
        $this->emailSender = $dbPlan->email_sender;
        $this->emailSubject = $dbPlan->email_subject;
        $this->emailMessage = $dbPlan->email_message;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @return mixed
     */
    public function getFrequencyTranslate()
    {
        $freq = $this->frequency;
        return getFrequencyTranslate($freq);
    }

    /**
     * @return mixed
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethodArray()
    {
        return json_decode($this->paymentMethod);
    }

    /**
     * @return mixed
     */
    public function getInitialAmount()
    {
        return $this->initialAmount;
    }

    /**
     * @return mixed
     */
    public function getInitialAmountNumber()
    {
        return $this->initialAmountNumber;
    }

    /**
     * @return mixed
     */
    public function getDateFirstPayment()
    {
        return $this->dateFirstPayment;
    }

    /**
     * @return mixed
     */
    public function getPrefixOrderId()
    {
        return $this->prefixOrderId;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return mixed
     */
    public function getEmailDays()
    {
        return $this->emailDays;
    }

    /**
     * @return mixed
     */
    public function getEmailSender()
    {
        return $this->emailSender;
    }

    /**
     * @return mixed
     */
    public function getEmailSubject()
    {
        return $this->emailSubject;
    }

    /**
     * @return mixed
     */
    public function getEmailMessage()
    {
        return $this->emailMessage;
    }

    /**
     * I create a form token
     * @param $currency
     * @param $orderId
     * @param $email
     * @return array
     * @throws LyraException
     */
    public static function createTokenPlan($currency, $orderId, $email): array
    {
        $client = Payzen_API::getPayzenClient();
        $store = array(
            "currency" => $currency, //EUR
            "orderId" => uniqid($orderId),
            "customer" => array(
                "email" => $email
            ));

        return $client->post("V4/Charge/CreateToken", $store);
    }

    /**
     * @param $token
     * @param $currency
     * @param $orderId
     * @param $email
     * @return mixed
     * @throws LyraException
     */
    public static function updateTokenPlan($token, $currency, $orderId, $email){
        $client = Payzen_API::getPayzenClient();
        $store = array(
            "paymentMethodToken" => $token,
            "currency" => $currency, //EUR
            "orderId" => uniqid($orderId),
            "customer" => array(
                "email" => $email
            ));

        return $client->post("V4/Token/Update", $store);
    }

    /**
     * @param $token
     * @return mixed
     * @throws LyraException
     * @throws Exception
     */
    public static function getTokenPlan($token){
        if($token === ""){
            throw new Exception('Token empty');
        }
        $client = Payzen_API::getPayzenClient();
        $store = array(
            "paymentMethodToken" => $token
        );
        return $client->post("V4/Token/Get", $store);
    }

    /**
     * I create a formToken with initial payment
     * @param $planId
     * @param $currency
     * @param $orderId
     * @param $email
     * @return array
     * @throws LyraException
     */
    public static function createPaymentPlan($planId, $currency, $orderId, $email): array
    {
        $client = Payzen_API::getPayzenClient();
        $db = new Payzen_DB();
        $plan = $db->getPlanById($planId)[0];
        $wpUser = wp_get_current_user();
        $store = array(
            "amount"        => $plan->amount,
            "formAction"    => "REGISTER_PAY",
            "currency"      => $currency, //EUR
            "orderId"       => uniqid($orderId),
            "customer"      => array(
                "email"             => $email,
                "reference"         => getUserMeta('paysubs_vads_uuid')[0],
                "billingDetails"    => array(
                    "firstName" => $wpUser->first_name,
                    "lastName"  => $wpUser->last_name
                )
            ));

        return $client->post("V4/Charge/CreatePayment", $store);
    }

    /**
     * I create a subscription
     * @param $currency
     * @param $orderId
     * @param $initialAmountNumber
     * @return array
     * @throws LyraException
     */
    public function createSubscriptionPlan($currency, $orderId, $initialAmountNumber): array
    {
        $client = Payzen_API::getPayzenClient();
        /* Use client SDK helper to retrieve POST parameters */
        if(is_user_logged_in()){
            $paymentMethodToken = getUserMeta("paysubs_token")[0];
        }else{
            $formAnswer = $client->getParsedFormAnswer();
            $paymentMethodToken = $formAnswer['kr-answer']['transactions'][0]['paymentMethodToken'];
        }


        $store = array(
            "amount" => $this->getAmount(),
            "currency" => $currency,
            "effectDate" => getStartSubscriptionPlan($this->getFrequency()),
            "orderId" => uniqid($orderId),
            "initialAmount" => $this->getAmount(),
            "initialAmountNumber" => $initialAmountNumber,
            "paymentMethodToken" => $paymentMethodToken,
            "rrule" => createRrule($this->getFrequency(), (int) $this->getPeriod())
        );

        return $client->post("V4/Charge/CreateSubscription", $store);
    }

    /**
     * @param $subscriptionId
     * @param $token
     * @return mixed
     * @throws LyraException
     */
    public static function cancelSubscriptionPlan($subscriptionId, $token){
        $client = Payzen_API::getPayzenClient();

        $store = array(
            "paymentMethodToken" => $token,
            "subscriptionId" => $subscriptionId
        );
        return $client->post("V4/Subscription/Cancel", $store);
    }

    public static function getSubscriptionPlan($subscriptionId, $token){
        $client = Payzen_API::getPayzenClient();
        $store = array(
            "paymentMethodToken" => $token,
            "subscriptionId" => $subscriptionId
        );
        return $client->post("V4/Subscription/Get", $store);
    }

    public function getPlanById($planId){
        $db = new Payzen_DB();
        return $db->getPlanById($planId)[0];
    }

    public static function getPlans(): array
    {
        $db = new Payzen_DB();
        return $db->getPlans();
    }

}