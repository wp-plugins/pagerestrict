<?php
/*
Part of WordPress Plugin: Page Restrict
Plugin URI: http://sivel.net/wordpress/
*/

$pr_version = '1.5';

// Check the version in the options table and if less than this version perform update
function pr_ver_check () {
	global $pr_version;
	if ( ( pr_get_opt ( 'version' ) < $pr_version ) || ( !pr_get_opt ( 'version' ) ) ) :
		$pr_options = array ();
		$pr_options['version'] = $pr_version;
		$pr_options['pages'] = explode ( ',' , pr_get_opt ( 'pages' ) );
		$pr_options['method'] = pr_get_opt ( 'method' );
		$pr_options['message'] = 'You are required to login to view this page.';
		pr_delete ();
		add_option ( 'pr_options' , $pr_options , 'Page Restrict Options' );
	endif;
}

// Initialize the default options during plugin activation
function pr_init () {
	global $pr_version;
	if ( ! pr_get_opt( 'version' ) ) :
	        $pr_options = array ();
		$pr_options['version'] = $pr_version;
	        $pr_options['pages'] = array ();
		$pr_options['method'] = 'selected';
		$pr_options['message'] = 'You are required to login to view this page.';
	        add_option ( 'pr_options', $pr_options, 'Page Restrict Options' ) ;
	else :
		pr_ver_check ();
	endif;
}

// Delete all options 
function pr_delete () {
        delete_option ( 'pr_options' );
}

// Add the options page
function pr_options_page () {
	if ( is_admin () ) :
	        if ( function_exists ( 'add_options_page' ) ) :
	                add_options_page ( 'Page Restrict' , 'Page Restrict' , 'manage_options' , 'pagerestrict/pagerestrict.php' , 'pr_admin_page' );
	        endif;
	endif;
}

// The options page
function pr_admin_page () {
	if ( is_admin () ) :
		pr_ver_check ();
		if ( $_POST['submit'] ) :
			$page_ids = $_POST['page_id'];
			$pr_options['pages'] = $page_ids;
			$pr_method = $_POST['method'];
			if ( count ( $page_ids ) == 0 )
				$pr_options['method'] = 'none';
			else
				$pr_options['method'] = $pr_method;
			$pr_options['version'] = pr_get_opt ( 'version' );
			$pr_message = $_POST['message'];
			$pr_options['message'] = $pr_message;
			update_option ( 'pr_options' , $pr_options );
		else :
			$page_ids = pr_get_opt ( 'pages' );
			$pr_method = pr_get_opt ( 'method' );
			$pr_message = pr_get_opt ( 'message' );
		endif;
		if ( $pr_method == 'all' ) :
			$all_checked = ' checked="checked" ';
		elseif ( $pr_method == 'none' ) :
			$none_checked = ' checked="checked" ';
		else :
			$selected_checked = ' checked="checked" ';
		endif;
		echo '<div class="wrap">' . "\r\n";
		echo '<h2>Page Restrict Options</h2>' . "\r\n";
		echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">' . "\r\n";
		echo '<h3>Enter your restriction message:</h3>' . "\r\n";
		echo '<input type="text" size="64" name="message" value="' . $pr_message . '" /><br />';
		echo '<h3>Choose the restriction method:</h3>' . "\r\n";
		echo '<input type="radio" name="method" value="all"' . $all_checked  . ' />Restrict all pages<br />' . "\r\n";
		echo '<input type="radio" name="method" value="none"' . $none_checked  . ' />Restrict no pages<br />' . "\r\n";
		echo '<input type="radio" name="method" value="selected"' . $selected_checked  . ' />Restrict selected pages only<br />' . "\r\n";
		if ( $pr_method == 'selected' ) :
			echo '<h3>Select the Pages you wish to restrict to logged in users only:</h3>' . "\r\n";
			echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">' . "\r\n";
		        $avail_pages = get_pages ();
		        $i = count ( $avail_pages );
			while ( $i > 0 ) :
				$i--;
				$pr_page_id = $avail_pages[$i]->ID;	
				$pr_page_title = $avail_pages[$i]->post_title;
				if ( $page_ids ) :
					if ( in_array ( $pr_page_id , $page_ids ) ) :
						$page_checked = ' checked="checked" ';
					else :
						$page_checked = '';
					endif;
				endif;
				echo '<input type="checkbox" name="page_id[]" value="' . $pr_page_id  . '"' . $page_checked . ' />' . $pr_page_title  . '<br />' . "\r\n";
			endwhile;
		endif;
		echo '<input type="submit" name="submit" class="button" value="Submit" />&nbsp;&nbsp;';
		echo '<input type="reset" name="reset" class="button" value="Reset" />&nbsp;&nbsp;';
		echo '<input type="button" name="cancel" value="Cancel" class="button" onclick="javascript:history.go(-1)" />' . "\r\n";
		echo '</form>' . "\r\n";
		echo '</div>' . "\r\n";
	endif;
}

// Add Actions
add_action ( 'admin_menu' , 'pr_options_page' ) ;
add_action ( 'activate_pagerestrict/pagerestrict.php' , 'pr_init' );
?>
