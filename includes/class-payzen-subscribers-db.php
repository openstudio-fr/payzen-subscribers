<?php


class Payzen_DB
{
    private $dbName;

    function __construct() {
        global $wpdb;
        $this->dbName = $wpdb->prefix."payzen_subscriptions_settings";
    }

    public function getDbName(){
        return $this->dbName;
    }

    public function install() {
        global $wpdb;

        $wpdb->query("CREATE TABLE IF NOT EXISTS {$this->dbName} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NULL,
            amount INT NOT NULL,
            frequency VARCHAR(255) NOT NULL,
            period INT NOT NULL,
            paymentMethod VARCHAR(255) NOT NULL,
            email_sender VARCHAR(255) NOT NULL,
            email_days INT NOT NULL,
            email_subject VARCHAR(255) NOT NULL,
            email_message LONGTEXT NULL);");
    }

    public function update() {
        global $wpdb;
        $version = 0;

        if (defined('PAYZEN_SUBSCRIBERS_VERSION')) {
            $version = PAYZEN_SUBSCRIBERS_VERSION;
        }

        //Faire les tests de version
    }

    /**
     * @param $item
     * @param $where
     * @return bool
     */
    public function updateSetting($item, $where){
        global $wpdb;
        try {
            $response = $wpdb->update($this->getDbName(), $item, $where);
        }catch (Exception $e){
            return false;
        }
        if($response === 0) {
        }
        return true;
    }

    public function newSetting($item){
        global $wpdb;
        try {
            $wpdb->insert($this->getDbName(), $item);
        }catch (Exception $e){
            return false;
        }
        return true;
    }

    public function deleteItems($id) {
        global $wpdb;
        try {
            $wpdb->delete($this->getDbName(), $id);
        }catch (Exception $e){
            return false;
        }
        return true;
    }

    public function uninstall() {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$this->dbName};");
    }

    public function getPlanById($id) {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->dbName} WHERE id={$id};");
    }

    public function getPlans(): array
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->dbName};");
    }
}