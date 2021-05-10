<?php


use Lyra\Client;

class Payzen_API
{

    /**
     * @return Client
     */
    public static function getPayzenClient(): Client
    {
        $options = get_option('paysubs_api_settings');

        /* Username, password and endpoint used for server to server web-service calls */
        Client::setDefaultUsername($options['paysubs_field_user']);
        Client::setDefaultEndpoint($options['paysubs_field_name_server']);

        if(isApiTest()){ /* TEST */
            /* password */
            Client::setDefaultPassword($options['paysubs_field_test_password']);
            /* publicKey and used by the javascript client */
            Client::setDefaultPublicKey($options['paysubs_field_js_key_test']);
            /* SHA256 key */
            Client::setDefaultSHA256Key($options['paysubs_field_hash_key_test']);
        }else{ /* PRODUCTION */
            /* password */
            Client::setDefaultPassword($options['paysubs_field_prod_password']);
            /* publicKey and used by the javascript client */
            Client::setDefaultPublicKey($options['paysubs_field_js_key_prod']);
            /* SHA256 key */
            Client::setDefaultSHA256Key($options['paysubs_field_hash_key_prod']);
        }

        return new Client();
    }

    /**
     * @param null $uuid
     * @return mixed
     * @throws \Lyra\Exceptions\LyraException
     */
    public static function getTransaction($uuid = null){
        $client = Payzen_API::getPayzenClient();
        if($uuid === null){
            $formAnswer = $client->getParsedFormAnswer();
            $uuid = $formAnswer['kr-answer']['transactions'][0]['uuid'];
        }
        $store = array(
            "uuid" => $uuid
        );
        return $client->post("V4/Transaction/Get", $store);
    }

    /**
     * @return bool
     */
    public static function isPayzenApiResponse(): bool
    {
        if (empty ($_POST)){
            return false;
        }
        return isset($_POST['kr-hash']);
    }

    /**
     * @return bool
     */
    public static function isIpnSddResponse(): bool
    {
        if (empty ($_POST)){
            return false;
        }
        return isset($_POST['vads_url_check_src']);
    }

}