<?php
/*
Plugin Name: VIP login debug
Plugin URI:  https://wordpressvip.com
Description: Debug info for locked out logins
Version:     1.1
Author:      Rick Hurst
Author URI:  https://wordpressvip.com
*/


/* Add message above login form */
function vip_debug_login_message() {

    // store some key/values which might be useful for debugging login limit
    $login_debug_vars = [];

    $login_debug_vars['cache_key_lock_prefix'] = CACHE_KEY_LOCK_PREFIX;
    $login_debug_vars['cache_group_login_limit'] = CACHE_GROUP_LOGIN_LIMIT;
    $login_debug_vars['cache_group_lost_password_limit'] = CACHE_GROUP_LOST_PASSWORD_LIMIT;
    $login_debug_vars['cache_key_lock_prefix'] = CACHE_KEY_LOCK_PREFIX;

    // IP address
    if( isset($_GET['ip']) && "" !== $_GET['ip'] ){ //phpcs:ignore
        $login_debug_vars['ip'] = $_GET['ip'];
    } else {
        $login_debug_vars['ip']   = preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR'] ); //phpcs:ignore
    }

    $thresholds = _vip_get_auth_count_limit_defaults();
    $login_debug_vars = array_merge( $login_debug_vars, $thresholds );

    // check if the username is set as limited
    if( isset($_GET['username']) && "" !== $_GET['username'] ){ //phpcs:ignore
        $raw_username = $_GET['username']; //phpcs:ignore
        $cache_keys = vip_login_debug_get_cache_keys( $raw_username, $login_debug_vars['ip'] );
        $login_debug_vars = array_merge( $login_debug_vars, $cache_keys );

        $cache_group = CACHE_GROUP_LOGIN_LIMIT;
        $login_debug_vars['is_ip_username_locked'] = wp_cache_get( CACHE_KEY_LOCK_PREFIX . $cache_keys['ip_username_cache_key'], $cache_group );
        $login_debug_vars['is_ip_locked'] = wp_cache_get( CACHE_KEY_LOCK_PREFIX . $cache_keys['ip_cache_key'], $cache_group );
        $login_debug_vars['is_username_locked'] = wp_cache_get( CACHE_KEY_LOCK_PREFIX . $cache_keys['username_cache_key'], $cache_group );
    }

    $output = '<div>';
    $output .= vip_login_debug_display_array_as_table($login_debug_vars);
    $output .= vip_login_debug_cache_commands($cache_keys);
    $output .= '</div>';

    return $output; //phpcs:ignore
}

/**
 * Output an associative array as an HTML table with WordPress escaping.
 *
 * @param array $data The associative array to be displayed as a table.
 */
function vip_login_debug_display_array_as_table($data) {
    if (empty($data) || !is_array($data)) {
        return '';
    }

    $table_html = '<table style="background:white;margin-bottom: 20px;">';

    foreach ($data as $key=>$val) {

        if(is_bool($val)){
            $val = (true === $val) ? 'yes' : 'no';
        }   

        $table_html .= '<tr>';
        $table_html .= '<th style="text-align:left;padding:3px;">' . esc_html($key) . '</th>';
        $table_html .= '<td style="text-align:left;padding:3px;">' . esc_html($val) . '</td>';
        $table_html .= '</tr>';
    }

    $table_html .= '</table>';

    return $table_html;
}

function vip_login_debug_cache_commands( $cache_keys ){
    $output = '<div>';
    $output .= '<pre>';
    foreach($cache_keys as $key){
        $output .= 'wp cache delete "' . CACHE_KEY_LOCK_PREFIX . $key . '" ' . CACHE_GROUP_LOGIN_LIMIT . PHP_EOL;
    }
    $output .= '</pre></div>';
    return $output;
}

function vip_login_debug_get_cache_keys( $raw_username, $ip ){
            $username = vip_strict_sanitize_username( $raw_username );
        
            // phpcs:ignore WordPressVIPMinimum.Variables.ServerVariables.UserControlledHeaders
            $ip = filter_var( $ip ?? '', FILTER_VALIDATE_IP, [ 'options' => [ 'default' => '' ] ] );
        
            return [
                'ip_username_cache_key' => $ip . '|' . $username,
                'ip_cache_key'          => $ip,
                'username_cache_key'    => $username,
            ];
}


if( isset($_GET['vip_login_debug']) ){ //phpcs:ignore
    add_filter('login_message', 'vip_debug_login_message');
}