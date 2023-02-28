<?php
if( ! defined('WP_UNINSTALL_PLUGIN')) {
	die;
}

$vk = get_posts(array('post_type' => 'vksettings'));
foreach($vk as $vks){
	wp_delete_post($vks->ID, false);
}
global $wpdb;
$wpdb->query("DELETE FROM wp_posts WHERE post_type = 'vksettings'");
$wpdb->query("DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT id FROM wp_posts)");