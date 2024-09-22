<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function bold_timeline_get_bold_timelines() {
	$args = array(
        'post_type'			=> 'bold-timeline',
		'post_status'		=> 'publish',
        'posts_per_page'	=> -1
    );
	$bold_timelines = new WP_Query($args);

	$bold_timelines_data = array();	
	if ( isset( $bold_timelines ) && $bold_timelines->have_posts() ) {
		while ( $bold_timelines->have_posts() ) {			
			$bold_timelines->the_post();
			$post		= get_post();
			$post_id	= get_the_ID();

			$posts_data			= array();
			$post_data['ID']	= $post_id;
			$post_data['title'] = get_the_title( $post_id );
			$bold_timelines_data[]	= $post_data;
		}
		wp_reset_postdata();			
	} 
	
	$bold_timelines_arr = array( '0' => esc_html__( '', 'bold-timeline' ) );
	foreach ( $bold_timelines_data as $item) {		
		if ( $item ) {			
			$bold_timelines_arr[ $item["ID"] ] = $item["title"]; 
		}
	}
	
	return $bold_timelines_arr;
}


function bold_timeline_get_bold_timelines_wpbakery_bb() {
	$args = array(
        'post_type'			=> 'bold-timeline',
		'post_status'		=> 'publish',
        'posts_per_page'	=> -1
    );
	$bold_timelines = new WP_Query($args);

	$bold_timelines_data = array();	
	if ( isset( $bold_timelines ) && $bold_timelines->have_posts() ) {
		while ( $bold_timelines->have_posts() ) {			
			$bold_timelines->the_post();
			$post		= get_post();
			$post_id	= get_the_ID();

			$posts_data			= array();
			$post_data['ID']	= $post_id;
			$post_data['title'] = get_the_title( $post_id );
			$bold_timelines_data[]	= $post_data;
		}
		wp_reset_postdata();			
	} 
	
	$bold_timelines_arr = array( esc_html__( '', 'bold-timeline' ) => '0' );
	foreach ( $bold_timelines_data as $item) {	
		if ( $item ) {	
			$bold_timelines_arr[$item["title"]] =   $item["ID"] ;
		}
	}
	
	return $bold_timelines_arr;
}