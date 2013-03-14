<?php
/*
 * Plugin Name: AutoVer
 * Plugin URI: http://wordpress.org/extend/plugins/autover/
 * Description: Automatically version your CSS and JS files.
 * Author: PressLabs
 * Version: 1.0
 * Author URI: http://www.presslabs.com/
 */

//--------------------------------------------------------------------

function autover_activate() {
	add_option('autover_dev_mode', array('1','1') );
}
register_activation_hook(__FILE__,'autover_activate');

//--------------------------------------------------------------------

function autover_deactivate() {
	delete_option('autover_dev_mode');
}
register_deactivation_hook(__FILE__,'autover_deactivate');

//--------------------------------------------------------------------
//
// Add settings link on plugin page.
//
function autover_settings_link($links) { 
	$plugin = plugin_basename(__FILE__); 
	$settings_link = '<a href="tools.php?page='.$plugin.'">Settings</a>'; 
	array_unshift($links, $settings_link);

	return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'autover_settings_link' );

//--------------------------------------------------------------------
//
// Return the string between 'start' and 'end' from 'conent'.
//
function autover_str_between( $start, $end, $content ) {
	$r = explode($start, $content);

	if (isset($r[1])) {
		$r = explode($end, $r[1]);
		return $r[0];
	}

	return '';
}

//--------------------------------------------------------------------
//
// Return the  file with the new version.
//
function autover_version_filter($src) {
	//
	// Parse the url of the input file.
	//
  	$src_parsed = parse_url( $src );
	$src_path = $src_parsed["path"];

	//
	// Extract the modification time of the input file.
	//
	$filename = $_SERVER['DOCUMENT_ROOT'] . $src_path;
	$timestamp_version = filemtime( $filename );

	//
	// If the file is not on the server then return the input file.
	//
	if ( $timestamp_version == '' ) return $src;

	//
	// Remove the old version if exist.
	//
	$src_query = autover_str_between( '?', '#', $src . '#' ); // Find the query.
	$src_with_no_query = str_replace( $src_query, '', $src ); // Remove query if exist.
	$src_with_no_query = str_replace( '?', '', $src_with_no_query ); // Remove '?' char.
	
	//
	// Create the new version.
	//
	$src_with_new_version = $src_with_no_query . '?ver=' . $timestamp_version;

	return $src_with_new_version;
}

//
// ASctivate/deactivate the filter if the options are set.
//
$autover_dev_mode = get_option('autover_dev_mode', array('','') );

if ( $autover_dev_mode[0] > '' )
	add_filter( 'style_loader_src', 'autover_version_filter', 10, 1 );

if ( $autover_dev_mode[1] > '' )
	add_filter( 'script_loader_src', 'autover_version_filter', 10, 1 );

//--------------------------------------------------------------------

function autover_update_options() {
	$autover_dev_mode_value = array( '', '' );

	$status = true;
	if ( isset( $_POST['autover_dev_mode_style'] ) ) {
		$autover_dev_mode_value[0] = '1';
		$status = false;
	}
	if ( isset( $_POST['autover_dev_mode_script'] ) ) {
		$autover_dev_mode_value[1] = '1';
		$status = false;
	}
	$status = update_option('autover_dev_mode', $autover_dev_mode_value);
	if ( ($autover_dev_mode_value[0]=='') && ($autover_dev_mode_value[1]=='') )
		$status = false;

	if ($status) { ?>
		<div id="message" class="updated fade">
			<p><strong>Saved options!</strong></p>
		</div>
	<?php } else { ?>
		<div id="message" class="error fade">
			<p>
				<strong>
					<span style="color:brown;">
						This plugin is currently not used!
					</span>
				</strong>
			</p>
		</div>
	<?php }
}

//--------------------------------------------------------------------

function autover_options() {
	if ( isset( $_POST['submit_settings'] ) ) {
		autover_update_options();
	}
?>

<div class="wrap">



<div id="icon-tools" class="icon32">&nbsp;</div>
<h2>Settings</h2>


<?php 
	$autover_dev_mode = get_option('autover_dev_mode');
	$dev_mode_checked = array('','');
	for ( $k = 0; $k < 2; $k++ )
		if ( $autover_dev_mode[$k] == '1' )
			$dev_mode_checked[$k] = ' checked="checked"';
?>

<form method="post">
<table class="form-table">
<tbody>
	<tr valign="top">
	<th scope="row">
		<label for="autover_dev_mode">Developer mode</label>
	</th>
	<td>
		<fieldset>

		<legend class="screen-reader-text"><span>Developer mode</span></legend>
		<label for="autover_dev_mode_style">
			<input name="autover_dev_mode_style" id="autover_dev_mode_style" value="<?php echo$autover_dev_mode[0];?>" type="checkbox"<?php echo$dev_mode_checked[0];?>>
			<span>CSS files</span>
		</label><br />

		<label for="autover_dev_mode_script">
			<input name="autover_dev_mode_script" id="autover_dev_mode_script" value="<?php echo$autover_dev_mode[1];?>" type="checkbox"<?php echo$dev_mode_checked[1];?>>
			<span>JavaScript files</span>
		</label><br />

		<p class="description">The file type you want to rewrite the version.</p>

		</fieldset>
	</td>
	</tr>

	<tr valign="top">
	<td>
	</td>
	</tr>
</tbody>
</table>

<p class="submit">
<input type="submit" class="button button-primary" name="submit_settings" value="Save Changes">
</p>

</form>


<p>
<h3><span style="color:red;font-weight:bold;">IMPORTANT !!!</span></h3>
If you want to use the functionality of this plugin you must add 
<strong>CSS styles</strong> and <strong>JS scripts</strong> with WordPress function 
<strong>'wp_enqueue_script'</strong> and <strong>'wp_enqueue_style'</strong>.
</p>


<p>
<h3><span style="color:black;font-weight:bold;">Example:</span></h3>

<h3><span style="color:green;font-weight:bold;">YES</span></h3>
<img src="<?php echo plugins_url('/img/wp-enqueue.png', __FILE__); ?>" alt="wp-enqueue" title="CORRECT CODE">

<h3><span style="color:red;font-weight:bold;">NO</span></h3>
<img src="<?php echo plugins_url('/img/wp-head.png', __FILE__); ?>" alt="wp-head" title="DO NOT USE THIS CODE">

<h3 style="font-weight:normal;">If you want to use <strong>'wp_enqueue_style'</strong> to add your <strong>'style.css'</strong> of your theme.<br />Add the next code to your theme file <strong>'functions.php'</strong> <span style="color:red;font-weight:bold;">and remove your &lt;link&gt; tag</span> from <strong>'header.php'</strong> which refer to your <strong>'style.css'</strong>.</h3>
<img src="<?php echo plugins_url('/img/mythemename.png', __FILE__); ?>" alt="mythemename" title="add this code to 'functions.php' file">
</p>


</div><!-- .wrap -->
<?php }

//--------------------------------------------------------------------

function autover_menu() {
	add_management_page(
		'AutoVer - Options', //'custom menu title', 
		'AutoVer', //'custom menu', 
		'administrator', //'add_users', 
		__FILE__, //$menu_slug, 
		'autover_options'
	);
}
add_action('admin_menu', 'autover_menu');

?>
