<?php

/**
 * Description of class-payzen-subscribers-subscriptions-settings
 *
 * @author adrien
 */
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Payzen_Subscribers_Subscriptions_Plans extends WP_List_Table{

    private $table_plugin_name;
    private $db;

    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'plan',
            'plural' => 'plans',
        ));
        $this->db = new Payzen_DB();
        $this->table_plugin_name = $this->db->getDbName();

    }

    public function displaySettings() {

        $message = '';
        if ('delete' === $this->current_action()) {
            $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'payzen-subscribers'), count(array($_REQUEST['id']))) . '</p></div>';
            //Do delete action
            $this->db->deleteItems(array('id' => $_REQUEST['id']));
        }

        $this->prepare_items();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php _e('Parameters list subscriptions', 'payzen-subscribers') ?></p>
        </div>
        <div class="wrap">
            <?php echo $message; ?>
            <form id="factures-table" method="GET">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <?php $this->display() ?>
            </form>
        </div>
        <?php
    }

    /**
     * Display setting
     */
    public function displaySetting(){
        global $wpdb;
        $table_name = $this->db->getDbName();

        $message = '';
        $notice = '';

        $paymentMethods = array();

        /**
         * Update or new subscriptions-setting
         */
        if(isset($_REQUEST['title'])) {
            /**
             * Treatment submitted form : json payment methods checkboxes
             */
            foreach (getPaymentMethods() as $key => $paymentMethod){
                if(isset($_REQUEST['paymentMethod_'.$paymentMethod])) {
                    array_push($paymentMethods, $_REQUEST['paymentMethod_' . $paymentMethod]);
                }
            }

            $default = array(
                'title' => $_REQUEST['title'],
                'description' => $_REQUEST['description'],
                'amount' => $_REQUEST['amount'],
                'frequency' => $_REQUEST['frequency'],
                'period' => $_REQUEST['period'],
                'paymentMethod' => json_encode($paymentMethods),
                'email_sender' => $_REQUEST['email_sender'],
                'email_subject' => $_REQUEST['email_subject'],
                'email_message' => $_REQUEST['email_message'],
                'email_days' => $_REQUEST['email_days'],
            );
        }
        if(isset($_REQUEST['id']) && (int) $_REQUEST['id'] === 0){ // if is new
            $item = shortcode_atts($default, $_REQUEST);
            $this->db->newSetting($item);
            $message = __('Saved','payzen-subscribers');
        }elseif(isset($_REQUEST['title'])) { // if is update
            $item = shortcode_atts($default, $_REQUEST);
            $where = array( 'id' => (int) $_REQUEST['id'] );
            $this->db->updateSetting($item, $where);
            $message = __('Saved','payzen-subscribers');
        }else{ // if is not exist
            $default = array(
                'id' => 0,
                'title' => '',
                'description' => '',
                'amount' => '',
                'frequency' => '',
                'period' => 0,
                'paymentMethod' => '',
                'email_sender' => wp_get_current_user()->get('user_email'),
                'email_days' => 1,
                'email_subject' => '',
                'email_message' => '',
            );
            $item = $default;
        }

        // if this is not post back we load item to edit or give new one to create
        if (isset($_REQUEST['id']) && (int) $_REQUEST['id'] !== 0) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'payzen-subscribers');
            }
        }elseif(isset($_REQUEST['id'])){
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE 1 = %d ORDER BY id DESC", 1), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'payzen-subscribers');
            }
        }

        // here we adding our custom meta box
        add_meta_box('param_form_meta_box', __('Settings', 'payzen-subscribers'), array($this, 'displayMetaBox'), 'payzen-souscriptions-setting', 'normal', 'default');
        add_meta_box('email_form_meta_box', __('Settings emails', 'payzen-subscribers'), array($this, 'displayEmailMetaBox'), 'payzen-souscriptions-email-setting', 'normal', 'default');
        ?>
        <div class="wrap">

            <?php if (!empty($notice)): ?>
                <div id="notice" class="error below-h2"><p><?php echo $notice ?></p></div>
            <?php endif; ?>
            <?php if (!empty($message)): ?>
                <div id="message" class="updated below-h2"><p><?php echo $message ?></p></div>
            <?php endif; ?>

            <form id="form" method="POST">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>"/>
                <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
                <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

                <div class="metabox-holder" id="poststuff">
                    <div id="post-body">
                        <div id="post-body-content">
                            <form method="POST" action="<?php echo admin_url( 'admin.php' ); ?>">
                                <?php do_meta_boxes('payzen-souscriptions-setting', 'normal', $item); ?>
                                <?php do_meta_boxes('payzen-souscriptions-email-setting', 'normal', $item); ?>
                            </form>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    public function displayMetaBox($item) {
        ?>
            <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
                <tbody>
                <tr class="form-field">
                    <th valign="top" scope="row">
                        <label for="name"><?php _e('Id', 'payzen-subscribers') ?></label>
                    </th>
                    <td>
                        <input type="number" name="id" value="<?php echo esc_attr($item['id']) ?>" />
                    </td>
                </tr>
                <tr class="form-field">
                    <th valign="top" scope="row">
                        <label for="name"><?php _e('Title', 'payzen-subscribers') ?></label>
                    </th>
                    <td>
                        <input type="text" name="title" value="<?php echo esc_attr(wp_unslash($item['title'])) ?>" />
                    </td>
                </tr>
                <tr class="form-field">
                    <th valign="top" scope="row">
                        <label for="name"><?php _e('Description', 'payzen-subscribers') ?></label>
                    </th>
                    <td>
                        <input type="text" name="description" value="<?php echo esc_attr(wp_unslash($item['description'])) ?>" />
                    </td>
                </tr>
                <tr class="form-field">
                    <th valign="top" scope="row">
                        <label for="name"><?php _e('Amount', 'payzen-subscribers') ?> (7€ = 700)</label>
                    </th>
                    <td>
                        <input type="text" name="amount" value="<?php echo esc_attr($item['amount']) ?>" />
                    </td>
                </tr>
                <tr class="form-field">
                    <th valign="top" scope="row">
                        <label for="name"><?php _e('Payment method', 'payzen-subscribers') ?></label>
                    </th>
                    <td>
                        <?php
                        foreach (getPaymentMethods() as $key => $paymentMethod){
                            ?>
                            <input type="checkbox" name="paymentMethod_<?php echo $paymentMethod; ?>" value="<?php echo $paymentMethod; ?>" <?php echo (in_array($paymentMethod, json_decode($item['paymentMethod'])) ? 'checked' : ''); ?>>
                                <?php _e(ucfirst(strtolower($paymentMethod)), 'payzen-subscribers'); ?>
                            </input>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <tr class="form-field">
                    <th valign="top" scope="row">
                        <label for="name"><?php _e('Frequency', 'payzen-subscribers') ?></label>
                    </th>
                    <td>
                        <select id="frequency" name="frequency" >
                        <?php
                            foreach (getRrulesFrequencies() as $key => $rruleFreq){
                                ?>
                                <option value="<?php echo $rruleFreq; ?>" <?php echo ($item['frequency'] === $rruleFreq ? 'selected' : ''); ?>><?php echo ucfirst(strtolower(getFrequencyTranslate($rruleFreq))); ?></option>
                                <?php
                            }
                        ?>
                        </select>
                    </td>
                </tr>
                <tr class="form-field">
                    <th valign="top" scope="row">
                        <label for="name"><?php _e('Period', 'payzen-subscribers') ?></label>
                    </th>
                    <td>
                        <select id="period" name="period" >
                            <option value="0" <?php echo ((int)$item['period'] === 0 ? 'selected' : ''); ?>><?php _e('Inscription date', 'payzen-subscribers'); ?></option>
                        <?php
                        //TODO here is possible to add periods => front end is not dev
                            for ($i = 1; $i <= 0; $i++){
                                ?>
                                <option value="<?php echo $i; ?>" <?php echo ((int)$item['period'] === $i ? 'selected' : ''); ?>><?php _e('First day of the period', 'payzen-subscribers'); ?></option>
                                <?php
                            }
                        ?>
                        </select>
                    </td>
                </tr>
                <tr class="form-field">
                    <th valign="top" scope="row">
                    </th>
                    <td>
                        <?php echo submit_button();?>
                    </td>
                </tr>
                </tbody>
            </table>
        <?php
    }

    function displayEmailMetaBox($item){
    ?>
        <form method="POST" action="<?php echo admin_url( 'admin.php' ); ?>">
            <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
                <tbody>
                    <tr class="form-field">
                        <th valign="top" scope="row">
                            <label for="name"><?php _e('Day(s) sending before expiration', 'payzen-subscribers') ?></label>
                        </th>
                        <td>
                            <input type="number" name="email_days" value="<?php echo esc_attr($item['email_days']) ?>" />
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th valign="top" scope="row">
                            <label for="name"><?php _e('Email sender', 'payzen-subscribers') ?></label>
                        </th>
                        <td>
                            <input type="text" name="email_sender" value="<?php echo esc_attr(wp_unslash($item['email_sender'])) ?>" />
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th valign="top" scope="row">
                            <label for="name"><?php _e('Subject', 'payzen-subscribers') ?></label>
                        </th>
                        <td>
                            <input type="text" name="email_subject" value="<?php echo esc_attr(wp_unslash($item['email_subject'])) ?>" />
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th valign="top" scope="row">
                            <label for="name"><?php _e('Message', 'payzen-subscribers') ?> (html)</label>
                        </th>
                        <td>
                            <textarea type="text" name="email_message" value="<?php echo esc_attr(wp_unslash($item['email_message'])) ?>" ><?php echo esc_attr(wp_unslash($item['email_message'])) ?></textarea>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th valign="top" scope="row">
                        </th>
                        <td>
                            <?php echo submit_button();?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    <?php
    }

    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" nom="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_title($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'details' => sprintf('<a href="?page=payzen-subscriptions-setting&id=%s">%s</a>', $item['id'], __('Modifier', 'payzen-subscribers')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'payzen-subscribers')),
        );

        return sprintf('%s %s',
            $item['title'],
            $this->row_actions($actions)
        );
    }


    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'id' => __('id', 'payzen-subscribers'),
            'title' => __('Title', 'payzen-subscribers'),
            'description' => __('Description', 'payzen-subscribers'),
            'amount' => __('Amount', 'payzen-subscribers'),
            'frequency' => __('Frequency', 'payzen-subscribers')
        );
        return $columns;
    }

    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array('id', true),
            'title' => array('title', true),
        );
        return $sortable_columns;
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    function get_bulk_actions()
    {
        $actions = array(
        );
        return $actions;
    }


    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing pdf action
     * message about successful deletion will be shown on page in next part
     */
    function process_bulk_action()
    {
        $table_name = $this->table_plugin_name; // do not forget about tables prefix

    }

    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items()
    {
        global $wpdb;
        $table_name = $this->table_plugin_name; // do not forget about tables prefix

//        print_r($table_name);die;

        $per_page = 20; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = wp_unslash($wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged*$per_page), ARRAY_A));
        foreach ($this->items as $key => $item){
            $this->items[$key]['frequency'] = getFrequencyTranslate($item['frequency']);
        }

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }

}
