<?php
/**
 * Misc WordPress functions.
 */

/**
 * Custom WP setup.
 */
function custom_wordpress_setup() {
	// Enable tags for Pages
	register_taxonomy_for_object_type( 'post_tag', 'page' );

	// Enable excerpts for pages
	add_post_type_support( 'page', 'excerpt' );

	// Disable the hiding of big images
	add_filter( 'big_image_size_threshold', '__return_false' );
	add_filter( 'max_srcset_image_width', '__return_false' );
}
add_action( 'init', 'custom_wordpress_setup' );

/**
 * Setup theme
 */
function custom_theme_setup() {
	// Turn on menus
	add_theme_support( 'menus' );

	// Enable HTML5 support
	add_theme_support( 'html5', array( 'gallery', 'caption' ) );
}
add_action( 'after_setup_theme', 'custom_theme_setup' );

/**
 * Prevent Google from indexing any PHP generated part of the API.
 */
function add_nofollow_header() {
	header( 'X-Robots-Tag: noindex, nofollow', true );
}
add_action( 'send_headers', 'add_nofollow_header' );

/**
 * Return the fuxt_home_url value when code requests the Site Address (URL)
 * 
 * @see NOTE FROM FABIAN: REMOVING THIS WILL BREAK THE API (CORS ERROR).
 *
 * @param string      $url         The complete home URL including scheme and path.
 * @param string      $path        Path relative to the home URL. Blank string if no path is specified.
 * @param string|null $orig_scheme Scheme to give the home URL context. Accepts 'http', 'https', 'relative', 'rest', or null.
 * @return string
 */
function fuxt_get_home_url( $url, $path, $orig_scheme ) {
	if ( 'rest' !== $orig_scheme ) {
		$fuxt_home_url = get_option( 'fuxt_home_url' );
	} else {
		$fuxt_home_url = get_option( 'siteurl' );
	}

	if ( ! empty( $fuxt_home_url ) ) {
		$url = untrailingslashit( $fuxt_home_url );

		if ( $path && is_string( $path ) ) {
			$url .= '/' . ltrim( $path, '/' );
		}
	}

	return $url;
}
add_filter( 'home_url', 'fuxt_get_home_url', 99, 3 );

/**
 * Update the Site Address value in General Settings panel to return fuxt override
 *
 * @param string $value Site address value.
 * @return string
 */
function fuxt_filter_home_option( $value ) {
	global $pagenow;
	if ( $pagenow === 'options-general.php' ) {
		$value = get_option( 'fuxt_home_url' );
	}
	return $value;
}
add_filter( 'option_home', 'fuxt_filter_home_option', 99, 1 );

/**
 * Whitelist siteurl for wp_safe_redirect.
 *
 * @param string[] $hosts An array of allowed host names.
 * @return string[]
 */
function fuxt_allow_siteurl_safe_redirect( $hosts ) {
	$wpp = wp_parse_url( site_url() );
	return array_merge( $hosts, array( $wpp['host'] ) );
}
add_filter( 'allowed_redirect_hosts', 'fuxt_allow_siteurl_safe_redirect' );
