<?php
/*
Plugin Name: Stripe Member Registration
Description: A plugin to handle member registration with Stripe integration.
Version: 1.0
Author: Chakramani Joshi
*/

if (!defined('ABSPATH')) {
    exit;
};

// On activation of plugin, create a table
register_activation_hook( __FILE__, 'activate_stripe_member_registration' );
function activate_stripe_member_registration() {
	global $wpdb;
    $prefix = $wpdb->prefix;
    $form_db = $prefix . "stripe_member_registration";
    $charset_collate = $wpdb->get_charset_collate();
    //Check if table exists. In case it's false we create it
    if ($wpdb->get_var("SHOW TABLES LIKE '$form_db'") !== $form_db) {
        
        $sql = "CREATE TABLE " . $form_db . "(
            id int(11) NOT NULL AUTO_INCREMENT,
            first_name VARCHAR(50) NULL,
            last_name VARCHAR(50) NULL,
            email VARCHAR(100) NULL,
            phone int(50) NULL,
            paid_amount int(11) NULL,
            payment_intent_id VARCHAR(100) NULL,
            created_at DATETIME NULL,
            PRIMARY KEY  (id)
            ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// On deactivation drop table
register_deactivation_hook( __FILE__, 'deactivate_stripe_member_registration' );
function deactivate_stripe_member_registration() {
	global $wpdb;
    $table_name = $wpdb->prefix.'stripe_member_registration';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
}


function multi_stripe_style_enqueue_admin()
{
    if (is_admin() && isset($_GET['page']) && ($_GET['page'] === 'stripe-setting' || $_GET['page'] === 'stripe-history-page')) {
        wp_enqueue_style('cpm_custom_for_css_admin', plugin_dir_url(__FILE__) . '/style_admin.css', array(), rand(), false);
    }
}
add_action('admin_enqueue_scripts', 'multi_stripe_style_enqueue_admin');

function multi_stripe_style_enqueue()
{
    wp_enqueue_style('cpm_custom_for_css_frontend', plugin_dir_url(__FILE__) . '/style.css', array(), rand(), false);
    wp_enqueue_script('cm-stripe', 'https://js.stripe.com/v3/', array(), null);
    wp_enqueue_script('member-registration-stripe', plugin_dir_url(__FILE__) . '/member-registration-stripe.js', array('cm-stripe'), null);
}

add_action('wp_enqueue_scripts', 'multi_stripe_style_enqueue');


/* require plugin loder file */
$init_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "stripe-member-registration" . DIRECTORY_SEPARATOR  . "stripe-member-loader.php";
$registration_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "stripe-member-registration" . DIRECTORY_SEPARATOR  . "stripe-member-registration-page.php";
require $init_file;
require $registration_file;
