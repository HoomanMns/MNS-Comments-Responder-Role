<?php

/**
 * Plugin Name: MNS Comments Responder Role
 * Plugin URI: https://github.com/HoomanMns/mns-comments-responder-role
 * Description: This plugin adds a comments responder user role!
 * Version: 1.0.0
 * Author: Hooman Mansuri
 * Author URI: https://hoomanmns.com/
 * Developer: Hooman Mansuri
 * Developer URI: https://hoomanmns.com/
 * Text Domain: mnscrr
 * Domain Path: /languages
 * License: GPLv2 or later
 *
 * Copyright: © 2020 MNS.
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


// Define important values
define( 'MNSCRR_VERSION', '1.0.0' );
define( 'MNSCRR_ROOT', __FILE__ );
define( 'MNSCRR_DIR', plugin_dir_path( MNSCRR_ROOT ) );
define( 'MNSCRR_URL', plugin_dir_url( MNSCRR_ROOT ) );


// Add plugin support for Translation
function mnscrr_load_textdomain() {
	
	load_plugin_textdomain('mnscrr', false, basename(dirname(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'mnscrr_load_textdomain');

 
// Add plugin row meta
function mnscrr_row_meta($plugin_meta, $plugin_file, $plugin_data, $status) {

	if(strpos($plugin_file, basename(__FILE__))) {

		$plugin_meta[] = '<a href="https://t.me/codimns">' . __('Telegram', 'mnswmc') . '</a>';
	}
	return $plugin_meta;
}
add_filter('plugin_row_meta', 'mnscrr_row_meta', 10, 4);


// Remove Posts Subs
function mnscrr_posts_remove_subs($views) {
	
	$new_views = array();
	
	if(isset($views['publish']))
		$new_views['publish'] = $views['publish'];
	
	return $new_views;
}


// Remove Posts Row Actions
function mnscrr_posts_remove_row_actions($actions, $post) {
	
	$new_actions = array();
	
	if(!in_array($post->post_status, array('pending', 'draft', 'future')))
		$new_actions['view'] = $actions['view'];
	
    return $new_actions;
}


// Remove Posts Bulk Actions
function mnscrr_posts_remove_bulk_actions($bulk_actions) {
	
	return array();
}


// Remove Posts Checkbox Inputs
function mnscrr_posts_remove_checkboxes($columns) {

	if(isset($columns['cb']))
		unset($columns['cb']);
	
    return $columns;
}


// Remove Posts Edit and Delete Caps
function mnscrr_posts_remove_post_edit_or_delete_caps($allcaps, $caps, $args) {

	if($args[0] == 'edit_post' or $args[0] == 'delete_post') {

		foreach((array) $caps as $cap) {

			if(array_key_exists($cap, $allcaps))
				$allcaps[$cap] = 0;
		}
	}
	return $allcaps;
}


// Add Moderate All Comments Cap
function mnscrr_add_moderate_all_comments_cap($caps, $cap, $user_id, $args) {
	
	if(in_array($cap, array('edit_comment', 'edit_post')) and $caps)
		return array();

	return $caps;
}


// Remove Comments Subs
function mnscrr_comments_remove_subs($views) {
	
	if(isset($views['trash']))
		unset($views['trash']);
	
	return $views;
}


// Remove Comments Row Actions
function mnscrr_comments_remove_row_actions($actions, $comment) {
	
	if(isset($actions['trash']))
		unset($actions['trash']);
	
	if(isset($actions['delete']))
		unset($actions['delete']);
	
	return $actions;
}

function mnscrr_comments_remove_bulk_actions($bulk_actions) {
	
	if(isset($bulk_actions['trash']))
		unset($bulk_actions['trash']);
	
	return $bulk_actions;
}


// Display Error and Die!
function mnscrr_restrict_message($post_ID) {
	
	wp_die(__('You are not allowed to do this!', 'mnscrr'));
}


// Remove Admin Bar "New post" menu
function mnscrr_edit_admin_bar() {
	
	if(!current_user_can('mnscrr_cap'))
		return;
	
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( 'new-content' );
}
add_action('wp_before_admin_bar_render', 'mnscrr_edit_admin_bar');


// Remove Admin Menu Entries
function mnscrr_clean_responder_dashboard() {
	
	if(!current_user_can('mnscrr_cap'))
		return;
	
	global $menu;
	
	if($menu) {
		
		$allowed_menu = array(__('Comments'), __('Profile'));
		
		foreach($menu as $menu_key => $menu_val) {
			
			$menu_value = explode(' ', $menu[$menu_key][0]);
			
			if(!in_array($menu_value[0] != null ? $menu_value[0] : '', $allowed_menu) !== false)
				unset($menu[$menu_key]);
		}
	}
}
add_action('admin_init', 'mnscrr_clean_responder_dashboard');


// Redirect to Comments Page
function mnscrr_redirect_responder() {
	
	global $pagenow;
	
	$allowed = array('edit-comments.php', 'comment.php', 'admin-ajax.php', 'profile.php');
	
	if(current_user_can('mnscrr_cap')and $pagenow and !in_array($pagenow, $allowed)) {

		wp_redirect( admin_url( 'edit-comments.php' ) );
		die();
	}
}
add_action( 'admin_init', 'mnscrr_redirect_responder' );


// Restrict Sections
function mnscrr_restrict_sections() {
	
	if(current_user_can('mnscrr_cap')) {
		
		add_filter('views_edit-post', 'mnscrr_posts_remove_subs', 10, 1);
		add_filter('post_row_actions', 'mnscrr_posts_remove_row_actions', 10, 2);
		add_filter('bulk_actions-edit-post', 'mnscrr_posts_remove_bulk_actions', 10, 1);
		add_filter('manage_edit-post_columns', 'mnscrr_posts_remove_checkboxes', 10, 1);
		add_filter('user_has_cap', 'mnscrr_posts_remove_post_edit_or_delete_caps', 10, 3);
		add_filter('views_edit-comments','mnscrr_comments_remove_subs', 10, 1);
		add_filter('comment_row_actions', 'mnscrr_comments_remove_row_actions', 10, 2);
		add_filter('bulk_actions-edit-comments', 'mnscrr_comments_remove_bulk_actions', 10, 1);
		add_filter('map_meta_cap', 'mnscrr_add_moderate_all_comments_cap', 10, 4);
		
		add_action('trash_comment', 'mnscrr_restrict_message', 10, 1);
		add_action('delete_comment', 'mnscrr_restrict_message', 10, 1);
		add_action('wp_trash_post', 'mnscrr_restrict_message', 10, 1);
		add_action('before_delete_post', 'mnscrr_restrict_message', 10, 1);
	}
}
add_action( 'admin_init', 'mnscrr_restrict_sections');


// Create Role When Activate
function mnscrr_activate_plugin() {

	$capabilities = array(
		'mnscrr_cap' => true,
		'read' => true,
		'edit_posts' => true,
		'edit_other_posts' => true,
		'edit_published_posts' => true,
		'moderate_comments' => true,
	);
	add_role('mnscrr', __('Comments Responder', 'mnscrr'), $capabilities);
}
register_activation_hook( MNSCRR_ROOT, 'mnscrr_activate_plugin' );


// Remove Role When Deactivate
function mnscrr_deactivate_plugin() {

	$users = get_users(array('role' => 'mnscrr'));
	
	if(!empty($users)) {
		
		foreach($users as $user) {
			
			$user->remove_role('mnscrr');
			$user->add_role('subscriber');
		}
	}
	remove_role('mnscrr');
}
register_deactivation_hook( MNSCRR_ROOT, 'mnscrr_deactivate_plugin' );