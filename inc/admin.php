<?php
/*
Part of WordPress Plugin: Page Restrict
Plugin URI: http://sivel.net/wordpress/
*/

//
$pr_version = '1.5.8.0';

// Check the version in the options table and if less than this version perform update
function pr_ver_check () {
	global $pr_version;
	if ( ( pr_get_opt ( 'version' ) < $pr_version ) || ( ! pr_get_opt ( 'version' ) ) ) :
		$pr_options['version'] = $pr_version;
		$pr_options['pages'] = explode ( ',' , pr_get_opt ( 'pages' ) );
		$pr_options['method'] = pr_get_opt ( 'method' );
		$pr_options['message'] = 'You are required to login to view this page.';
		$pr_options['loginform'] = true;
		pr_delete ();
		add_option ( 'pr_options' , $pr_options );
	endif;
}

// Initialize the default options during plugin activation
function pr_init () {
	global $pr_version;
	if ( ! pr_get_opt( 'version' ) ) :
		$pr_options['version'] = $pr_version;
		$pr_options['pages'] = array ();
		$pr_options['method'] = 'selected';
		$pr_options['message'] = 'You are required to login to view this page.';
		$pr_options['loginform'] = true;
		add_option ( 'pr_options' , $pr_options ) ;
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
	add_options_page ( 'Page Restrict' , 'Page Restrict' , 'publish_pages' , 'pagerestrict' , 'pr_admin_page' );
}

// The options page
function pr_admin_page () {
	pr_ver_check ();
	if ( $_POST['action'] == 'update' ) :
		if ( $_POST['update'] == 'pages' ) :
			$page_ids = $_POST['page_id'];
		else :
			$page_ids = pr_get_opt ( 'pages' );	
		endif;
		$pr_options['pages'] = $page_ids;
		$pr_method = $_POST['method'];
		$pr_options['method'] = $pr_method;
		$pr_options['version'] = pr_get_opt ( 'version' );
		$pr_message = $_POST['message'];
		$pr_options['message'] = $pr_message;
		if ( $_POST['loginform'] == 'true' )
			$pr_options['loginform'] = true;
		else
			$pr_options['loginform'] = false;
		update_option ( 'pr_options' , $pr_options );
		echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
	else :
		$page_ids = pr_get_opt ( 'pages' );
		$pr_method = pr_get_opt ( 'method' );
		$pr_message = pr_get_opt ( 'message' );
	endif;
?>
	<div class="wrap">
		<h2>Page Restrict Options</h2>
		<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
                        <input type="hidden" name="action" value="update" />
			<h3>General Options</h3>
			<p>These options pertain to the gerneral operation of the plugin</p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						Restriction Message
					</th>
					<td>
						<textarea cols="64" rows="4" name="message"><?php echo $pr_message; ?></textarea>
						<br />
						This field can contain HTML.
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						Show Login Form
					</th>
					<td>
						<select name="loginform">
							<option value="true"<?php selected ( true , pr_get_opt ( 'loginform' ) ); ?>>Yes</option>
							<option value="false"<?php selected ( false , pr_get_opt ( 'loginform' ) ); ?>>No</option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						Restriction Method
					</th>
					<td>
						<select name="method">
							<option value="all"<?php selected ( 'all' , pr_get_opt ( 'method' ) ); ?>>All</option>
							<option value="none"<?php selected ( 'none' , pr_get_opt ( 'method' ) ); ?>>None</option>
							<option value="selected"<?php selected ( 'selected' , pr_get_opt ( 'method' ) ); ?>>Selected</option>
						</select>
					</td>
				</tr>
			</table>
<?php
	if ( $pr_method == 'selected' ) :
?>
			<h3>Page List</h3>
			<p>Select the pages that you wish to restrict to logged in users.</p>
			<input type="hidden" name="update" value="pages" />
			<table class="form-table">
<?php
		$avail_pages = get_pages ();
		foreach ( $avail_pages as $page ) :
?>
				<tr valign="top">
					<th scope="row">
						<?php echo $page->post_title; ?>
					</th>
					<td>
						<input type="checkbox" name="page_id[]" value="<?php echo $page->ID; ?>"<?php checked ( true , in_array ( $page->ID , $page_ids ) ); ?> />
					</td>
				</tr>
<?php
		endforeach;
?>
			</table>
<?php
	endif;
?>
			<br />
			<p class="submit">
				<input type="submit" name="submit" class="button" value="Save Changes" />
			</p>
		</form>
	</div>
<?php
}

/**
 * The meta box
 */
function page_restriction_status_meta_box ( $post ) {
	$post_ID = $post->ID;
?>
	<p>
		<input name="pr" type="hidden" value="update" />
		<label for="restriction_status" class="selectit">
			<input type="checkbox" name="restriction_status" id="restriction_status"<?php if ( in_array ( $post_ID , pr_get_opt ( 'pages' ) ) ) echo ' checked="checked"'; ?>/>
			Restrict Page
		</label>
	</p>
	<p>These settings apply to this page only. For a full list of restriction statuses see the <a href="options-general.php?page=pagerestrict">global options page</a>.</p>
<?php
}

/**
 * Add meta box to create/edit page pages
 */
function pr_meta_box () {
	add_meta_box ( 'pagerestrictionstatusdiv' , 'Restriction' , 'page_restriction_status_meta_box' , 'page' , 'normal' , 'high' );
}

/**
 * Get custom POST vars on edit/create page pages and update options accordingly
 */
function pr_meta_save () {
	if ( $_POST['pr'] == 'update' ) :
		$post_ID = $_POST['post_ID'];
		$restricted_pages = pr_get_opt ( 'pages' );
		if ( ! is_array ( $restricted_pages ) )
			$restricted_pages = array ();
		if ( $_POST['restriction_status'] == 'on' ) :
			$restricted_pages[] = $post_ID ;
			$pr_options['pages'] = $restricted_pages;
		else :
			$pr_options['pages'] = array_filter ( $restricted_pages , 'pr_array_delete' );
		endif;
		$pr_options['loginform'] = pr_get_opt ( 'loginform' );
		$pr_options['method'] = pr_get_opt ( 'method' );
		$pr_options['message'] = pr_get_opt ( 'message' );
		$pr_options['version'] = pr_get_opt ( 'version' );
		update_option ( 'pr_options' , $pr_options );
	endif;
}

/**
 * Remove item from array
 */
function pr_array_delete ( $item ) {
	return ( $item !== $_POST['post_ID'] );
}

/**
 * Activation hook
 */
register_activation_hook ( dirname ( dirname ( __FILE__ ) ) . '/pagerestrict.php' , 'pr_init' );

/**
 * Tell WordPress what to do.  Action hooks.
 */
add_action ( 'admin_menu' , 'pr_meta_box' );
add_action ( 'save_post' , 'pr_meta_save' );
add_action ( 'admin_menu' , 'pr_options_page' ) ;
?>
