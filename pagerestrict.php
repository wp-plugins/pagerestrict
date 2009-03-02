<?php
/*
Plugin Name: Page Restrict
Plugin URI: http://sivel.net/wordpress/
Description: Restrict certain pages to logged in users
Author: Matt Martz
Author URI: http://sivel.net/
Version: 0.3

	Copyright (c) 2008 Matt Martz (http://sivel.net)
        Page Restrict is released under the GNU Lesser General Public License (LGPL)
	http://www.gnu.org/licenses/lgpl-3.0.txt
*/

// Set Page Restrict Version Number
$pr_version = '0.3';

// Get Specific Page Restrict Option
function pr_get_opt($option) {
	$pr_options = get_option('pr_options');
        return $pr_options[$option];
}

// Check the version in the options table and if less than this version perform update
function pr_ver_check() {
	global $pr_version;
	if ((pr_get_opt('version') < $pr_version) || (!pr_get_opt('version'))):
		$pr_options = array();
		$pr_options['version'] = $pr_version;
		$pr_options['pages'] = pr_get_opt('pages');
		$pr_options['method'] = pr_get_opt('method');
		pr_delete();
		add_option('pr_options', $pr_options, 'Page Restrict Options');
	endif;
}

// Initialize the Page Restrict default options during plugin activation
function pr_init() {
	global $pr_version;
        $pr_options = array();
	$pr_options['version'] = $pr_version;
        $pr_options['pages'] = '';
	$pr_options['method'] = 'selected';
        add_option('pr_options', $pr_options, 'Page Restrict Options');
}

// Delete all Page Restrict Options 
function pr_delete() {
        delete_option('pr_options');
}

// Add headers to keep browser from caching the pages when user not logged in
// Resolves a problem where users see the login form after logging in and need 
// to refresh to see content
function pr_no_cache_headers() {
	global $user_ID;
	get_currentuserinfo();
        if (!$user_ID) {
		$current_tz = date_default_timezone_get();
		date_default_timezone_set('GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: ' . date('r', strtotime('last week'))); 
		date_default_timezone_set($current_tz);
	}
}

// Perform the restriction and if restricted replace the page content with a login form
function pr_page_restrict($pr_page_content) {
	global $user_ID;
	get_currentuserinfo();
	if (!$user_ID) :
		if (((is_page(explode(',',pr_get_opt('pages')))) && (pr_get_opt('method') != 'none')) || ((is_page()) && (pr_get_opt('method') == 'all')) ):
			$pr_page_content = '
			<p>You are required to login to view this page.</p>
			<form style="text-align: left;" action="' . get_bloginfo('url') . '/wp-login.php" method="post">
				<p>
				<label for="log"><input type="text" name="log" id="log" value="' . wp_specialchars(stripslashes($user_login), 1) . '" size="22" /> User</label><br />
				<label for="pwd"><input type="password" name="pwd" id="pwd" size="22" /> Password</label><br />
				<input type="submit" name="submit" value="Log In" class="button" />
				<label for="rememberme"><input name="rememberme" id="rememberme" type="checkbox" checked="checked" value="forever" /> Remember me</label><br />
				</p>
				<input type="hidden" name="redirect_to" value="' . $_SERVER['REQUEST_URI'] . '" />
			</form>
			<p><a href="' . get_bloginfo('url') . '/wp-register.php">Register</a>&nbsp;|&nbsp;<a href="' . get_bloginfo('url') . '/wp-login.php?action=lostpassword">Lost your password?</a></p>
			';
			return $pr_page_content;
		else :
			return $pr_page_content;
		endif;
	else :
		return $pr_page_content;
	endif;
}

// Add the Page Restrict options page
function pr_options_page() {
        if (function_exists('add_options_page')) {
                add_options_page('Page Restrict','Page Restrict', 'manage_options', 'pagerestrict.php', 'pr_admin_page');
        }
}

// The Page Restrict options page
function pr_admin_page() {
	pr_ver_check();
	if ($_POST['page_id']) :
		$page_ids_post_arr = $_POST['page_id'];
		$page_ids_post_str = implode(',',$page_ids_post_arr);
		$pr_options['pages'] = $page_ids_post_str;
		$pr_options['method'] = pr_get_opt('method');
		$pr_options['version'] = pr_get_opt('version');
		update_option('pr_options', $pr_options);
	endif;
	if ($_POST['method']) :
		$pr_method_post = $_POST['method'];
		$pr_options['method'] = $pr_method_post;
		$pr_options['pages'] = pr_get_opt('pages');
		$pr_options['version'] = pr_get_opt('version');
		update_option('pr_options', $pr_options);
	endif;
	if ($pr_method_post)
		$pr_method = $pr_method_post;
	else
		$pr_method = pr_get_opt('method');
	if ($pr_method == 'all') :
		$all_checked = ' checked="checked" ';
	elseif ($pr_method == 'none') :
		$none_checked = ' checked="checked" ';
	else :
		$selected_checked = ' checked="checked" ';
	endif;
	echo '<div class="wrap">' . "\r\n";
	echo '<h2>Page Restrict Options</h2>' . "\r\n";
	echo '<h3>Choose the restriction method:</h3>' . "\r\n";
	echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">' . "\r\n";
	echo '<input type="radio" name="method" value="all"' . $all_checked  . ' />Restrict all pages<br />' . "\r\n";
	echo '<input type="radio" name="method" value="none"' . $none_checked  . ' />Restrict no pages<br />' . "\r\n";
	echo '<input type="radio" name="method" value="selected"' . $selected_checked  . ' />Restrict selected pages only<br />' . "\r\n";
        echo '<input type="submit" name="submit" class="button" value="Submit" />&nbsp;&nbsp;';
        echo '<input type="reset" name="reset" class="button" value="Reset" />&nbsp;&nbsp;';
        echo '<input type="button" name="cancel" value="Cancel" class="button" onclick="javascript:history.go(-1)" />' . "\r\n";
	echo '</form>' . "\r\n";
	if ($pr_method == 'selected') :
        $page_ids_opt_str = pr_get_opt('pages');
	        if ($page_ids_opt_str)
	                $page_ids_opt_arr = explode(',',$page_ids_opt_str);
	        if (($page_ids_opt_arr) && ($page_ids_post_arr)):
	                $page_ids = array_merge($page_ids_opt_arr, $page_ids_post_arr);
	        elseif ($page_ids_opt_arr) :
	                $page_ids = $page_ids_opt_arr;
	        else :
	                $page_ids = $page_ids_post_arr;
	        endif;
		echo '<h3>Select the Pages you wish to restrict to logged in users only:</h3>' . "\r\n";
		echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">' . "\r\n";
	        $avail_pages = get_pages();
	        $avail_pages_cnt = count($avail_pages);
	        $i = $avail_pages_cnt;
		while ($i > 0) :
			$i--;
			$pr_page_id = $avail_pages[$i]->ID;	
			$pr_page_title = $avail_pages[$i]->post_title;
			if ($page_ids) :
				if (in_array($pr_page_id, $page_ids)) :
					$page_checked = ' checked="checked" ';
				else :
					$page_checked = '';
				endif;
			endif;
			echo '<input type="checkbox" name="page_id[]" value="' . $pr_page_id  . '"' . $page_checked . ' />' . $pr_page_title  . '<br />' . "\r\n";
		endwhile;
		echo '<input type="submit" name="submit" class="button" value="Submit" />&nbsp;&nbsp;';
		echo '<input type="reset" name="reset" class="button" value="Reset" />&nbsp;&nbsp;';
		echo '<input type="button" name="cancel" value="Cancel" class="button" onclick="javascript:history.go(-1)" />' . "\r\n";
		echo '</form>' . "\r\n";
	endif;
	echo '</div>' . "\r\n";
}

// Add Actions
add_action('admin_menu', 'pr_options_page');
add_action('activate_pagerestrict/pagerestrict.php','pr_init');
add_action('send_headers','pr_no_cache_headers');

// Add Filters
add_filter('the_content','pr_page_restrict');
