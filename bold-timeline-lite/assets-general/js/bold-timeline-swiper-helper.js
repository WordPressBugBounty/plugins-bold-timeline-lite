/* Hero posts Swiper slider helper */

function convertToUnitlessPixels ( e ) { 
	if( e === undefined ) return '20';
	var e_gap = e.css( "--hero-posts-base-gap" );
	if( e_gap === undefined ) return '20';
	return ( e_gap.substr( e_gap.length - 2 ) == 'px' ) ? parseFloat( e_gap ) : parseFloat( e_gap ) * parseFloat( e.css('font-size') ); 
}

function initHeroPostsSwiper() {
	
	var heroPostsSliderSelector = '.hero-posts-slider-swiper-holder';
	var defaultOptions = {
		direction: "horizontal",
		loop: true,
		autoplay: false,
	};

	var heroPostsSlidersCollection = document.querySelectorAll( heroPostsSliderSelector );
	
	console.log( heroPostsSlidersCollection );
		
	[].forEach.call( heroPostsSlidersCollection, function( heroPostsSlider, index, arr ) {
		var data = heroPostsSlider.getAttribute('data-hero-posts-swiper') || {};
		if ( data ) {
			var dataOptions = JSON.parse( data );
		}
		
		heroPostsSlider.options = Object.assign( {}, defaultOptions, dataOptions);
		
		if ( typeof heroPostsSlider.options.spaceBetween !== 'undefined' && heroPostsSlider.options.spaceBetween === 'inherit' ) {
			heroPostsSlider.options.spaceBetween = convertToUnitlessPixels ( jQuery( heroPostsSlider.options.parentElement ) );
		}
		
		var heroPostsSliderSwiper = new Swiper( heroPostsSlider, heroPostsSlider.options );

		/* stop on hover */
		
		if ( typeof heroPostsSlider.options.autoplay !== 'undefined' && heroPostsSlider.options.autoplay !== false ) {
			heroPostsSlider.addEventListener('mouseenter', function(e) { heroPostsSliderSwiper.autoplay.stop(); });
			heroPostsSlider.addEventListener('mouseleave', function(e) { heroPostsSliderSwiper.autoplay.start(); });
		}
	});
	jQuery( ".hero-posts-slider-swiper-holder" ).addClass( "hero-posts-slider-swiper-initialized" );		
}
	function checkElementor() {
		console.log( "elementor/frontend/init" );
	}

/* Hero posts Swiper slider init */

jQuery( document ).ready( function(){
	initHeroPostsSwiper();
});

/**/

jQuery( window ).on( "elementor/frontend/init", checkElementor );



