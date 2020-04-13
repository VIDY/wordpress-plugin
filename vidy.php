<?php

/**
 * @package Vidy
 * @version 1.0.0
 */

/*
	Plugin Name: Vidy
	Plugin URI: http://wordpress.org/plugins/vidy/
	Description: With just a hold, users can now reveal tiny hyper-relevant videos hidden behind the text of any page on the web, unlocking a whole new dimension to the internet.
	Author: Vidy
	Version: 1.0.0
	Author URI: https://www.vidy.com
	Text Domain: vidy
	Domain Path: /languages
*/

class VidySettings {
	private $vidy_settings_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'vidy_settings_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'vidy_settings_page_init' ) );
	}

	public function vidy_settings_add_plugin_page() {
		add_options_page(
			'Vidy', // page_title
			'Vidy', // menu_title
			'manage_options', // capability
			'vidy-settings', // menu_slug
			array( $this, 'vidy_settings_create_admin_page' ) // function
		);
	}

	public function vidy_settings_create_admin_page() {
		$this->vidy_settings_options = get_option( 'vidy_settings_option_name' ); ?>

		<div class="wrap">
			<h2><?php _e( 'Vidy', 'vidy' ); ?></h2>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'vidy_settings_option_group' );
					do_settings_sections( 'vidy-settings-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function vidy_settings_page_init() {
		register_setting(
			'vidy_settings_option_group', // option_group
			'vidy_settings_option_name', // option_name
			array( $this, 'vidy_settings_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'vidy_settings_setting_section', // id
			'Settings', // title
			array( $this, 'vidy_settings_section_info' ), // callback
			'vidy-settings-admin' // page
		);

		add_settings_field(
			'appid', // id
			__('Application ID', 'vidy'), // title
			array( $this, 'appid_callback' ), // callback
			'vidy-settings-admin', // page
			'vidy_settings_setting_section' // section
		);

		add_settings_field(
			'content', // id
			__('Content', 'vidy'), // title
			array( $this, 'content_callback' ), // callback
			'vidy-settings-admin', // page
			'vidy_settings_setting_section' // section
		);
	}

	public function vidy_settings_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['appid'] ) ) {
			$sanitary_values['appid'] = sanitize_text_field( $input['appid'] );
		}

		if ( isset( $input['content'] ) ) {
			$sanitary_values['content'] = sanitize_text_field( $input['content'] );
		}
		return $sanitary_values;
	}

	public function vidy_settings_section_info() {

	}

	public function appid_callback() {
		printf(
			'<input class="regular-text" type="text" name="vidy_settings_option_name[appid]" id="appid" value="%s">
			<p class="description" id="appid-description">'.__('Your Application ID','vidy').'</p>',
			isset( $this->vidy_settings_options['appid'] ) ? esc_attr( $this->vidy_settings_options['appid']) : ''
		);
	}

	public function content_callback() {
		printf(
			'<input class="regular-text" type="text" name="vidy_settings_option_name[content]" id="content" value="%s">
			<p class="description" id="appid-description">'.__('Specify a content selector, this specifies where Vidy can draw highlights','vidy').'</p>',
			isset( $this->vidy_settings_options['content'] ) ? esc_attr( $this->vidy_settings_options['content']) : ''
		);
	}
}

if ( is_admin() )
	$vidy_settings = new VidySettings();

// Method for front end
function vidy_hook() {
	$js_script = "
<script src='https://static.vidy.com/embed.min.js'></script>
<script>
	var vidy = new Vidy({
		appid: '#appid',
		postid: '#postid',
		content: '#content',
		autoload: true
	});
</script>
";


	$vidy_settings_options = get_option( 'vidy_settings_option_name' );
	$appid = $vidy_settings_options['appid']; // Appid
	$content = $vidy_settings_options['content'];// Content

	$js_script = str_replace("#appid", $appid, $js_script);
	$js_script = str_replace("#postid", get_queried_object_id(), $js_script);
	$js_script = str_replace("#content", $content, $js_script);

	echo $js_script;

}

add_action('wp_head', 'vidy_hook', 1000);
add_action( 'plugins_loaded', 'vidy_i18n' );
/**
 * Load locales.
 */
function vidy_i18n() {
	$pluginDirName = dirname( plugin_basename( __FILE__ ) );
	$domain        = 'vidy';
	$locale        = apply_filters( 'plugin_locale', get_locale(), $domain );
	load_textdomain( $domain, WP_LANG_DIR . '/' . $pluginDirName . '/' . $domain . '-' . $locale . '.mo' );
	load_plugin_textdomain( $domain, '', $pluginDirName . '/languages/' );
}
