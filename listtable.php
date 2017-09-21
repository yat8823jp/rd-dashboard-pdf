<?php
/*
Plugin Name: RD Dashboard pdf
Plugin URI:
Description: Display pdf on the dashboard. For example, user's manual etc.
Author: YAT
Version: 1.1.0
Text Domain: rd-dashboard-pdf
*/
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

echo ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class RD_List_Table extends WP_List_Table {
	function __construct() {
		parent:: __construct( array (
			'singular' => 'wp_list_text_link',
			'plural'   => 'wp_list_test_links',
			'ajax'     => false
		) );
	}
	function get_columns() {
		return $columns = array(
			'id'           => __( 'ID' ),
			'url'          => __( 'Url' ),
			'name'         => __( 'Name' ),
			'pdf-title'    => __( 'title' )
		);
	}
	public function get_sortable_columns() {
		return $sortable = array(
			'id' => 'id'
		);
	}
	function prepare_items() {
	}
}


// $rd_list_table = new RD_List_Table;
