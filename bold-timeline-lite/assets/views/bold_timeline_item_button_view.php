<?php
        $prefix = 'bold_timeline_item_button';
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

		if ( $target == '' ) {
			$target = '_self';
		}

		if ( $style != '' ) {
			$class[] = $prefix . '_style_' . $style;
		}
		
		$style_attr = '';
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' .  esc_attr( $el_style ) . '"';
		}				

		if ( $shape != '' ) {
			$class[] = $prefix . '_shape_' . $shape;
		}		

		if ( $width != '' ) {
			$class[] = $prefix . '_width_' . $width;
		}		

		if ( $size != '' ) {
			$class[] = $prefix . '_size_' . $size;
		}
		
		$css_override = '';
		Bold_Timeline::$crush_vars = array();
		
		Bold_Timeline::$crush_vars['defaultButtonColor'] = '';
		if ( $color != '' ) {
			Bold_Timeline::$crush_vars['defaultButtonColor'] = $color;	
		}
		require( dirname(__FILE__) . '/../../css-override-button.php' );
		
		$output = '<div class="' .  esc_attr( implode( ' ', $class ) ) . '"' . $id_attr . $style_attr . ' data-css-override="' . $css_override . '"><div class="bold_timeline_item_button_inner">';
		if ( $url != '' ) {
			$output .= '<a href="' . $url . '" alt=' . $title . ' target="' . esc_attr( $target ) . '" class="bold_timeline_item_button_innet_text">' . $title . '</a>';
		} else {
			$output .= '<span class="bold_timeline_item_button_innet_text">' . $title . '</span>';
		}
		$output .= '</div></div>';
