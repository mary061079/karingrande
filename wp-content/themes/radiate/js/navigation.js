/**
 * navigation.js
 *
 * Handles toggling the navigation menu for small screens.
 */
( function() {
	var container, button, menu;

	container = document.getElementById( 'site-navigation' );
	if ( ! container )
		return;

	button = container.getElementsByTagName( 'h1' )[0];
	if ( 'undefined' === typeof button )
		return;

	menu = container.getElementsByTagName( 'ul' )[0];

	// Hide menu toggle button if menu is empty and return early.
	if ( 'undefined' === typeof menu ) {
		button.style.display = 'none';
		return;
	}

	if ( -1 === menu.className.indexOf( 'nav-menu' ) )
		menu.className += 'nav-menu';

	button.onclick = function() {
		if ( -1 !== container.className.indexOf( 'main-small-navigation' ) )
			container.className = container.className.replace( 'main-small-navigation', 'main-navigation' );
		else
			container.className = container.className.replace( 'main-navigation', 'main-small-navigation' );
	};
} )();

jQuery( document ).ready( function() {
  jQuery( '#menu-menu-1' ).on( 'click', '#menu-item-101 > a, #menu-item-112 > a', function(e) {
    e.preventDefault();
  })
  jQuery( '#menu-item-101, #menu-item-112' ).addClass( 'dropdown' );
  jQuery( '#menu-item-101' ).append( jQuery( '.boys-of-vine' ) );
  jQuery( '#menu-item-112' ).append( jQuery( '.nickelodeon-news' ) );
})
                           