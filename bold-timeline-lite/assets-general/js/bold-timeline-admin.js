"use strict";

(function( $ ) {
	
	window.bt_bb_add_root = function( obj, base ) {
		if ( ! window.bt_bb_custom_dialog ) {
			window.bt_bb_custom_dialog = {};
		}
		window.bt_bb_custom_dialog.title = window.bt_bb_hero_text.add_template;
				
		var content = '';
		var extra_class = '';
		var item_name_arr = new Array();
		var item_base_name = '';
		var item_new_base_name = '';
		var item_base_url = 'https://hero-posts.bold-themes.com/demos/';
		
		Object.keys( window.bt_bb_hero_templates ).forEach( item => {
			extra_class = ( window.bt_bb_hero_templates[ item ].pro_only === true ) ? ' bt_bb_hero_template_item_pro' : '';
			item_name_arr = item.split('-').map( name => name.toLowerCase() );
			// Show title start
			item_new_base_name = window.bt_bb_hero_templates[ item ].name.split(' ')[0];
			if ( item_new_base_name != item_base_name ) {
				item_base_name = item_new_base_name;
				// content += '<div class="bt_bb_hero_template_item bt_bb_hero_template_item_title" data-title="' + item_new_base_name + '">' + item_new_base_name + '</div>';	
			}
			// Show title end
			content += '<div class="bt_bb_hero_template_item' + extra_class + '" data-item="' + item + '">';
				content += '<div class="bt_bb_hero_template_item_inner">';
					content += '<a class="bt_bb_hero_template_item_inner_href" href="' + item_base_url + item_name_arr[0] + '-' + item_name_arr[1] + '/#' + item_name_arr.join('-') + '" title="' + window.bt_bb_hero_text.view_demo + ' ' + window.bt_bb_hero_templates[ item ].name + '" target="_demo_page"><span>Live demo</span></a>';
					if ( window.bt_bb_hero_templates[ item ].pro_only ) {
						content += '<span class="bt_bb_hero_template_item_inner_pro_only">';
							content += '<span class="bt_bb_hero_template_item_inner_pro_only_info">Not avaliable in lite version</span>';
							content += '<a class="bt_bb_hero_template_item_inner_pro_only_href" href="https://hero-posts.bold-themes.com/" title="Get Hero posts" target="_blank">Get Hero posts</a>';
						content += '</span>';	
					}
					content += '<div class="bt_bb_hero_template_item_inner_add"  title="' + window.bt_bb_hero_text.add_template + ': ' + window.bt_bb_hero_templates[ item ].name + '"><span>' + window.bt_bb_hero_templates[ item ].name + '</span></div>';
					content += '<img src="' + window.bt_bb_hero_templates_url +  'images/' + item + '.jpg' + '" title="' + window.bt_bb_hero_text.add_template + ': ' + window.bt_bb_hero_templates[ item ].name + '">';
				content += '</div>';
			content += '</div>';
		});

		$( '#bt_bb_dialog .bt_bb_dialog_content' ).html( content );
		
		$( '#bt_bb_dialog' ).addClass( 'bt_bb_dialog_add_template' );
		
		window.bt_bb_dialog.show( 'custom' );
	}
	
	$( document ).ready(function() {
		$( '#bt_bb_dialog' ).on( 'click', '.bt_bb_hero_template_item:not(.bt_bb_hero_template_item_pro) .bt_bb_hero_template_item_inner_href', function( e ) {
			e.stopPropagation();
		});
		$( '#bt_bb_dialog' ).on( 'click', '.bt_bb_hero_template_item:not(.bt_bb_hero_template_item_pro):not(.bt_bb_hero_template_item_title)', function( e ) {
			var item = $( this );
			var dialog_content = $( '.bt_bb_dialog_content' );
			item.addClass( 'loading' );
			dialog_content.addClass( 'loading' );
			var data = {
				'action': 'hero_posts_get_template',
				'item': item.data( 'item' ),
			};
			$.ajax({
				type: 'POST',
				url: window.BTAJAXURL,
				data: data,
				async: true,
				success: function( response ) {
					item.removeClass( 'loading' );
					dialog_content.removeClass( 'loading' );
					var import_json = decodeURIComponent( window.bt_bb_b64DecodeUnicode( response ) );
					var import_json_obj = JSON.parse( import_json );
					for ( var i = 1; i <= import_json_obj.length; i++ ) {
						var obj = JSON.parse( decodeURIComponent( window.bt_bb_b64DecodeUnicode( import_json_obj[ i - 1 ].bt_bb_cb ) ) );
						obj = bt_bb_set_new_keys( obj );
						window.bt_bb_state_current.children.push( obj );
						bt_bb_event( 'refresh' );
					};
					window.bt_bb_dialog.hide();
				}
			});
		});
	});

}( jQuery ));