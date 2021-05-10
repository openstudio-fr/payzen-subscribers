<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Payzen_Subscribers_Widget_Subscribe_Form extends WP_Widget {

    /**
     * Formulaire public du widget basic
     * @var type Payzen_Subscribers_Widget_Subscribe_Form_Pub
     */
    protected $widgetBasic;
    protected $db;

    public function __construct() {
        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */



        parent::__construct('payzen_subscribers_subscribe_form', 'Payzen subscribe form', array('description' => 'Formulaire de d\'abonnement via Payzen'));
        /**
         * Load widget public display
         */
        $this->widgetBasic = new Payzen_Subscribers_Widget_Subscribe_Form_Pub();
        $this->db   = new Payzen_DB();
    }

    /**
     * Display widget admin form
     * @param $instance
     * @return string
     */
    public function form($instance)
    {
        $id = isset($instance['id']) ? $instance['id'] : '';
        ?>
            <p><?php _e('To display widget by schortcode', 'payzen-subscribers') ?> [paysubs id=XX /]</p>
            <p>
                <label for="<?php echo $this->get_field_name('id'); ?>"><?php _e('Id','payzen_subscribers'); echo ' :' ?></label>
                <input class="widefat"
                       id="<?php echo $this->get_field_id('id'); ?>"
                       name="<?php echo $this->get_field_name('id'); ?>"
                       type="text"
                       value="<?php echo $id; ?>"
                />
            </p>
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
         * If id is defined : [paysubs id=XX /] or widget form => display just this id plan
         * Else display all plans
         */
        $id = isset($instance['id']) ? (int) $instance['id'] : null;
        if($id){
            $plans = $this->db->getPlanById($id);
        }else{
            $plans = $this->db->getPlans();
        }

        $this->widgetBasic->subscribeFormActions($args, $plans);

    }

}
