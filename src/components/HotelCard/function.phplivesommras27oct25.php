<?php
// Enqueue Styles 
// $currentUser = wp_get_current_user();
// if ( $current_user && ! current_user_can( 'administrator' ) ) {

//     if(isset($_GET['action']) == 'rp')
//     {
// 	// Redirect users to the homepage or a custom page
//     $redirect_url = 'https://www.sommras.com/';
    
//     header("Location: $redirect_url");
//     exit;
	
//    }
// }


//add_action('template_redirect', 'custom_role_based_redirect_without_hook');

function custom_role_based_redirect_without_hook() {
    // Check if the user is not logged in and not on the redirect page
    if (!is_user_logged_in()) {
        // Check if the current page is the homepage
       /* if (is_front_page() || is_home()) {
            // Redirect to the specified URL for non-logged-in users
            wp_redirect('https://sommras.com/sommras-wines-discover-fine-wine-spirits-liquor-warehouse/');
            exit;
        }*/
    } elseif (is_user_logged_in() && is_page('sommras-wines-discover-fine-wine-spirits-liquor-warehouse')) {
        // Redirect logged-in users away from the redirect page to a different URL
        wp_redirect('https://sommras.com/my-account/');
        exit;
    }

}

if ( ! function_exists('tw_enqueue_styles') ) {
    function tw_enqueue_styles() {
        wp_enqueue_style( 'twenty-twenty-two-style', get_template_directory_uri() .'/style.css?v=1.1' );
    }
    add_action('wp_enqueue_scripts', 'tw_enqueue_styles');
}

add_action( 'template_redirect', 'checkout_redirect_non_logged_to_login_access');
function checkout_redirect_non_logged_to_login_access() {
    
    $unlock =  get_field('unlock_website', 'option');
   // print_r('<pre>'); print_r($unlock); print_r('</pre>');
   
    //check for website unlocked
    if (count($unlock) == 0) {
   
    // Here the conditions (woocommerce checkout page and unlogged user)
    if(  !is_user_logged_in() && !is_page_template( 'template-mainLP.php' ) && !is_account_page() && !is_page('contact-for-membership')  && !is_page('reservation') && !is_page('luxury-in-house-wine-experience') && !is_page('vineyard-experience') && !is_page('tonyhsiehaward') && !is_page('vineyard-experience/event-form') ){ 

        wp_redirect( get_permalink( get_option('woocommerce_myaccount_page_id') ) );
        exit;
    }
    
    }
}

//add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 20);

// The shortcode function
function fun_showproducts() { 
  
 $data = '';

if( have_rows('featured_products') ) { $i = 1;
    while( have_rows('featured_products') ) {
        the_row();
        
        if($i == 1)  $data .= '<div class="wp-block-columns is-layout-flex wp-container-4 wp-block-columns-is-layout-flex"> ';
         $data .='<div class="product type-product wp-block-column is-layout-flow wp-block-column-is-layout-flow ">
	 <img  src="'.get_sub_field('image').'" class="woocommerce-placeholder wp-post-image" alt="Placeholder" loading="lazy">
    <p><strong> '.get_sub_field('product_name').'</strong><br />
     '.get_sub_field('price_and_other_info').'</p>
     
     </div>'; 
     
     if($i == 4){ $data .= '</div>'; $i=0;}
     $i++;
         
    }
    
    if($i != 1 ) $data .= '</div>';
}

return $data; 
  
}
// Register shortcode
add_shortcode('showproducts', 'fun_showproducts'); 

// display an 'Out of Stock' label on archive pages
add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_stock', 10 );
function woocommerce_template_loop_stock() {
    global $product;
    if ( ! $product->managing_stock() && ! $product->is_in_stock() )
        echo '<p class="stock out-of-stock">Out of Stock</p>';
}


add_filter( 'body_class', function( $classes ) {
	$user = wp_get_current_user();
	$roles = $user->roles;
    return array_merge( $classes, $roles );
} );

// Hook into the login action and perform role-based redirection
add_action('wp_login', 'custom_role_based_redirect', 10, 2);

function custom_role_based_redirect($user_login, $user) {
    $user_roles = $user->roles;

    // Check if the user has the 'pos' role
    if (in_array('pos', $user_roles)) {
        $site_url = site_url();
        wp_redirect($site_url.'/product-category/wine/');
        exit;
    }
}
add_action('woocommerce_product_query', 'wpdd_limit_shop_categories');

function wpdd_limit_shop_categories($q) {
    // Check if it's the main shop page
    if (is_shop()) {
        $tax_query = (array)$q->get('tax_query');

        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array('wine'),
            'include_children' => true,
        );

        $q->set('tax_query', $tax_query);
    }
}

function my_custom_menu() {
    register_nav_menu('my-custom-menu',__( 'My Custom Menu' ));
}
add_action( 'init', 'my_custom_menu' );

/*Hook to add money in wallet automatically when user buy subscription */

add_action('woocommerce_new_order', 'after_specific_product_purchased', 10, 2);
function after_specific_product_purchased($order_id, $order) {
    

    // Get the order object
   // $order = wc_get_order($order_id);

    // Check if the order contains a specific product (replace 'your_product_id' with the actual product ID)
    $specific_product_id = array(587, 591, 592); // Change this to your actual product ID
    $product_found = false;

    foreach ($order->get_items() as $item) {
        //if ($item->get_product_id() == $specific_product_id) {
        if (in_array($item->get_product_id(), $specific_product_id)) {
        

            $product_found = true;
            // Get the product object
            $product = $item->get_product();

            // Get the product price
            $product_price = $product->get_price();
            break;
        }
    }

    // If the specific product is found in the order, perform your custom actions
    if ($product_found) {
         
       if ( class_exists( 'WooWallet' ) ) {
	
	 $current_user = wp_get_current_user();

	  WooWallet::instance()->wallet->credit( $current_user->ID, (float)$product_price , 'Wallet Filled with subscription' );
	}

    }
}

/* 
Reason: This code is used to active Edit orders feature for completed orders to calculate taxes 
Added By: Shabnam
Date: 02-01-2024
*/
add_filter( 'wc_order_is_editable', 'make_order_editable', 9999, 2 );
     
    function make_order_editable( $allow_edit, $order ) {
        if ( $order->get_status() === 'completed'  ||  $order->get_status() === 'refunded' || $order->get_status() === 'processing' ) {
            $allow_edit = true;
        }
        return $allow_edit;
    }



function custom_logo_link($html) {
    $new_logo_link = home_url('/').'sommras-your-gateway-to-exceptional-wine-experiences/';

    // Find the existing logo link
    $existing_logo_link = home_url('/');

    // Replace the existing link with the new link
 if ( is_user_logged_in() ) {
   $html = str_replace('href="'.$existing_logo_link,'href="'. $new_logo_link, $html);
 }
	else{
		
		  $html = str_replace('href="'.$existing_logo_link,'href="'. $existing_logo_link, $html);
		
	}
    return $html;
}

add_filter('get_custom_logo', 'custom_logo_link');


function custom_wp_mail_from($email) {
    return 'info@sommras.com';
}

function custom_wp_mail_from_name($name) {
    return 'Sommras';
}

add_filter('wp_mail_from', 'custom_wp_mail_from');
add_filter('wp_mail_from_name', 'custom_wp_mail_from_name');


// Remove the Downloads tab
function custom_remove_downloads_tab($items) {
    unset($items['wps_subscriptions']);
    return $items;
}

add_filter('woocommerce_account_menu_items', 'custom_remove_downloads_tab');


function shipment_details_users($type) {
    global $last_shippment_date, $last_shipment_details;
   
    $user_id = get_current_user_id(); 
    if($type == 'date')
    return   $last_shippment_date  = get_user_meta( $user_id, 'last_shippment_date', true );
    if($type == 'detail') return $last_shipment_details = get_user_meta( $user_id, 'last_shipment_details', true );
}

// Hook the function to a specific action, for example, init
add_action('init', 'shipment_details_users');

/*
// Schedule a cron job to run every six hours
function custom_schedule_cron() {
    if ( ! wp_next_scheduled( 'Vinfilment_cron_hook' ) ) {
        wp_schedule_event( time(), 'every_three_hours', 'Vinfilment_cron_hook' );
    }
}


// Define a custom time interval for every six hours
function custom_add_three_hours_interval( $schedules ) {
    $schedules['every_three_hours'] = array(
        'interval' => 10800, // 3 hours in seconds
        'display'  => __( 'Every Three Hours' ),
    );
    return $schedules;
}

// Hook into the filter to add the custom time interval
add_filter( 'cron_schedules', 'custom_add_three_hours_interval' );



// Hook into the custom_cron_hook action
add_action( 'Vinfilment_cron_hook', 'Vinfilment_cron_function' );

// Hook into the action when the WordPress system is loaded
add_action( 'wp', 'custom_schedule_cron' );
*/
// Define the function to be executed by the cron job
/* 
 * function Vinfilment_cron_function() {     
    wp_mail( 'shabnam@envyusmediaindia.com', 'Vinfillment Cron Job Executed', 'The Vinfillment cron job has been executed.' );
    wp_mail( 'parveen@envyusmediaindia.com', 'Vinfillment Cron Job Executed', 'The Vinfillment cron job has been executed.' );

    
    
	 
	 
   // Get current time in UTC
$current_time_utc = current_time('timestamp');

// Calculate the datetime 6 hours ago
 $six_hours_ago_utc = date('Y-m-d H:i:s', strtotime('-6 hours', $current_time_utc));

// Query parameters for fetching orders placed in the last 6 hours
$args = array(
    'status'         =>  array('wc-completed'),
   'date_created'   => '>=' . ( time() - 10800 ), // 6 hours ago (6 hours * 60 minutes * 60 seconds)
    'date_modified'  => '>=' . ( time() - 10800 ), // 6 hours ago (6 hours * 60 minutes * 60 seconds)  
   
);

// Get orders based on the query parameters
$orders = wc_get_orders($args);

// Loop through the orders
foreach ($orders as $order) {
    $order_id = $order->get_id();
 
    // Example: Get the order details
    $order =  $order = wc_get_order($order_id);
    $order_data = $order->get_data();
    $billing_address = $order_data['billing'];
    
    
    // Check if the order exists
if ($order) {
    
	$items = $order->get_items();

    $itemsdata = '';

      // Loop through each item
    foreach ($items as $item_id => $item) {
        // Get product information
        $product_id = $item->get_product_id();
        $product = wc_get_product($product_id);
        
        $itemsdata .= ' <Item>

 <ItemSku>' . $product->get_sku() . '</ItemSku>

 <ItemDescription>' . $product->get_name() . '</ItemDescription>

 <Quantity>' . $item->get_quantity() . '</Quantity>

 </Item>';
    }
	
	

    
    
    
    
$apiUrl = 'https://1151066.restlets.api.netsuite.com/app/site/hosting/restlet.nl?script=391&deploy=1';



$oauthCredentials = [

    'consumer_key' => 'daf7b22039319dec5b850c1b3fefa13d4129f3fe40b71243e1e7472df578f838',

    'consumer_secret' => '9e054f5b92079a9e84935e23e9db08638ba489344faef0fd1b105ad4da07c41c',

    'token' => '53a89a93dcfe5635a62c459683944c5a51133d03b4f737887925057fc0dd6a1a',

    'token_secret' => '6bd7e944b96894a88f7f2756b5626152fd195a4c7616b55b0a7312a5ecc0c1ac',

];





// Generate OAuth nonce and timestamp

$oauthNonce = md5(mt_rand());

$oauthTimestamp = time();





// Set up OAuth parameters

$oauthParameters = [

     'realm' => '1151066',

    'oauth_consumer_key' => $oauthCredentials['consumer_key'],

    'oauth_nonce' => $oauthNonce,

     'oauth_signature_method' => '"HMAC-SHA256"',

    'oauth_timestamp' => $oauthTimestamp,

    'oauth_token' => $oauthCredentials['token'],

    'oauth_version' => '1.0',

];


//print_r('<pre>'); print_r($order); print_r('</pre>');



//print_r('<pre>'); print_r($order_data); print_r('</pre>');


$data = '<?xml version="1.0" encoding="UTF-8"?>

<Orders>

 <Order>

 <Winery>SOM</Winery>

 <OrderNumber>'.$order_id.'</OrderNumber>

 <RecipientName>'.$order_data['billing']['first_name'].'  '.$order_data['billing']['last_name'].' </RecipientName>

 <CompanyName> '.$order_data['billing']['company'].'</CompanyName>

 <AddressLine1> '.$order_data['billing']['address_1'].'</AddressLine1>

 <AddressLine2>'.$order_data['billing']['address_2'].'</AddressLine2>

 <City> '.$order_data['billing']['city'].'</City>

 <State> '.$order_data['billing']['state'].'</State>

 <Zip> '.$order_data['billing']['postcode'].'</Zip>

 <Country> '.$order_data['billing']['country'].'</Country>

 <ShipMethod>FXO</ShipMethod>

 <ShipDate>'.date('m/d/Y').' </ShipDate>

 <GiftMessage></GiftMessage>

 <SpecialInstructions>

'.$order_data['customer_note'].'
 </SpecialInstructions>

 <Ice>True</Ice>

 <Phone>'.$order_data['billing']['phone'].'</Phone>

 <Email>'.$order_data['billing']['email'].'</Email>

'.$itemsdata.'


 </Order>

</Orders>

';



$allParameters = $oauthParameters;

 

// Construct the base string

$baseString = 'POST&' . rawurlencode($apiUrl) . '&' . rawurlencode(http_build_query($allParameters, '', '&', PHP_QUERY_RFC3986));



// Construct the signing key

$signingKey = rawurlencode($oauthCredentials['consumer_secret']) . '&' . rawurlencode($oauthCredentials['token_secret']);



// Generate the OAuth signature

$oauthSignature = base64_encode(hash_hmac('sha256', $baseString, $signingKey, true));



// Add the signature to the OAuth parameters

$oauthParameters['oauth_signature'] = $oauthSignature;






// Set up cURL

$ch = curl_init($apiUrl);



// Set cURL options for a POST request

curl_setopt($ch, CURLOPT_VERBOSE, true);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$realm = "1151066";
$ckey = "daf7b22039319dec5b850c1b3fefa13d4129f3fe40b71243e1e7472df578f838"; //Consumer Key
$csecret = "9e054f5b92079a9e84935e23e9db08638ba489344faef0fd1b105ad4da07c41c"; //Consumer Secret
$tkey = "53a89a93dcfe5635a62c459683944c5a51133d03b4f737887925057fc0dd6a1a"; //Token ID
$tsecret = "6bd7e944b96894a88f7f2756b5626152fd195a4c7616b55b0a7312a5ecc0c1ac"; //Token Secret
$key = rawurlencode($csecret) . '&' . rawurlencode($tsecret);

$baseString = 'POST&' . rawurlencode("https://1151066.restlets.api.netsuite.com/app/site/hosting/restlet.nl") . "&"
        . rawurlencode("deploy=1&oauth_consumer_key=" . rawurlencode($ckey)
            . "&oauth_nonce=" . rawurlencode($oauthNonce)
            . "&oauth_signature_method=HMAC-SHA256"
            . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
            . "&oauth_token=" . rawurlencode($tkey)
            . "&oauth_version=1.0"
            . "&script=391"
        );
$signature = rawurlencode(base64_encode(hash_hmac('sha256', $baseString, $key, true)));

curl_setopt($ch, CURLOPT_HTTPHEADER, [

    'content-type: text/plain',

    "Authorization: OAuth realm=\"$realm\", oauth_consumer_key=\"$ckey\", oauth_token=\"$tkey\", oauth_nonce=\"$oauthNonce\", oauth_timestamp=\"$oauthTimestamp\", oauth_signature_method=\"HMAC-SHA256\", oauth_version=\"1.0\", oauth_signature=\"$signature\"",

]);



// Execute cURL and get the response

$response = curl_exec($ch);



// Check for cURL errors

if (curl_errno($ch)) {

   // echo 'Curl error: ' . curl_error($ch);

}



// Close cURL

curl_close($ch);

    
    

    // Example: Send a custom email
    wp_mail('shabnam@envyusmediaindia.com', 'Vinfillement API HIT -- '.$order_id.'', $data.'<br />=====<br />'. $response);
	}
}  
}
*/




function disable_membership_fields_validation() {
    
if (strpos($_SERVER['REQUEST_URI'], 'pos') !== false) {
    // The specific word is present in the URL


        // Remove the specific action hook
        remove_action('woocommerce_register_post', array(af_wum()->registration, 'validate_membership_fields_data'), 10, 3);
    }
}

// Hook the function to an early action hook
add_action('init', 'disable_membership_fields_validation');


function delete_unused_user_roles() {
    $roles_to_delete = array('pms_subscription_plan_308', 'pms_subscription_plan_307','pos');

    foreach ($roles_to_delete as $role) {
        remove_role($role);
    }
}

// Hook the function to an action, such as 'init' or 'admin_init'
add_action('init', 'delete_unused_user_roles');


function custom_admin_styles() {
    $screen = get_current_screen();
    if ( $screen->id === 'user-edit' || $screen->id === 'profile' ) { // Check if on user edit or profile page
        ?>
        <style>
           
            .user-facebook-wrap, .user-instagram-wrap, .user-linkedin-wrap, .user-myspace-wrap, .user-pinterest-wrap, .user-soundcloud-wrap, .user-tumblr-wrap, .user-twitter-wrap, .user-wikipedia-wrap, .user-youtube-wrap, .application-passwords, .yoast.yoast-settings { 
                display: none !important;
            }
        </style>
        <?php
    }
}
add_action( 'admin_head', 'custom_admin_styles' );




/*Shipment Module Code */



function add_product_to_user_cart( $user_id, $product_id, $quantity ) {
    // Check if WooCommerce is active
    if ( ! class_exists( 'WooCommerce' ) ) {
        return false;
    }

    // Get the cart instance
    $cart = WC()->cart;

    // Check if the user ID is valid
    if ( ! is_numeric( $user_id ) || $user_id <= 0 ) {
        return false;
    }

    // Check if the product ID is valid
    if ( ! is_numeric( $product_id ) || $product_id <= 0 ) {
        return false;
    }

    // Check if the quantity is valid
    if ( ! is_numeric( $quantity ) || $quantity <= 0 ) {
        return false;
    }

    // Check if the user exists
    $user = get_user_by( 'id', $user_id );
    if ( ! $user ) {
        return false;
    }

    // Add the product to the cart
    $cart_item_key = $cart->add_to_cart( $product_id, $quantity );

    // Return the cart item key
    return $cart_item_key;
}

// Function to get WordPress post ID given the post title
function bl_get_post_id_by_title( string $title = '' ): int {
    $posts = get_posts(
        array(
            'post_type'              => 'product',
            'title'                  => $title,
            'numberposts'            => 1,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
            'orderby'                => 'post_date ID',
            'order'                  => 'ASC',
            'fields'                 => 'ids'
        )
    );

    return empty( $posts ) ? get_the_ID() : $posts[0];
}




// Add new menu item to WordPress admin
function custom_admin_menu() {
    // Add a new top-level menu item
    add_menu_page(
        'Allocation', // Page title
        'Allocation', // Menu title
        'manage_options', // Capability required to access
        'shipment', // Menu slug (unique identifier)
        'shipment_page', // Function to render page content
        'dashicons-admin-generic' // Icon URL or dashicon class
    );
    
     add_submenu_page( 'shipment', 
     'Allocation Details', // Page title
        'Allocation Details', // Menu title
        'manage_options', // Capability required to access
        'shipment-detail', // Menu slug (unique identifier)
        'shipment_detail_page', // Function to render page content
        'dashicons-admin-generic' // Icon URL or dashicon class);
        );
      
}
add_action( 'admin_menu', 'custom_admin_menu' );




function shipment_detail_page() {
    
    if(isset($_REQUEST['user_id']) && isset($_REQUEST['entry_id']) )
    {
        $user_id = $_REQUEST['user_id']; 
        $field_id = 112; 
        $entry_id = $_REQUEST['entry_id']; 
        

        $product_name = FrmProEntriesController::get_field_value_shortcode(array('field_id' => 112, 'entry' => $_REQUEST['entry_id']));
        $product_name = explode(',', $product_name); 


        $qty = FrmProEntriesController::get_field_value_shortcode(array('field_id' => 113, 'entry' => $_REQUEST['entry_id']));
        $qty = explode(',', $qty);


        // Create a new order
          //  $order = wc_create_order();
            //$order->set_customer_id( $user_id );
           // $order = wc_create_order(array('customer_id'=>$user_id));
            //print_r('<pre>'); print_r($order); print_r('</pre>');


        $i = 0; 
        foreach ($product_name as $product) {
                 $product_id = bl_get_post_id_by_title($product);
                 if($product_id)
                 {
                     //$order->add_product( wc_get_product($product_id), $qty[$i] );
                   //  $cart_item_key = WC()->cart->add_to_cart( $product_id, $qty[$i] );
                   
                   
                   
                   // Add the product to the selected customer's cart
                   // $cart_item_key = WC()->cart->add_to_cart( $product_id, $qty[$i], 0, array(), $user_id );

                    // Provide feedback to the user based on whether the product was successfully added to the customer's cart or not
                  
             
                 }
           
              
              
        $i++; 
        }
   //$order->save();
 if ( method_exists( 'user_switching', 'maybe_switch_url' ) ) {
 
 	$target_user = get_userdata( $user_id );
      echo $url = user_switching::maybe_switch_url( $target_user );
      
      header("Location: $url"); /* Redirect browser */

    
  //  wp_redirect(urldecode($url));
            exit;
 
}


           
 

    }
    else
    {
    
            echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <h2>Allocation Details</h2>';
            
            if(isset($_GET['entry'])){ 
                echo '<p><a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=shipment"><strong>BACK</strong></a></p>';
                }
            
            
            echo do_shortcode('[display-frm-data id=1492 filter=limited]');
    
    } 
    
}





// Render the custom menu page
function shipment_page() {
    echo '<div class="wrap"> <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
';
   echo "	<link rel='stylesheet' id='formidable_admin_global-css' href='https://sommras.com/wp-content/plugins/formidable/css/admin/frm_admin_global.css?ver=6.7' media='all' />
			<link rel='stylesheet' id='formidable-css' href='https://sommras.com/wp-content/plugins/formidable/css/formidableforms.css?ver=12142111' media='all' />

<h1>Shipment Management</h1>";
   
   if(!isset($_GET['entry'])){ 
   echo '<style>
    /* Style to make iframe full width and height */
    iframe {
        width: 100%;
        height: 100vh; /* 100% of viewport height */
        border: none; /* Remove iframe border */
    }
</style>
<iframe src="'.get_bloginfo('url').'/wp-admin/admin.php?page=formidable-entries&frm_action=new&form=13" frameborder="0" id="external-frame"></iframe>

 <script>
        window.onload = function() {
            var iframe = document.getElementById(\'external-frame\');
            var iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
            
            // Check if the iframe document is loaded
            if (iframeDocument) {
                // Hide all elements except the desired div
                var divToDisplay = iframeDocument.getElementById(\'post-body-content\');
                iframeDocument.getElementById(\'adminmenumain\').style.display = \'none\';;
                iframeDocument.getElementById(\'wpadminbar\').style.display = \'none\';;
                iframeDocument.getElementById(\'frm_top_bar\').style.display = \'none\';;
                iframeDocument.getElementById(\'frm_field_122_container\').style.display = \'none\';;
                iframeDocument.getElementsByClassName(\'frm-admin-footer-links\').style.display = \'none\';;
     
            }
        };
    </script>';
    
    }
    else
    {
        echo '<p><a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=shipment"><strong>BACK</strong></a></p>';
        }
   echo '
    
    <h2>Allocation Details</h2>



';

echo do_shortcode('[display-frm-data id=1484 filter=limited]');
   
   
    
echo '</div>';
}





// Get Formidable entry ID by user ID
function get_formidable_entry_id_by_user_id($user_id, $form_id) {
    global $wpdb;

    // Query the Formidable table to get the entry ID based on user ID and form ID
    $entry_id = $wpdb->get_var(
        $wpdb->prepare("
            SELECT id
            FROM {$wpdb->prefix}frm_items
            WHERE user_id = %d AND form_id = %d
            LIMIT 1",
            $user_id,
            $form_id
        )
    );

    return $entry_id;
}


add_shortcode( 'duplicate_entry_form', 'frm_duplicate_entry_form' );
function frm_duplicate_entry_form( $atts ) {
    global $wpdb;
 
    $current_user_id = get_current_user_id();
    $form_id = 13; 
    $entry_id = get_formidable_entry_id_by_user_id($current_user_id, $form_id);
 

if ($entry_id) {
}
else{
      
  $old_id = $atts['entry_id'];
  if ( ! empty( $old_id ) && ! is_numeric( $old_id ) ) {
    $old_id = absint( $_GET[ $old_id ] );
  }
  $user_id_field = absint( $atts['field_id'] );
  /* add_filter( 'frm_add_entry_meta', 'autoincrement_on_duplicate' );*/ //Un-comment this line if you have auto increment field in your form
  $new_entry_id = FrmEntry::duplicate( $old_id );
  /* remove_filter( 'frm_add_entry_meta', 'autoincrement_on_duplicate' );*/  //Un-comment this line if you have auto increment field in your form

  // set the new entry to the ID of the current user
  global $wpdb;
  $wpdb->update( $wpdb->prefix .'frm_items', array( 'user_id' => get_current_user_id() ), array( 'id' => $new_entry_id ) );
  $wpdb->update( $wpdb->prefix .'frm_item_metas', array( 'meta_value' => get_current_user_id() ), array( 'item_id' => $new_entry_id, 'field_id' => $user_id_field ) );
  
   $wpdb->update( $wpdb->prefix .'frm_item_metas', array( 'meta_value' => '' ), array( 'item_id' => $new_entry_id, 'field_id' => '138' ) );
  
  
} 
    return FrmFormsController::get_form_shortcode( array( 'id' => 13 ) );
 
    
  
  //return FrmFormsController::get_form_shortcode( array('id' => $atts['id'], 'entry_id' => $new_entry_id ) ); 
}


// ------------------
// 1. Register new endpoint (URL) for My Account page
// Note: Re-save Permalinks or it will give 404 error
  
function bbloomer_add_shipment_endpoint() {
    add_rewrite_endpoint( 'shipment', EP_ROOT | EP_PAGES );
}
  
add_action( 'init', 'bbloomer_add_shipment_endpoint' );
  
// ------------------
// 2. Add new query var
  
function bbloomer_shipment_query_vars( $vars ) {
    $vars[] = 'shipment';
    return $vars;
}
  
add_filter( 'query_vars', 'bbloomer_shipment_query_vars', 0 );
  
// ------------------
// 3. Insert the new endpoint into the My Account menu
  
function bbloomer_add_shipment_link_my_account( $items ) {
    $items['shipment'] = 'Allocation';
    return $items;
}
  
add_filter( 'woocommerce_account_menu_items', 'bbloomer_add_shipment_link_my_account' );
  
// ------------------
// 4. Add content to the new tab
  
function bbloomer_shipment_content() {
  
     if(!isset($_GET['staging']))
        {
    
        global $wpdb;
        
        $current_user = wp_get_current_user();


$results = $wpdb->get_results(
    "SELECT `item_id` FROM `{$wpdb->prefix}frm_item_metas` WHERE `meta_value` LIKE '%{$current_user->user_email}%' AND `field_id` = '138' LIMIT 1"
);

if ( $results ) {
    foreach ( $results as $row ) {

        echo do_shortcode('[duplicate_entry_form id=13 entry_id='.$row->item_id.' field_id=122]');
        
        break;
    }
}  

          
        }
        else
        {
                 echo '<p>Coming Soon</p>';
            }
            
}
  
add_action( 'woocommerce_account_shipment_endpoint', 'bbloomer_shipment_content' );
// Note: add_action must follow 'woocommerce_account_{your-endpoint-slug}_endpoint' format


add_filter('frm_setup_new_fields_vars', 'populate_select_options', 10, 2);
add_filter('frm_setup_edit_fields_vars', 'populate_select_options', 20, 2); //use this function on edit too
function populate_select_options($values, $field) {
    if ($field->id == 138) { // Replace YOUR_SELECT_FIELD_ID with the ID of your select field
       // WP_Query arguments
$args = array(
    'post_type' => 'af_member', // Replace 'your_custom_post_type' with the name of your custom post type
    'posts_per_page' => -1, // Set the number of posts to display. Use -1 to display all posts
);

// The Query
$query = new WP_Query( $args );

$memberarr = array();

// The Loop
if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
        $query->the_post();
        
        $planid = get_post_meta( get_the_ID(), 'af_member_plan', true );
		 $uid = get_post_meta( get_the_ID(), 'afwum_member_user', true );
		$page_title = get_the_title( $planid );
        
        
        $user = get_user_by( 'ID', $uid );
        $email = $user->user_email;
		
		$memberarr[$page_title][] = array('id' =>  $email, 'title' =>  get_the_title()); 
	 
    }
} 
// Restore original Post Data
wp_reset_postdata();

        // Generate select options
        $options = array();
        foreach ( $memberarr as $category => $posts ) {
            $options[] = array('label' =>  $category ,'values' => '');
            foreach ( $posts as $post ) {
           
                $options[] = array('label' => '&nbsp; &nbsp;'.$post['title'], 'value' => $post['id']);
            }
            
        }
         $values['options'] = $options;
    }

    return $values;
}
 



// Define a function to create a nonce and return it as a shortcode
function my_generate_nonce_shortcode( $atts ) {
    // Set the default attributes and merge them with the user input
    $atts = shortcode_atts( array( 'action' => 'switch_to_user' ), $atts, 'generate_nonce' );

    // Create a nonce with the action name from the attributes
    $nonce = wp_create_nonce( $atts['action'] );

    // Escape and return the nonce
    return esc_html( $nonce );
}

// Register the shortcode
add_shortcode( 'generate_nonce', 'my_generate_nonce_shortcode' );



// Define a function to create a nonce and return it as a shortcode
function my_shipmetlink_shortcode( $atts ) {
    // Set the default attributes and merge them with the user input
    $atts = shortcode_atts( array( 'id' => '' ), $atts );
    $data = '';

   if($atts['id'] )
   {
    $user_id = $atts['id'];
         if ( method_exists( 'user_switching', 'maybe_switch_url' ) ) {
             
                $target_user = get_userdata( $user_id );
                  $data = user_switching::maybe_switch_url( $target_user );
                  
                  
             
            }
       
    }

    // Escape and return the nonce
    return esc_html( $data );
}

// Register the shortcode
add_shortcode( 'shipmetlink', 'my_shipmetlink_shortcode' );




function add_custom_content_to_my_account_page() {
   
  if ( function_exists( 'current_user_switched' ) ) {
    $switched_user = current_user_switched();
    if ( $switched_user ) {
    
        if(isset($_REQUEST['task']))
        {
            global $wpdb;
            
              $user_id = get_current_user_id(); 
             $form_id = 13; 
             
             // Prepare SQL query to retrieve entry ID
                $query = $wpdb->prepare( "
                    SELECT id
                    FROM {$wpdb->prefix}frm_items
                    WHERE user_id = %d
                    AND form_id = %d
                    LIMIT 1
                ", $user_id, $form_id );

                // Execute query
                  $entry_id = $wpdb->get_var( $query );
               
               

                $field_id = 112; 
               
                

                $product_name = FrmProEntriesController::get_field_value_shortcode(array('field_id' => 112, 'entry' => $entry_id));
                $product_name = explode(',', $product_name); 


                $qty = FrmProEntriesController::get_field_value_shortcode(array('field_id' => 113, 'entry' => $entry_id));
                $qty = explode(',', $qty);


              

                $i = 0; 
                foreach ($product_name as $product) {
                          $product_id = bl_get_post_id_by_title($product); 
                         if($product_id)
                         {
                             
                        // Add the product to the cart
                       $cart_item_key = WC()->cart->add_to_cart( $product_id, $qty[$i] );

                        /* // Check if the product was successfully added to the cart
                        if ( $cart_item_key ) {
                            echo "Product added to cart successfully.";
                        } else {
                            echo "Failed to add product to cart.";
                        } */
                         }
                   
                      
                      
                $i++; 
                }
 
    echo '<h3>Products has been loaded in Cart. Please proceed with <a href="'.wc_get_checkout_url().'">Checkout</a></h3>';
    
        }
        else
        {
            echo '<a href="'.get_bloginfo('url').'/my-account/?task=shipment" class="btn">Checkout Allocation</a>';
        }
    }
    
    
}


}
add_action( 'woocommerce_account_dashboard', 'add_custom_content_to_my_account_page' );


function mytheme_register_menus() {
    register_nav_menus( array(
        'my-custom-menu'          => __( 'My Custom Menu', 'mytheme' ),
         'primary'          => __( 'Primary Menu', 'mytheme' ),
        'main_navigation'  => __( 'Main Navigation', 'mytheme' ),
    ) );
}
add_action( 'after_setup_theme', 'mytheme_register_menus' );

// Add custom column to admin order list
function custom_add_order_delivery_column( $columns ) {
    $new_columns = array();
    foreach ( $columns as $column_name => $column_info ) {
        $new_columns[ $column_name ] = $column_info;
        if ( 'order_status' === $column_name ) {
            $new_columns['delivery_type'] = __( 'Delivery Type', 'your-textdomain' );
        }
    }
    return $new_columns;
}
add_filter( 'manage_edit-shop_order_columns', 'custom_add_order_delivery_column' );


// Populate custom column with delivery type
function custom_populate_order_delivery_column( $column ) {
    global $post;
    if ( 'delivery_type' === $column ) {
        $order = wc_get_order( $post->ID );
        $shipping_method_title = $order->get_shipping_method();
        if ( strpos( $shipping_method_title, 'Local Pickup (Main Office)' ) !== false ) {
            echo $shipping_method_title; // __( 'Pickup', 'your-textdomain' );
        } else {
            echo $shipping_method_title; // __( 'Shipping', 'your-textdomain' );
        }
    }
}
add_action( 'manage_shop_order_posts_custom_column', 'custom_populate_order_delivery_column' );


// Add a custom meta box to the order detail page
function add_shipping_date_meta_box() {
    add_meta_box(
        'shipping_date_meta_box',
        'Vinfillment Shipping Settings',
        'render_shipping_date_meta_box',
        'shop_order',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_shipping_date_meta_box');

// Render the custom meta box content
function render_shipping_date_meta_box($post) {
    // Retrieve the shipping date and vinfillment status for this order
    $vinfillment_status = get_post_meta($post->ID, 'vinfillment_status', true);

    // Check if order is already sent to vinfillment
    if ($vinfillment_status == '1') {
        $shipping_date = get_post_meta($post->ID, 'shipping_date', true);
                $formatted_shipping_date = date('m-d-Y', strtotime($shipping_date));

        ?>
        <p>Order already sent to vinfillment</p>
        <p>Shipping Date: <?php echo $formatted_shipping_date; ?></p>
        <?php
    } else {
   
        // Get the current date in YYYY-MM-DD format
        $currentDate = date('d-m-Y');
        $nextYearDate = date('d-m-Y', strtotime('+1 year'));

  
        // Output the form fields for shipping date and vinfillment status
        ?>
        <p>
            <label for="shipping_date">Shipping Date:</label>
            <input type="date" id="shipping_date" name="shipping_date" value="<?php echo $currentDate ?>" min="<?php echo $currentDate ?>" max="<?php echo $nextYearDate;?>"   >
        </p>
        <input type="hidden" name="vinfillment_status" value="0">
        <p>
            <button id="send-to-vinfillment-btn" class="button">Send to Vinfillment</button>
        </p>
        <script>
            jQuery(document).ready(function($) {
                $('#send-to-vinfillment-btn').on('click', function() {
                    // Set vinfillment status to 1
                    $('input[name="vinfillment_status"]').val('1');
                    // Save the order
                    $('#post').submit();
                });
            });
        </script>
        <?php
    }
}

// Save the shipping date and vinfillment status when the order is saved
function save_shipping_date_meta_box_data($post_id) {
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
	    $shipping_date = $_POST['shipping_date'];


    // Convert the shipping date to a timestamp
    $shipping_timestamp = strtotime($shipping_date);

    // Check if the shipping date is not 01-01-1970 and it's either today or a future date
    if ($shipping_timestamp !== false && $shipping_timestamp != 0 && $shipping_timestamp >= strtotime('today')) {
 
        update_post_meta($post_id, 'shipping_date', sanitize_text_field($_POST['shipping_date']));
        
        
          $order_id = $post_id;
          $shipping_date = date('m-d-Y', strtotime(sanitize_text_field($_POST['shipping_date'])));
 
    // Example: Get the order details
    $order =  $order = wc_get_order($order_id);
    $order_data = $order->get_data();
    $billing_address = $order_data['billing'];
    
    $shippingcode = '';
    $shipping_method_title = $order->get_shipping_method();
	
	 if (strpos($shipping_method_title, 'FedEx Ground') !== true) {
        $shippingcode = 'FXG';
    } elseif (strpos($shipping_method_title, 'FedEx 2 Day') !== true) {
        $shippingcode = 'FEX';
    } 
	 elseif (strpos($shipping_method_title, 'FedEx 3 Day') !== true) {
         $shippingcode = 'FX3';
    } 
	 elseif (strpos($shipping_method_title, 'FedEx Standard Overnight') !== true) {
         $shippingcode = 'FXO';
    } 
	 elseif (strpos($shipping_method_title, 'FedEx Priority Overnight') !== true) {
         $shippingcode = 'FXP';
    }
	elseif (strpos($shipping_method_title, 'UPS Summer Overnight Shipping') !== true) {
         $shippingcode = 'UPO';
    }
	 elseif (strpos($shipping_method_title, 'Fedex 2 Day Air') !== true) {
         $shippingcode = ' ';
    }

    
		
		
		
    
    // Check if the order exists
if ($order) {
    
	$items = $order->get_items();

    $itemsdata = '';

      // Loop through each item
    foreach ($items as $item_id => $item) {
        // Get product information
        $product_id = $item->get_product_id();
        $product = wc_get_product($product_id);
        
        $itemsdata .= ' <Item>

 <ItemSku>' . $product->get_sku() . '</ItemSku>

 <ItemDescription>' . $product->get_name() . '</ItemDescription>

 <Quantity>' . $item->get_quantity() . '</Quantity>

 </Item>';
    }
	
	

    
    
    
    
$apiUrl = 'https://1151066.restlets.api.netsuite.com/app/site/hosting/restlet.nl?script=391&deploy=1';



$oauthCredentials = [

    'consumer_key' => 'daf7b22039319dec5b850c1b3fefa13d4129f3fe40b71243e1e7472df578f838',

    'consumer_secret' => '9e054f5b92079a9e84935e23e9db08638ba489344faef0fd1b105ad4da07c41c',

    'token' => '53a89a93dcfe5635a62c459683944c5a51133d03b4f737887925057fc0dd6a1a',

    'token_secret' => '6bd7e944b96894a88f7f2756b5626152fd195a4c7616b55b0a7312a5ecc0c1ac',

];





// Generate OAuth nonce and timestamp

$oauthNonce = md5(mt_rand());

$oauthTimestamp = time();





// Set up OAuth parameters

$oauthParameters = [

     'realm' => '1151066',

    'oauth_consumer_key' => $oauthCredentials['consumer_key'],

    'oauth_nonce' => $oauthNonce,

     'oauth_signature_method' => '"HMAC-SHA256"',

    'oauth_timestamp' => $oauthTimestamp,

    'oauth_token' => $oauthCredentials['token'],

    'oauth_version' => '1.0',

];


//print_r('<pre>'); print_r($order); print_r('</pre>');



//print_r('<pre>'); print_r($order_data); print_r('</pre>');


$data = '<?xml version="1.0" encoding="UTF-8"?>

<Orders>

 <Order>

 <Winery>SOM</Winery>

 <OrderNumber>'.$order_id.'</OrderNumber>

 <RecipientName>'.$order_data['shipping']['first_name'].'  '.$order_data['shipping']['last_name'].' </RecipientName>

 <CompanyName> '.$order_data['shipping']['company'].'</CompanyName>

 <AddressLine1> '.$order_data['shipping']['address_1'].'</AddressLine1>

 <AddressLine2>'.$order_data['shipping']['address_2'].'</AddressLine2>

 <City> '.$order_data['shipping']['city'].'</City>

 <State> '.$order_data['shipping']['state'].'</State>

 <Zip> '.$order_data['shipping']['postcode'].'</Zip>

 <Country> '.$order_data['shipping']['country'].'</Country>

 <ShipMethod>'.$shippingcode.'</ShipMethod>

 <ShipDate>'.$shipping_date.' </ShipDate>

 <GiftMessage></GiftMessage>

 <SpecialInstructions>

'.$order_data['customer_note'].'
 </SpecialInstructions>

 <Ice>True</Ice>

 <Phone>'.$order_data['shipping']['phone'].'</Phone>

 <Email>'.$order_data['billing']['email'].'</Email>

'.$itemsdata.'


 </Order>

</Orders>

';



$allParameters = $oauthParameters;

 

// Construct the base string

$baseString = 'POST&' . rawurlencode($apiUrl) . '&' . rawurlencode(http_build_query($allParameters, '', '&', PHP_QUERY_RFC3986));



// Construct the signing key

$signingKey = rawurlencode($oauthCredentials['consumer_secret']) . '&' . rawurlencode($oauthCredentials['token_secret']);



// Generate the OAuth signature

$oauthSignature = base64_encode(hash_hmac('sha256', $baseString, $signingKey, true));



// Add the signature to the OAuth parameters

$oauthParameters['oauth_signature'] = $oauthSignature;






// Set up cURL

$ch = curl_init($apiUrl);



// Set cURL options for a POST request

curl_setopt($ch, CURLOPT_VERBOSE, true);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$realm = "1151066";
$ckey = "daf7b22039319dec5b850c1b3fefa13d4129f3fe40b71243e1e7472df578f838"; //Consumer Key
$csecret = "9e054f5b92079a9e84935e23e9db08638ba489344faef0fd1b105ad4da07c41c"; //Consumer Secret
$tkey = "53a89a93dcfe5635a62c459683944c5a51133d03b4f737887925057fc0dd6a1a"; //Token ID
$tsecret = "6bd7e944b96894a88f7f2756b5626152fd195a4c7616b55b0a7312a5ecc0c1ac"; //Token Secret
$key = rawurlencode($csecret) . '&' . rawurlencode($tsecret);

$baseString = 'POST&' . rawurlencode("https://1151066.restlets.api.netsuite.com/app/site/hosting/restlet.nl") . "&"
        . rawurlencode("deploy=1&oauth_consumer_key=" . rawurlencode($ckey)
            . "&oauth_nonce=" . rawurlencode($oauthNonce)
            . "&oauth_signature_method=HMAC-SHA256"
            . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
            . "&oauth_token=" . rawurlencode($tkey)
            . "&oauth_version=1.0"
            . "&script=391"
        );
$signature = rawurlencode(base64_encode(hash_hmac('sha256', $baseString, $key, true)));

curl_setopt($ch, CURLOPT_HTTPHEADER, [

    'content-type: text/plain',

    "Authorization: OAuth realm=\"$realm\", oauth_consumer_key=\"$ckey\", oauth_token=\"$tkey\", oauth_nonce=\"$oauthNonce\", oauth_timestamp=\"$oauthTimestamp\", oauth_signature_method=\"HMAC-SHA256\", oauth_version=\"1.0\", oauth_signature=\"$signature\"",

]);



// Execute cURL and get the response

$response = curl_exec($ch);



// Check for cURL errors

if (curl_errno($ch)) {

   // echo 'Curl error: ' . curl_error($ch);

}



// Close cURL

curl_close($ch);
 // Example: Send a custom email
    wp_mail('shabnam@envyusmediaindia.com', 'Vinfillement API HIT -- '.$order_id.'', $data.'<br />=====<br />'. $response);
  
    }
    }

    // Save vinfillment status
    if (isset($_POST['vinfillment_status'])) {
        update_post_meta($post_id, 'vinfillment_status', sanitize_text_field($_POST['vinfillment_status']));
    }
}
add_action('save_post', 'save_shipping_date_meta_box_data');


    /* Formidable forms hook to validate form entries dynamically */
    add_filter('frm_validate_field_entry', 'validate_user_credentials', 10, 3);
    function validate_user_credentials( $errors, $field, $posted_value ){      
        if ( $field->id == 142  && $posted_value != '') {
            /* Check if user entered username already exists. If yes then we show an error message */
            if ( !email_exists( $posted_value ) ) {
                //Error message if username already exists
                $errors['field' . $field->id] = 'Only Registered users allowed to Book Reservation';
            }
        }
        
        return $errors;
    }


//add_filter( 'woocommerce_states', 'custom_disable_states_checkout' );

function custom_disable_states_checkout( $states ) {
    // Define the states for which you want to disable checkout
    $disabled_states = array(
        'AR', // Example: New York
        'DE',
        'MS',
        'OK',
        'UT',
        'AA',
        'AE',
        'AP',
        'AL',
'IL',
'KS',
'KY',
'LA',
'MD',
'MA',
'MI',
'NH',
'NJ',
'ND',
'PA',
'SD',
'TN',
'VA',
'WV',
'WI',
        // Add more states as needed
    );

    // Loop through each state and unset it from the $states array
    foreach ( $disabled_states as $state_code ) {
        if ( isset( $states['US'][$state_code] ) ) {
            unset( $states['US'][$state_code] );
        }
    }

    return $states;
}


add_action( 'pre_user_query', function( $uqi ) {
    global $wpdb;

    $search = '';
    if ( isset( $uqi->query_vars['search'] ) )
        $search = trim( $uqi->query_vars['search'] );

    if ( $search ) {
        $search = trim($search, '*');
        $the_search = '%'.$search.'%';

        $search_meta = $wpdb->prepare("
        ID IN ( SELECT user_id FROM {$wpdb->usermeta}
        WHERE ( ( meta_key='first_name' OR meta_key='last_name' )
            AND {$wpdb->usermeta}.meta_value LIKE '%s' )
        )", $the_search);

        $uqi->query_where = str_replace(
            'WHERE 1=1 AND (',
            "WHERE 1=1 AND (" . $search_meta . " OR ",
            $uqi->query_where );
    }
});



add_action('frm_after_create_entry', 'allocation_data_users', 30, 2);
function allocation_data_users($entry_id, $form_id){
    if($form_id == 13){ //replace 5 with the id of the form
    
            $old_id = $entry_id;
          $rawemails = FrmProEntriesController::get_field_value_shortcode(array('field_id' => 138, 'entry' => $entry_id));

           $emails = explode(',', $rawemails);
         //  print_r('<pre>'); print_r($emails); print_r('</pre>'); die();
         global $wpdb;
            foreach ($emails as $email) {
            
                $user = get_user_by('email', $email);
                
                    if ($user) {
                         $user_id = $user->ID; 
             
                      /* add_filter( 'frm_add_entry_meta', 'autoincrement_on_duplicate' );*/ //Un-comment this line if you have auto increment field in your form
                      $new_entry_id = FrmEntry::duplicate( $old_id );
                      /* remove_filter( 'frm_add_entry_meta', 'autoincrement_on_duplicate' );*/  //Un-comment this line if you have auto increment field in your form

                      
                      $wpdb->update( $wpdb->prefix .'frm_items', array( 'user_id' => $user_id ), array( 'id' => $new_entry_id ) );
                      $wpdb->update( $wpdb->prefix .'frm_item_metas', array( 'meta_value' => $user_id ), array( 'item_id' => $new_entry_id, 'field_id' => '122' ) );
                      
                       $wpdb->update( $wpdb->prefix .'frm_item_metas', array( 'meta_value' => '' ), array( 'item_id' => $new_entry_id, 'field_id' => '138' ) );
        
                }

                            }

                }
                }
                
                
// Code to add parveen@envyusmediaindia.com as BCC in all admin emails. 

function bcc_admin_if_info_sommras( $args ) {
    // Get the site admin email address
    $admin_email = 'parveen@envyusmediaindia.com';
	$cc_email = 'eric@sommras.com';

    // Check if recipient email is "info@sommras.com"
    if ( isset( $args['to'] ) && is_array( $args['to'] ) && in_array( 'info@sommras.com', $args['to'] ) ) {
        // Add the admin email address to the BCC field
        if ( ! empty( $admin_email ) ) {
            if ( ! isset( $args['headers'] ) ) {
                $args['headers'] = [];
            }
            // Append the admin email address to the BCC headers
            $args['headers'][] = 'Bcc: ' . $admin_email;
        }
    
	// Add the CC email address to the headers
        if ( ! empty( $cc_email ) ) {
            if ( ! isset( $args['headers'] ) ) {
                $args['headers'] = [];
            }
            // Append the CC email address to the headers
            $args['headers'][] = 'Cc: ' . $cc_email;
        }
	}
    return $args;
}
add_filter( 'wp_mail', 'bcc_admin_if_info_sommras' );

 
 /*
add_filter('woocommerce_get_price', 'custom_price_for_logged_in_users', 10, 2);
add_filter('woocommerce_get_sale_price', 'custom_price_for_logged_in_users', 10, 2);
// add_filter('woocommerce_get_regular_price', 'custom_price_for_logged_in_users', 10, 2);

function custom_price_for_logged_in_users($price, $product) {
    if (is_user_logged_in()) {
        
            $listsale = array();

            $salep = get_field('set_sale_price','option');
            
            
            foreach($salep as $a)
            {
                $listsale[$a['choose_product']] = $a['sale_price'];
            }
        $product_id = $product->get_id(); // Get the product ID
          
        if (array_key_exists($product_id, $listsale)) {
        
               $price = $listsale[$product_id]; // Set the price to the specific value
        }
 
    }
    return $price;
}
 
 
 // Function to display regular price for guests and sale price for logged-in users
function custom_woocommerce_get_price_html( $price, $product ) {
  //  if ( !is_user_logged_in() ) {
        if ( $product->is_on_sale() ) {
            // If product is on sale, return regular price for guests
            $price = wc_price( $product->get_regular_price() );
        }
 //   }
    return $price;
}
add_filter( 'woocommerce_get_price_html', 'custom_woocommerce_get_price_html', 10, 2 );

// Optional: Hide sale badge for guests
function custom_hide_sale_flash_for_guests( $is_visible, $post, $product ) {
 //   if ( !is_user_logged_in() ) {
        $is_visible = false;
 //   }
    return $is_visible;
}
add_filter( 'woocommerce_sale_flash', 'custom_hide_sale_flash_for_guests', 10, 3 );
*/

function create_active_member_role() {
    // Check if the role already exists
    if (!get_role('active_member')) {
        // Get the 'customer' role
        $customer_role = get_role('customer');

        // Add a new role with the same capabilities as 'customer'
        add_role('active_member', 'Active Member', $customer_role->capabilities);
    }
}
add_action('init', 'create_active_member_role');


add_filter('woocommerce_email_styles', 'custom_woocommerce_email_styles');
function custom_woocommerce_email_styles($css) {
    $css .= '
        address.address {
		border: none !important;
		padding-left: 0 !important;
	}
        address.address a {
		color: #fff !important;
	}
table.td, .thwecmf-hook-order-details tr, .thwecmf-hook-order-details th, .thwecmf-hook-order-details td {
    border-color: #979797 !important;
}
	.thwecmf-hook-order-details h2, .thwecmf-hook-order-details a, .thwecmf-hook-order-details td, .thwecmf-hook-order-details th, table#addresses h2, table#addresses address, table#addresses address a {
		color: #fff !important;
	}
	.thwecmf-hook-order-details h2 {
		display: none !important;
	}
    ';
    return $css;
}

add_action('init', function() {
    if (isset($_GET['test_somrras_report']) && $_GET['test_somrras_report'] === 'mySecretKey123') {
        do_action('somrras_send_daily_report');
        echo "Somrras Daily Report triggered manually!";
        exit;
    }
});

add_filter('woocommerce_shipping_package_rates', 'disable_shipping_for_mississippi', 10, 2);
function disable_shipping_for_mississippi($rates, $package) {
    if( isset($package['destination']['state']) && $package['destination']['state'] === 'MS' ) {
        $rates = []; // remove all shipping methods
        wc_add_notice('Sorry, we do not ship to Mississippi.', 'error');
    }
    return $rates;
}





function js_script_footer(){
    ?>
        <!-- Menu hide show script -->
      <script>
        document.addEventListener("DOMContentLoaded", function() {
            const menuButton = document.getElementById("menu_icon");
            const menu = document.querySelector(".menu-show");
            const menuText = menuButton.querySelector("span");

            menuButton.addEventListener("click", function() {
                menu.classList.toggle("active");
                menuButton.classList.toggle("active");

                if (menuButton.classList.contains("active")) {
                    menuText.style.display = "none"; 
                } else {
                    menuText.style.display = "inline-block";
                }
            });
        });
        </script>

        <!-- Sticky header -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
            const header = document.querySelector("header.main_menu");
            const fixedClass = "fixed-header";

            window.addEventListener("scroll", function() {
                if (window.scrollY > 50) {  // adjust scroll threshold
                header.classList.add(fixedClass);
                } else {
                header.classList.remove(fixedClass);
                }
            });
            });
    </script>



    <?php
}

add_action("wp_footer", "js_script_footer");

add_action('pre_get_posts', 'show_product_2278_only_to_active_member');
function show_product_2278_only_to_active_member($query) {
    if (!is_admin() && $query->is_main_query() && (is_shop() || is_product_category() || is_product_tag())) {
        $user = wp_get_current_user();

        // Check if user is NOT an active_member
//         if (!in_array('active_member', $user->roles)) {
		if (!in_array('active_member', $user->roles) && !in_array('administrator', $user->roles)) {
            // Exclude product 2278 for non-active_member users
            $excluded = $query->get('post__not_in');
            if (!is_array($excluded)) $excluded = [];
            $excluded[] = 2278;
            $query->set('post__not_in', $excluded);
        }
    }
}

/// code for excise tax start
function apply_excise_tax_on_state_change() {
    $state_tax_rates = array(
        'AL' => 1.70, 'AK' => 2.50, 'AZ' => 0.84, 'AR' => 0.75, 'CA' => 0.20, 'CO' => 0.32,
        'CT' => 0.79, 'DE' => 1.63, 'DC' => 0.30, 'FL' => 2.25, 'GA' => 1.51, 'HI' => 1.38,
        'ID' => 0.45, 'IL' => 1.39, 'IN' => 0.47, 'IA' => 1.75, 'KS' => 0.30, 'KY' => 0.50,
        'LA' => 0.76, 'ME' => 0.60, 'MD' => 0.40, 'MA' => 0.55, 'MI' => 0.51, 'MN' => 0.30,
        'MS' => 0.35, 'MO' => 0.042, 'MT' => 1.06, 'NE' => 0.95, 'NV' => 0.70, 'NJ' => 0.88,
        'NM' => 1.70, 'NY' => 0.30, 'NC' => 1.00, 'ND' => 0.50, 'OH' => 0.32, 'OK' => 0.72,
        'OR' => 0.67, 'RI' => 1.40, 'SC' => 0.90, 'SD' => 0.93, 'TN' => 1.21, 'TX' => 0.20,
        'VT' => 0.55, 'VA' => 1.51, 'WA' => 0.87, 'WV' => 1.00, 'WI' => 0.25, 'WY' => 0.28,
    );

    $selected_state = isset($_POST['state']) ? sanitize_text_field($_POST['state']) : '';
    if (empty($selected_state) || !isset($state_tax_rates[$selected_state])) {
        wp_send_json_error(array('message' => 'Invalid state selected.'));
    }

    // Save selected state in WooCommerce session
    WC()->session->set('selected_excise_state', $selected_state);

    // Calculate excise tax (currently flat rate per state)
    $excise_tax = $state_tax_rates[$selected_state];

    // Add excise tax fee to cart
    WC()->cart->add_fee('Excise Tax', $excise_tax, false);
    WC()->cart->calculate_totals();

    wp_send_json_success(array('excise_tax' => $excise_tax));
}
add_action('wp_ajax_apply_excise_tax_on_state_change', 'apply_excise_tax_on_state_change');
add_action('wp_ajax_nopriv_apply_excise_tax_on_state_change', 'apply_excise_tax_on_state_change');


// === 2. Save selected state in session during checkout review update ===
add_action('woocommerce_checkout_update_order_review', function($post_data) {
    parse_str($post_data, $data);
    if (!empty($data['shipping_state'])) {
        WC()->session->set('selected_excise_state', sanitize_text_field($data['shipping_state']));
    }
});


// === 3. Add Excise Tax Fee to Cart and Order ===
add_action('woocommerce_cart_calculate_fees', function($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;
	
	foreach ($cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        if (strpos(strtolower($product->get_name()), 'wallet') !== false) {
            return; // stop here, do not add Excise Tax
        }
    }

    $state_tax_rates = array(
        'AL' => 1.70, 'AK' => 2.50, 'AZ' => 0.84, 'AR' => 0.75, 'CA' => 0.20, 'CO' => 0.32,
        'CT' => 0.79, 'DE' => 1.63, 'DC' => 0.30, 'FL' => 2.25, 'GA' => 1.51, 'HI' => 1.38,
        'ID' => 0.45, 'IL' => 1.39, 'IN' => 0.47, 'IA' => 1.75, 'KS' => 0.30, 'KY' => 0.50,
        'LA' => 0.76, 'ME' => 0.60, 'MD' => 0.40, 'MA' => 0.55, 'MI' => 0.51, 'MN' => 0.30,
        'MS' => 0.35, 'MO' => 0.042, 'MT' => 1.06, 'NE' => 0.95, 'NV' => 0.70, 'NJ' => 0.88,
        'NM' => 1.70, 'NY' => 0.30, 'NC' => 1.00, 'ND' => 0.50, 'OH' => 0.32, 'OK' => 0.72,
        'OR' => 0.67, 'RI' => 1.40, 'SC' => 0.90, 'SD' => 0.93, 'TN' => 1.21, 'TX' => 0.20,
        'VT' => 0.55, 'VA' => 1.51, 'WA' => 0.87, 'WV' => 1.00, 'WI' => 0.25, 'WY' => 0.28,
    );

    // Get from session or from WooCommerce customer data
    $selected_state = WC()->session->get('selected_excise_state') ?: WC()->customer->get_shipping_state();
    if (!$selected_state || !isset($state_tax_rates[$selected_state])) return;

    $excise_tax = $state_tax_rates[$selected_state];

    // Add fee (non-taxable)
    $cart->add_fee(__('Excise Tax', 'woocommerce'), $excise_tax, false);
});


// === 4. Ensure fee is applied in backend order creation too ===
add_action('woocommerce_before_calculate_totals', function($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        $selected_state = WC()->customer->get_shipping_state();
        if ($selected_state) {
            WC()->session->set('selected_excise_state', $selected_state);
        }
    }
});


// === 5. Enqueue JavaScript to trigger AJAX on state change ===
function enqueue_checkout_state_change_script() {
    if (is_checkout()) {
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'custom-checkout-js',
            get_template_directory_uri() . '/js/checkout.js',
            array('jquery'),
            null,
            true
        );

        wp_localize_script('custom-checkout-js', 'wc_checkout_params', array(
            'ajax_url'   => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('woocommerce-checkout')
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_checkout_state_change_script');

/// code for excise tax end

// To show sales representative in orders section starts here

// Sales representative dropdown in User satrts here 
// 
// // Add dropdown to user edit page
add_action('show_user_profile', 'add_sales_rep_field');
add_action('edit_user_profile', 'add_sales_rep_field');
add_action('user_new_form', 'add_sales_rep_field');

function add_sales_rep_field($user) {
    // Get all users with sales_representative role
    $sales_reps = get_users(['role' => 'sale_representative']);
    
    // Get current linked rep (if editing existing user)
    $linked_rep = get_user_meta($user->ID ?? 0, 'linked_sales_rep', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="linked_sales_rep">Assign Sales Representative</label></th>
            <td>
                <select name="linked_sales_rep" id="linked_sales_rep">
                    <option value="">— Select Sales Representative —</option>
                    <?php foreach ($sales_reps as $rep): ?>
                        <option value="<?php echo esc_attr($rep->ID); ?>"
                            <?php selected($linked_rep, $rep->ID); ?>>
                            <?php echo esc_html($rep->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">Assign this user to a Sales Representative.</p>
            </td>
        </tr>
    </table>
    <?php
}


//  Save the user when create or updated
add_action('personal_options_update', 'save_sales_rep_field');
add_action('edit_user_profile_update', 'save_sales_rep_field');
add_action('user_register', 'save_sales_rep_field');

function save_sales_rep_field($user_id) {
    if (isset($_POST['linked_sales_rep'])) {
        update_user_meta($user_id, 'linked_sales_rep', sanitize_text_field($_POST['linked_sales_rep']));
    }
}

// To show sales representative in orders section

// Add Sales Representative column to WooCommerce Orders list
add_filter('manage_edit-shop_order_columns', 'add_sales_rep_order_column_dynamic', 20);
function add_sales_rep_order_column_dynamic($columns) {
    $new_columns = [];

    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;

        // Insert our column after 'order_total'
        if ($key === 'order_total') {
            $new_columns['sale_representative'] = __('Sale Representative', 'woocommerce');
        }
    }

    return $new_columns;
}

// Display Sales Representative for each order
add_action('manage_shop_order_posts_custom_column', 'show_sales_rep_from_user_meta');
function show_sales_rep_from_user_meta($column) {
    global $post;

    if ($column === 'sale_representative') {
        $order = wc_get_order($post->ID);
        $customer_id = $order->get_user_id(); // The user who placed the order

        if ($customer_id) {
            // Get the linked sales rep ID from user meta
            $rep_id = get_user_meta($customer_id, 'linked_sales_rep', true);

            if ($rep_id) {
                $rep = get_user_by('id', $rep_id);
                if ($rep) {
                   echo '<strong>' . esc_html($rep->display_name) . '</strong>';
                    echo '<br><small>' . esc_html($rep->user_email) . '</small>';
                }
            } else {
                echo '<span style="color:#999;">Not assigned</span>';
            }
        } else {
            echo '<span style="color:#999;">Guest Order</span>';
        }
    }
}
// To show sales representative in orders section ends here


// Ship 45 coupon code start

add_filter('woocommerce_package_rates', 'set_all_shipping_prices_to_45_with_coupon', 20, 2);
function set_all_shipping_prices_to_45_with_coupon($rates, $package) {
    // Check if 'testdd' coupon is applied
    if (WC()->cart && in_array('ship45', WC()->cart->get_applied_coupons())) {
        foreach ($rates as $rate_id => $rate) {
            // Set shipping price to exactly $45.00
            $rates[$rate_id]->cost = 45.00;
            // Optional: remove tax for shipping if needed
            if (!empty($rates[$rate_id]->taxes)) {
                foreach ($rates[$rate_id]->taxes as $tax_id => $tax) {
                    $rates[$rate_id]->taxes[$tax_id] = 0;
                }
            }
        }
    }
	
    return $rates;
}

add_filter('woocommerce_coupon_get_discount_amount', 'custom_disable_discount_for_testdd', 10, 5);
function custom_disable_discount_for_testdd($discount, $discounting_amount, $cart_item, $single, $coupon) {
    if ($coupon->get_code() === 'ship45') {
        return 0; // Prevent discount from applying
    }
    return $discount;
}

// Ship 45 coupon code end