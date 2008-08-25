<?php
/*
Plugin Name: Page Restrict
Plugin URI: http://sivel.net/category/wordpress/plugins/
Description: Restrict certain pages to logged in users
Author: Matt Martz <mdmartz@sivel.net>
Author URI: http://sivel.net/
Version: 1.4

	Copyright (c) 2008 Matt Martz (http://sivel.net)
        Page Restrict is released under the GNU Lesser General Public License (LGPL)
	http://www.gnu.org/licenses/lgpl-3.0.txt
*/

if ( is_admin () )
	require_once( dirname ( __FILE__ ) . '/inc/admin.php' );

// Get Specific Page Restrict Option
function pr_get_opt ( $option ) {
	$pr_options = get_option ( 'pr_options' );
        return $pr_options[$option];
}

// Add headers to keep browser from caching the pages when user not logged in
// Resolves a problem where users see the login form after logging in and need 
// to refresh to see content
function pr_no_cache_headers () {
	global $user_ID;
	get_currentuserinfo ();
        if ( ! $user_ID ) {
		header ( 'Cache-Control: no-cache, must-revalidate' );
		header ( 'Expires: ' . gmdate ( 'r' , strtotime ( 'last week' ) ) ); 
	}
}

// Perform the restriction and if restricted replace the page content with a login form
function pr_page_restrict ( $pr_page_content ) {
	global $user_ID;
	get_currentuserinfo ();
	if ( ! $user_ID ) :
		if ( ( ( is_page ( explode ( ',' , pr_get_opt ( 'pages' ) ) ) ) && ( pr_get_opt ( 'method' ) != 'none' ) ) || ( ( is_page () ) && ( pr_get_opt ( 'method' ) == 'all' ) ) ):
			$pr_page_content = '
			<p>You are required to login to view this page.</p>
			<form style="text-align: left;" action="' . get_bloginfo ( 'url' ) . '/wp-login.php" method="post">
				<p>
				<label for="log"><input type="text" name="log" id="log" value="' . wp_specialchars ( stripslashes ( $user_login ) , 1 ) . '" size="22" /> User</label><br />
				<label for="pwd"><input type="password" name="pwd" id="pwd" size="22" /> Password</label><br />
				<input type="submit" name="submit" value="Log In" class="button" />
				<label for="rememberme"><input name="rememberme" id="rememberme" type="checkbox" checked="checked" value="forever" /> Remember me</label><br />
				</p>
				<input type="hidden" name="redirect_to" value="' . $_SERVER['REQUEST_URI'] . '" />
			</form>
			<p><a href="' . get_bloginfo ( 'url' ) . '/wp-register.php">Register</a>&nbsp;|&nbsp;<a href="' . get_bloginfo ( 'url' ) . '/wp-login.php?action=lostpassword">Lost your password?</a></p>
			';
			return $pr_page_content;
		else :
			return $pr_page_content;
		endif;
	else :
		return $pr_page_content;
	endif;
}

// Add Actions
add_action ( 'admin_menu' , 'pr_options_page' ) ;

// Add Filters
add_filter ( 'the_content' , 'pr_page_restrict' );
add_filter ( 'the_excerpt' , 'pr_page_restrict' );
?>
