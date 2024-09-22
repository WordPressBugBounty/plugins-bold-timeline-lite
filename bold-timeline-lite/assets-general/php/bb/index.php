<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'admin_bar_init', 'bold_timeline_bt_bb_fe_init' );

function bold_timeline_bt_bb_fe_init() {
	if ( ! bt_bb_active_for_post_type_fe() || ( isset( $_GET['preview'] ) && ! isset( $_GET['bt_bb_fe_preview'] ) ) ) {
		return;
	}

	if ( current_user_can( 'edit_pages' ) ) {
		if (class_exists('BT_BB_FE')){
			BT_BB_FE::$elements = bold_timeline_add_bt_bb_fe();
		}
	}
}

function bold_timeline_add_bt_bb_fe() {
	$elements = BT_BB_FE::$elements;
	$elements[ 'bt_bb_bold_timeline' ] = array(
		'edit_box_selector' => '',
		'ajax_mejs' => true,
		'params' => array(
			'bold_timeline' => array(),
		),
	);

	return $elements;
}