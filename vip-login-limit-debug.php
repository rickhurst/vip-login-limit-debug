<?php
/*
Plugin Name: VIP login debug
Plugin URI:  https://wordpressvip.com
Description: Debug info for locked out logins
Version:     1.0
Author:      Rick Hurst
Author URI:  https://wordpressvip.com
*/

/* Add message above login form */
function vip_debug_login_message() {

    // store some key/values which might be useful for debugging login limit
    $login_vars = [];

    // IP address
    $login_vars['ip']   = preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR'] ); //phpcs:ignore

    // check if the username is set as limited
    if( isset($_GET['username']) && "" !== $_GET['username'] ){ //phpcs:ignore
        $username = $_GET['username']; //phpcs:ignore
        $login_vars['ip_username_cache_key'] = $login_vars['ip'] . '|' . $username;
        $login_vars['ip_count'] = wp_cache_get( $login_vars['ip'], 'login_limit' );
        $login_vars['ip_username_count'] = wp_cache_get( $login_vars['ip_username_cache_key'], 'login_limit' );
        $login_vars['username_count'] = wp_cache_get( $username, 'login_limit' );

        $ip_username_threshold = apply_filters( 'wpcom_vip_ip_username_login_threshold', 5, $login_vars['ip'], $username );
        $ip_threshold = apply_filters( 'wpcom_vip_ip_login_threshold', 50, $login_vars['ip'] );
        $username_threshold = 5 * $ip_username_threshold; // Default to 5 times the IP + username threshold
        $username_threshold = apply_filters( 'wpcom_vip_username_login_threshold', $username_threshold, $username );

        $login_vars['ip_username_threshold'] = $ip_username_threshold;
        $login_vars['ip_threshold'] = $ip_threshold;
        $login_vars['username_threshold'] = $username_threshold;
    }

    $debug_info = var_export($login_vars, true); //phpcs:ignore

    return '<p class="message"><b>VIP debug info</b><br>'.$debug_info.'</p>'; //phpcs:ignore

}

if( isset($_GET['vip_login_debug']) ){ //phpcs:ignore
    add_filter('login_message', 'vip_debug_login_message');
}