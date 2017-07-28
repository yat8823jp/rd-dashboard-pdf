<?php
/*
Plugin Name: RD Dashboard pdf
Plugin URI:
Description: Display pdf on the dashboard. For example, user's manual etc.
Author: YAT
Version: 0.0.1
Text Domain: rd-dashboard-pdf
*/


//----------------------------------------------------------------
// init
//----------------------------------------------------------------
function rddp_init() {
	add_settings_section( "rddp-section", "File upload", null, "rddp" );
	add_settings_field( "rddp-file", "pdf file to display on the dashboard", "rddp_file_display", "rddp", "rddp-section", array( 'label_for' => 'rddp-file' ) );
	register_setting( "rddp-section", "rddp-file", "rddp_file_upload" );

	add_action( 'wp_dashboard_setup', 'rddp_dashboard_widgets' );
}

add_action( "admin_init", "rddp_init" );

//----------------------------------------------------------------
// Setting page
//----------------------------------------------------------------
function rddp_setting_page() {
?>
	<div class="wrap">
		<h1><?php echo __( 'Upload pdf for display on dashboard.' , 'rd-dashboard-pdf' ); ?></h1>
		<form action="options.php" id="rddp-form" method="post">
			<?php
				settings_fields( "rddp-section" );
				do_settings_sections( "rddp" );
				submit_button();
			?>
		</form>
	</div>
<?php
}

/*
	file upload
*/
function rddp_file_upload( $option ) {
	if( !empty( $_FILES["rddp-file"]["tmp_name"] ) ) {
		$urls = wp_handle_upload( $_FILES["rddp-file"], array( 'rddp_form' => FALSE ) );
		$temp = $urls["url"];
		return $temp;
	}
	return $option;
}

/*
	file display
*/
function rddp_file_display() {
	?>
		<input type="file" name="rddp-file" id="rddp-file">
		<?php echo get_option( 'rddp-file' ); ?>
	<?php
}

//----------------------------------------------------------------
// Display Dashboard
//----------------------------------------------------------------
function rddp_dashboard_widgets() {
	wp_add_dashboard_widget( 'my_theme_options_widget', 'pdf', 'my_dashboard_widget_function' );
}
function rddp_dashboard_widget_function() {
	echo '<object data="' . get_template_directory_uri() . '/pdf/manual.pdf"' . ' type="application/pdf" style="width: 100%; height:440px;"></object>';
	echo '<p><a href="' . get_template_directory_uri() . '/pdf/manual.pdf"' . ' target="_blank">Open pdf in browser.</a></p>';
}

//----------------------------------------------------------------
// add menu
//----------------------------------------------------------------
function rddp_add_menu() {
	add_options_page(
		'rd-dashboard-pdf',
		'rd-dashboard-pdf',
		'activate_plugins',
		'rddp',
		'rddp_setting_page'
	);
}//rpdp_add_menu
add_action( 'admin_menu', 'rddp_add_menu' );
