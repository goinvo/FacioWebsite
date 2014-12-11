<?php
/*

Plugin Name: Simple Colorbox
Plugin URI: http://pixopoint.com/products/simple-colorbox/
Description: A WordPress plugin which adds a Colorbox to your site with no configuration required.
Author: Ryan Hellyer
Version: 1.2.2
Author URI: http://pixopoint.com/

Copyright (c) 2012 Ryan Hellyer

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
license.txt file included with this plugin for more information.

*/


/**
 * Define constants
 * 
 * @since 1.0
 * @author Ryan Hellyer <ryan@pixopoint.com>
 */
define( 'SIMPLECOLORBOX_DIR', dirname( __FILE__ ) . '/' ); // Plugin folder DIR
define( 'SIMPLECOLORBOX_URL', plugins_url( '', __FILE__ ) ); // Plugin folder URL
define( 'SIMPLECOLORBOX_VERSION', '1.2.1' );
//define( 'SIMPLECOLORBOX_THEME', '5' ); // Can be used to over-ride the default theme
//define( 'SIMPLECOLORBOX_OPACITY', '0.2' );
//define( 'SIMPLECOLORBOX_WIDTH', '50' );
//define( 'SIMPLECOLORBOX_HEIGHT', '50' );

/**
 * Instantiate the plugin
 * 
 * @copyright Copyright (c), Ryan Hellyer
 * @author Ryan Hellyer <ryan@pixopoint.com>
 * @since 1.0
 */
new Simple_Colorbox();

/**
 * Simple Colorbox class
 * Adds the required CSS and JS files to front-end of the site
 * 
 * This class may be abstracted from the plugin and used in your own theme if you prefer.
 * This can allow you to offer easy to use colorbox functionality without the hassle of 
 * users needing to install a complicated plugin.
 * 
 * @copyright Copyright (c), Ryan Hellyer
 * @author Ryan Hellyer <ryan@pixopoint.com>
 * @since 1.0
 */
class Simple_Colorbox {

	/**
	 * Class constructor
	 * Adds all the methods to appropriate hooks or shortcodes
	 * 
	 * @since 1.0
	 * @author Ryan Hellyer <ryan@pixopoint.com>
	 */
	public function __construct() {

		// Add action hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'external_css' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'external_scripts' ) );
		add_action( 'wp_head',            array( $this, 'simplecolorbox_ad' ) );
		add_action( 'wp_head',            array( $this, 'inline_scripts' ) );

	}

	/**
	* Print scripts onto pages
	 * 
	 * @since 1.0
	 * @author Ryan Hellyer <ryan@pixopoint.com>
	*/
	public function external_scripts() {

		wp_enqueue_script(
			'colorbox',
			SIMPLECOLORBOX_URL . '/scripts/jquery.colorbox-min.js',
			array( 'jquery' ),
			1.0,
			true
		);		
	}

	/**
	 * Print scripts onto pages
	 * 
	 * @since 1.0
	 * @author Ryan Hellyer <ryan@pixopoint.com>
	 */
	public function inline_scripts() {

		// Do definition check - used by themes/plugins to over-ride the default settings
		if ( ! defined( 'SIMPLECOLORBOX_OPACITY' ) )
			define( 'SIMPLECOLORBOX_OPACITY', '0.6' );
		if ( ! defined( 'SIMPLECOLORBOX_WIDTH' ) )
			define( 'SIMPLECOLORBOX_WIDTH', '95' );
		if ( ! defined( 'SIMPLECOLORBOX_HEIGHT' ) )
			define( 'SIMPLECOLORBOX_HEIGHT', '95' );
		if ( ! defined( 'SIMPLECOLORBOX_SLIDESHOW' ) )
			define( 'SIMPLECOLORBOX_SLIDESHOW', 'group' );

		// Colorbox settings
		echo '
<script>
	jQuery(function($){
		$("a[href$=\'jpg\'],a[href$=\'jpeg\'],a[href$=\'png\'],a[href$=\'bmp\'],a[href$=\'gif\'],a[href$=\'JPG\'],a[href$=\'JPEG\'],a[href$=\'PNG\'],a[href$=\'BMP\'],a[href$=\'GIF\']").colorbox({
			maxWidth:\'' . SIMPLECOLORBOX_WIDTH . '%\',
			maxHeight:\'' . SIMPLECOLORBOX_HEIGHT . '%\',
			opacity:\'' . SIMPLECOLORBOX_OPACITY . '\',
			rel:\'' . SIMPLECOLORBOX_SLIDESHOW . '\'
		});
	});
</script>';
	}

	/*
	 * Adds CSS to front end of site
	 * 
	 * @since 1.0
	 * @author Ryan Hellyer <ryan@pixopoint.com>
	 */
	public function external_css() {

		// Do definition check - used by themes/plugins to over-ride the default settings
		if ( ! defined( 'SIMPLECOLORBOX_THEME' ) )
			define( 'SIMPLECOLORBOX_THEME', '1' );

		// Load the stylesheet
		wp_enqueue_style( 'colorbox', SIMPLECOLORBOX_URL . '/themes/theme' . SIMPLECOLORBOX_THEME . '/colorbox.css', false, '', 'screen' );
	}

	/**
	* Display notice about the plugin in head
	 * 
	 * @since 1.0
	 * @author Ryan Hellyer <ryan@pixopoint.com>
	*/
	public function simplecolorbox_ad() {

		echo "\n<!-- Simple Colorbox Plugin v" . SIMPLECOLORBOX_VERSION ." by Ryan Hellyer ... http://pixopoint.com/products/simple-colorbox/ -->\n";

	}

}

