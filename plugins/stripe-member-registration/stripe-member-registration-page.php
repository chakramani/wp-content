<?php
// Create registration form shortcode
function highup_registration_form_shortcode()
{
    ob_start(); ?>
    <div class="container">
        <div class="title">Stripe Registration</div>
        <div class="content">
            <form method="post">
                <div class="user-details">
                    <div class="input-box">
                        <span class="details">First Name</span>
                        <input type="text" placeholder="Enter your first name" name="first_name" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Last Name</span>
                        <input type="text" placeholder="Enter your last name" name="last_name" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Email</span>
                        <input type="email" placeholder="Enter your email" name="email" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Phone Number</span>
                        <input type="tel" placeholder="Enter your number" name="phone" required />
                    </div>
                </div>
                <div class="button">
                    <button type="submit">Register</button>
                </div>
            </form>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('member_registration_form', 'highup_registration_form_shortcode');

// Handle form submission
function highup_registration_form_submission()
{
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['phone'])) {
        global $wpdb;
        // Process form data
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);


        $table_name = $wpdb->prefix . 'stripe_member_registration';
        $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE email = %s", $email);
        $email_count = $wpdb->get_var($query);




        if ($email_count > 0) {
            echo " <span class='error-msg'>Email already exists in the database.</span> ";
            return;
        } else {
            // Email doesn't exist, proceed with insertion

            $insert_data = highup_store_registration_data($first_name, $last_name, $email, $phone, NULL);
            $payment_intent_id = highup_process_stripe_payment();

            
        }
    }
}
add_action('init', 'highup_registration_form_submission');



// Process Stripe payment
function highup_process_stripe_payment()
{
    // Process payment using Stripe API
    // Set up Stripe using your API keys
    require_once plugin_dir_path(__FILE__) . 'stripe-php/init.php';

    $stripe = new \Stripe\StripeClient(stripe_secret_key());

    $checkout_session = $stripe->checkout->sessions->create([
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'User Registration',
                ],
                'unit_amount' => 10000,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://highup.local/success?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://highup.local/cancel',
    ]);

    header("HTTP/1.1 303 See Other");
    header("Location: " . $checkout_session->url);
}

// Store registration and payment data in the database
function highup_store_registration_data($first_name, $last_name, $email, $phone, $payment_intent_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'stripe_member_registration';

    // Insert data into the table
    $insert = $wpdb->insert($table_name, array(
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'phone' => $phone,
        'paid_amount' => 100,
        'payment_intent_id' => $payment_intent_id,
        'created_at' => current_time('mysql'),
    ));

    return $insert;
}


add_shortcode('stripe_paymet_success','stripe_paymet_success' );
function stripe_paymet_success() {
    session_start();
    require_once plugin_dir_path(__FILE__) . 'stripe-php/init.php';

    $stripe = new \Stripe\StripeClient(stripe_secret_key());
    try {
        $session = $stripe->checkout->sessions->retrieve($_GET['session_id']);
        if ($session->status == 'complete' && $session->payment_status == 'paid') {
            $payment_intent = $session->payment_intent;
            global $wpdb;
            $table_name = $wpdb->prefix . 'stripe_member_registration';
            $query = $wpdb->prepare("SELECT * FROM $table_name ORDER BY id DESC LIMIT 1");
            $res = $wpdb->get_results($query);

            $id = $res[0]->id;
            
            // Construct the SQL query
            $query = $wpdb->prepare("UPDATE $table_name SET payment_intent_id = %s WHERE id = %d", $payment_intent, $id);
            
            // Execute the query
            $result = $wpdb->query($query);
            if ($result !== false) {
                echo " <span class='thankyou-msg'>Thank you your payment is successful</span> ";
            } else {
                echo " <span class='error-msg'>Error updating payment intent ID: ". $wpdb->last_error."</span> ";
            }
        }
        http_response_code(200);
    } catch (Error $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
