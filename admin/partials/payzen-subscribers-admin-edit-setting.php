<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://pilab.fr
 * @since      1.0.0
 *
 * @package    payzen_subscribers
 * @subpackage payzen_subscribers/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">
    <h1><?php echo get_admin_page_title();?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields( 'smashing_fields' );
        do_settings_sections( 'smashing_fields' );
        submit_button();
        ?>
    </form>
</div>