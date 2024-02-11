<?php

/**
 * Register a custom menu page.
 */
function register_stripe_member__menu_page()
{
    add_menu_page(
        __('Stripe Member'),
        __('Stripe Member'),
        'manage_options',
        'stripe-setting',
        'multi_stripe_setting_page',
        'dashicons-screenoptions',
        6
    );
}
add_action('admin_menu', 'register_stripe_member__menu_page');

// Add a submenu item
function custom_submenu_page()
{
    add_submenu_page(
        'stripe-setting', // Parent menu slug
        'Stripe History', // Page title
        'Stripe History', // Menu title
        'manage_options', // Capability required to access
        'stripe-history-page', // Menu slug
        'stripe_history_page_callback' // Function to render the page
    );
}
add_action('admin_menu', 'custom_submenu_page');


function multi_stripe_setting_page()
{
    if (isset($_POST['save'])) {
        $stripe_api_key = sanitize_text_field($_POST['stripe_api_key']);
        $stripe_secret_key = sanitize_text_field($_POST['stripe_secret_key']);
        update_option('stripe_api_key', $stripe_api_key);
        update_option('stripe_secret_key', $stripe_secret_key);

        echo '<div id="message" class="updated inline"><p><strong>Your settings have been saved.</strong></p></div>';
    }
?>
    <div class='container'>
        <div class='main'>
            <div class='main__header'>
                <h2><?php _e('EURO Stripe settings'); ?></h2>
            </div>
            <div class='main__content'>
                <div class='main__settings-form'>
                    <form method='post'>
                        <label class='main__input-label'><?php _e('Publishable Key:'); ?></label>
                        <input class='main__input' placeholder='API Key' type='text' name="stripe_api_key" value="<?php echo !empty(stripe_api_key()) ? stripe_api_key() : ''; ?>">
                        <label class='main__input-label'><?php _e('Secret Key:'); ?></label>
                        <input class='main__input' placeholder='Secret Key' type='text' name="stripe_secret_key" value="<?php echo !empty(stripe_secret_key()) ? stripe_secret_key() : ''; ?>">
                        <input type="submit" name="save" value="Save" class='btn main__save-button'>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php
}

function stripe_history_page_callback()
{
    global $wpdb;
    $query = "SELECT * FROM {$wpdb->prefix}stripe_member_registration WHERE payment_intent_id IS NOT NULL";
    $results = $wpdb->get_results($query);
?>

    <div class="container">
        <h1>User Transaction Table</h1>
        <table class="rwd-table">
            <tbody>
                <tr>
                    <th>S.N.</th>
                    <th>First Name</th>
                    <th>Last Number</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Paid Amount</th>
                    <th>Transactin ID</th>
                    <th>Created At</th>
                </tr>
                <?php foreach ($results as $key => $result) : ?>
                    <tr>
                        <td data-th="Supplier Code">
                            <?php echo ++$key; ?>
                        </td>
                        <td data-th="First Name">
                            <?php echo $result->first_name; ?>
                        </td>
                        <td data-th="last Number">
                            <?php echo $result->last_name; ?>
                        </td>
                        <td data-th="Email">
                            <?php echo $result->email; ?>
                        </td>
                        <td data-th="Phone">
                            <?php echo $result->phone; ?>
                        </td>
                        <td data-th="Paid Amount">
                            <?php echo $result->paid_amount; ?>
                        </td>
                        <td data-th="Transaction ID">
                            <?php echo $result->payment_intent_id; ?>
                        </td>
                        <td data-th="Created At">
                            <?php echo $result->created_at; ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php
}

function stripe_api_key()
{
    $api_key = get_option('stripe_api_key');
    return $api_key;
}
function stripe_secret_key()
{
    $secret_key = get_option('stripe_secret_key');
    return $secret_key;
}
