<?php
        $prefix = 'bold_timeline_item_separator';
		$class = array( $prefix );
                
		if ( $el_id == '' ) {
			$el_id = 'id_' . uniqid();
		} else {
			// $el_id = 'id_' . $el_id . '_' . uniqid();
			$el_id = $el_id;
		}		
		$id_attr = ' ' . 'id="' .  esc_attr( $el_id ) . '"';
		
		if ( $el_class != '' ) {
			$class[] = $el_class;
		}
                
        if ( $responsive != '' ) {
			$class[] = 'bold_timeline_responsive_' . $responsive;
		}
		
		$style_attr = '';
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' .  esc_attr( $el_style ) . '"';
		}

		if ( $top_margin != '' ) {
			$class[] = $prefix . '_top_margin_' . $top_margin;
		}		

		if ( $bottom_margin != '' ) {
			$class[] = $prefix . '_bottom_margin_' . $bottom_margin;
		}
		
		$output = '<div class="' .  esc_attr( implode( ' ', $class ) ) . '"' . $id_attr . $style_attr . '><div class="bold_timeline_item_separator_inner">';
		
		$output .= '</div></div>';
