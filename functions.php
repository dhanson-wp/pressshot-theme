<?php
/**
 * PressShot theme setup.
 *
 * @package pressshot-theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue a theme stylesheet if the file exists.
 *
 * @param string $handle Style handle.
 * @param string $file   Path relative to the theme root.
 */
function pressshot_theme_enqueue_style( $handle, $file ) {
	$path = get_theme_file_path( $file );
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_style( $handle, get_theme_file_uri( $file ), array(), (string) filemtime( $path ) );
}

/**
 * Whether the Light Dark Toggle block is registered (plugin active).
 *
 * @return bool
 */
function pressshot_theme_has_toggle() {
	return WP_Block_Type_Registry::get_instance()->is_registered( 'wpcomsp/light-dark-toggle' );
}

// Dark mode needs the toggle plugin; without it the site stays light.
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( pressshot_theme_has_toggle() ) {
			pressshot_theme_enqueue_style( 'pressshot-theme-dark', 'assets/dark.css' );
			pressshot_theme_enqueue_style( 'pressshot-theme-toggle', 'assets/toggle.css' );
		}
	}
);

// Mirror the Light Dark Toggle's stored choice onto <html> before first paint.
// The plugin's own class (wpcomsp-light-dark-active) only means "light mode is
// current" and is added after domReady, so it can't drive dark styles without
// a flash. The plugin's storage key and its storage event are what we read.
//
// The script also names the switch for what it does. The plugin labels it
// "Switch to {current} mode", so it reads "Switch to dark mode" while dark is
// on. The label is set from the stored mode (or the block's default mode) on
// load, and again after each change, once the plugin has set its own.
add_action(
	'wp_head',
	function () {
		if ( ! pressshot_theme_has_toggle() ) {
			return;
		}
		$labels = array(
			'light' => __( 'Switch to dark mode', 'pressshot-theme' ),
			'dark'  => __( 'Switch to light mode', 'pressshot-theme' ),
		);
		?>
<script>
(function(){var d=document.documentElement,k='wpcomsp_light_dark_mode',n=<?php echo wp_json_encode( $labels ); ?>;function g(){try{return window.localStorage[k]||null;}catch(e){return null;}}function s(){var v=g();d.classList.toggle('pressshot-stored-dark',!!v&&v!=='light');d.classList.toggle('pressshot-stored-light',v==='light');}function l(){var v=g();document.querySelectorAll('.wp-block-wpcomsp-light-dark-toggle').forEach(function(b){var m=v?(v==='light'?'light':'dark'):(b.getAttribute('data-default-mode')==='dark'?'dark':'light');b.setAttribute('aria-label',n[m]);});}s();window.addEventListener('storage',function(){s();setTimeout(l,0);});if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',l);}else{l();}})();
</script>
		<?php
	},
	1
);

add_action(
	'login_enqueue_scripts',
	function () {
		pressshot_theme_enqueue_style( 'pressshot-theme-login', 'assets/login.css' );

		// A site icon replaces the PressShot mark (the stylesheet's fallback).
		$icon = get_site_icon_url( 84 );
		if ( $icon && wp_style_is( 'pressshot-theme-login', 'enqueued' ) ) {
			wp_add_inline_style( 'pressshot-theme-login', '.login #login h1 a{background-image:url("' . esc_url( $icon ) . '")}' );
		}
	}
);

// The login logo links to the site, not WordPress.org.
add_filter(
	'login_headerurl',
	function () {
		return home_url( '/' );
	}
);

add_filter(
	'login_headertext',
	function () {
		return esc_html( get_bloginfo( 'name' ) );
	}
);

// The templates carry the Light Dark Toggle's saved markup. Without the plugin
// that markup is a dead, empty button, so hide it.
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( pressshot_theme_has_toggle() ) {
			return;
		}
		wp_register_style( 'pressshot-theme-toggle-fallback', false, array(), '1' );
		wp_enqueue_style( 'pressshot-theme-toggle-fallback' );
		wp_add_inline_style( 'pressshot-theme-toggle-fallback', '.wp-block-wpcomsp-light-dark-toggle{display:none}' );
	}
);
