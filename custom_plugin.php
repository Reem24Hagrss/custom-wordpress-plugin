<?php
/**
 * Plugin Name: Custom Pulgin
 * plugin URI: https://github.com/Reem24Hagrss/custom-wordpress-plugin.git
 * Description: custom plugin that contains follow : - Adds a new user meta “age” in user profile.- Adds a new order status “delivered” to Woocommerce order statuses (extends Woocommerce plugin) - and Adds a new user meta “last_order_id” that’s the last “delivered” order id.
 * version: 1.0.0
 * Author: Reem Hagrss
 * Author E-Mail: reem24hagrss@gmail.com
 */


/**
 * Adds a new user meta “age” in user profile.
 */
add_action( 'show_user_profile', 'add_age_usermeta' );
add_action( 'edit_user_profile', 'add_age_usermeta' );

function add_age_usermeta( $user ) { ?>
    <h3><?php _e("Extra profile information", "blank"); ?></h3>
    <br>
    <table class="form-table">
    <tr>
        <th><label for="age"><?php _e("Age"); ?></label></th>
        <td>
            <input type="number" min="1" name="age" id="age" value="<?php echo  get_usermeta(  $user->id , 'age') ; ?>" class="regular-text" /><br />
        </td>
    </tr>
    
    </table>
    <br>
<?php }

add_action('personal_options_update', 'add_age_action');
add_action('edit_user_profile_update', 'add_age_action');
function add_age_action($user_id) {
    update_user_meta($user_id, 'age', $_POST['age']);
}

/**
 * Adds a new order status “delivered” to Woocommerce order statuses (extends Woocommerce plugin).
 */

//  check if WooCommerce exist


add_action( 'plugins_loaded', 'check_requirements' );

function check_requirements() {
    if ( class_exists( 'woocommerce' ) ) {

        add_action( 'init', 'register_delivered_status' );
        add_filter( 'wc_order_statuses', 'add_delivered_to_order_statuses' );

        add_action('woocommerce_order_status_changed', 'add_last_order_id_usermeta');

        add_action( 'show_user_profile', 'show_last_order_id_usermeta' );
        add_action( 'edit_user_profile', 'show_last_order_id_usermeta' );

    } else {
        add_action( 'admin_notices', 'missing_activate_wc' );

    }
}

 //  Register a "delivered" status to Woocommerce order statuses 
function register_delivered_status() {
    register_post_status( 'wc-delivered', array(
        'label'                     => 'Delivered',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Delivered (%s)', 'Delivered (%s)' )
    ) );
}

//  Add a "delivered" status to list of Woocommerce order statuses
function add_delivered_to_order_statuses( $order_statuses ) {
 
    $new_order_statuses = array();
 
    // add new order status after processing
    foreach ( $order_statuses as $key => $status ) {
 
        $new_order_statuses[ $key ] = $status;
 
        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-delivered'] = 'Delivered';
        }
    }
 
    return $new_order_statuses;
}

// Display a message advising WooCommerce is required
function missing_activate_wc() { 
    $class = 'notice notice-error';
    $message = __( 'This Plugin requires WooCommerce, please activate WooCommerce plugin fisrt', 'custom_plugin' );
 
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}

/**
 * Adds a new user meta “last_order_id” that’s the last “delivered” order id.
 */

function add_last_order_id_usermeta($order) {
    $order_data = wc_get_order( $order );
    $status = $order_data->get_status();
    if( strcmp($status, 'wc-delivered') === 0 ){
        $order_id = $order_data->get_id();
        $user_id = $order_data->get_user_id();
        update_user_meta($user_id, 'last_order_id', $order_id );
    }
}

function show_last_order_id_usermeta( $user ) { ?>
    <table class="form-table">
    <tr>
        <th><label for="last_order_id"><?php _e("Last Order Id"); ?></label></th>
        <td>
            <input type="number" min="1" name="last_order_id" id="last_order_id" value="<?php echo  get_usermeta(  $user->id , 'last_order_id') ;  ?>" class="regular-text" disabled /><br />
        </td>
    </tr>
    
    </table>
    <br>
<?php }
