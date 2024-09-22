<?php
		$prefix = 'bold_timeline_item_text';
		$class = array( $prefix );
                
		if ( $el_id == '' ) {
			$el_id = 'id_' . uniqid();
		} else {
			// $el_id = 'id_' . $el_id . '_' . uniqid();
			$el_id = $el_id;
		}		
		$id_attr = ' ' . 'id="' . esc_attr( $el_id ) . '"';
		
		if ( $el_class != '' ) {
			$class[] = $el_class;
		}
                
        if ( $responsive != '' ) {
			$class[] = 'bold_timeline_responsive_' . $responsive;
		}
		
		$style_attr = '';
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}   
		
		$output = '<div class="' . esc_attr( implode( ' ', $class ) ) . '"' . $id_attr . $style_attr . '>';
		if ( $content != '' ) $output .= '<div class="bold_timeline_item_text_inner">' . wptexturize( do_shortcode( $content ) ) . '</div>';
		$output .= '</div>';