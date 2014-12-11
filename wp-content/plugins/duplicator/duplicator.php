<?php
/*
Plugin Name: Duplicator
Plugin URI: http://www.lifeinthegrid.com/duplicator/
Description: Create a full WordPress backup of your files and database with one click. Duplicate and move an entire site from one location to another in 3 easy steps. Create full snapshot of your site at any point in time.
Version: 0.3.2
Author: LifeInTheGrid
Author URI: http://www.lifeinthegrid.com
License: GPLv2 or later
*/

/* ================================================================================ 
Copyright 2011-2012  Cory Lamle 
Copyright 2011  Gaurav Aggarwal  
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	
Contributors:
 Many thanks go out to Gaurav Aggarwal for starting the Backup and Move Plugin.
 This project is a fork of that project
 See http://www.logiclord.com/backup-and-move/ for more details.		

================================================================================ */

require_once("define.php");

if (is_admin() == true) {
	
	$_tmpDuplicatorOptions = get_option('duplicator_options', false);
	$GLOBALS['duplicator_opts'] = ($_tmpDuplicatorOptions == false) ? array() : @unserialize($_tmpDuplicatorOptions);
	
	//OPTIONS
	$GLOBALS['duplicator_opts']['dbhost']		= isset($GLOBALS['duplicator_opts']['dbhost'])			? $GLOBALS['duplicator_opts']['dbhost']			: '';
	$GLOBALS['duplicator_opts']['dbname']		= isset($GLOBALS['duplicator_opts']['dbname'])			? $GLOBALS['duplicator_opts']['dbname']			: '';
	$GLOBALS['duplicator_opts']['dbuser']		= isset($GLOBALS['duplicator_opts']['dbuser'])			? $GLOBALS['duplicator_opts']['dbuser'] 		: '';
	$GLOBALS['duplicator_opts']['dbiconv']		= isset($GLOBALS['duplicator_opts']['dbiconv'])			? $GLOBALS['duplicator_opts']['dbiconv']		: '1';
	$GLOBALS['duplicator_opts']['dbadd_drop']	= isset($GLOBALS['duplicator_opts']['dbadd_drop'])		? $GLOBALS['duplicator_opts']['dbadd_drop']     : '0';
	$GLOBALS['duplicator_opts']['nurl']  		= isset($GLOBALS['duplicator_opts']['nurl'] ) 			? $GLOBALS['duplicator_opts']['nurl']  			: '';
	$GLOBALS['duplicator_opts']['email-me']		= isset($GLOBALS['duplicator_opts']['email-me'])		? $GLOBALS['duplicator_opts']['email-me']		: '0';
	$GLOBALS['duplicator_opts']['log_level'] 	= isset($GLOBALS['duplicator_opts']['log_level'])		? $GLOBALS['duplicator_opts']['log_level']  	: '0';
	$GLOBALS['duplicator_opts']['max_time']		= is_numeric($GLOBALS['duplicator_opts']['max_time'])	? $GLOBALS['duplicator_opts']['max_time']   	: 1000;
	$GLOBALS['duplicator_opts']['max_memory']	= isset($GLOBALS['duplicator_opts']['max_memory'])  	? $GLOBALS['duplicator_opts']['max_memory'] 	: '1000M';
	$GLOBALS['duplicator_opts']['email_others']	= isset($GLOBALS['duplicator_opts']['email_others']) 	? $GLOBALS['duplicator_opts']['email_others']	: '';
	$GLOBALS['duplicator_opts']['skip_ext']		= isset($GLOBALS['duplicator_opts']['skip_ext'])  		? $GLOBALS['duplicator_opts']['skip_ext'] 		: '';
	$GLOBALS['duplicator_opts']['dir_bypass']	= isset($GLOBALS['duplicator_opts']['dir_bypass'])		? $GLOBALS['duplicator_opts']['dir_bypass']		: '';
	$GLOBALS['duplicator_opts']['rm_snapshot']  = isset($GLOBALS['duplicator_opts']['rm_snapshot']) 	? $GLOBALS['duplicator_opts']['rm_snapshot'] 	: '1';
	
	//Default Arrays
	$GLOBALS['duplicator_bypass-array']	  = explode(";", $GLOBALS['duplicator_opts']['dir_bypass'], -1);
	$GLOBALS['duplicator_bypass-array']   = count($GLOBALS['duplicator_bypass-array']) 			  ? $GLOBALS['duplicator_bypass-array'] 			      : null;
	$GLOBALS['duplicator_skip_ext-array'] = explode(";", $GLOBALS['duplicator_opts']['skip_ext']) ? explode(";", $GLOBALS['duplicator_opts']['skip_ext']) : array();

	require_once 'inc/functions.php';
	require_once 'inc/class.zip.php';
	require_once 'inc/actions.php';
	
	/* ACTIVATION 
	Only called when plugin is activated */
	function duplicator_activate() {

		global $wpdb;
		$table_name = $wpdb->prefix . "duplicator";
		
		//PRIMARY KEY must have 2 spaces before for dbDelta
		$sql = "CREATE TABLE `{$table_name}` (
		 id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT  PRIMARY KEY,
		 token 	 	 VARCHAR(25) NOT NULL, 
		 packname 	 VARCHAR(250) NOT NULL, 
		 zipname 	 VARCHAR(250) NOT NULL, 
		 zipsize 	 INT (11),
		 created 	 DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		 owner 		 VARCHAR(60) NOT NULL,
		 settings 	 LONGTEXT NOT NULL)" ;

		require_once(DUPLICATOR_WPROOTPATH . 'wp-admin/includes/upgrade.php');
		@dbDelta($sql);
		
		$duplicator_opts = array(
			'dbhost'		=>'localhost',
			'dbname'		=>'',
			'dbuser'		=>'',
			'nurl'			=>'',
			'email-me'		=>"{$GLOBALS['duplicator_opts']['email-me']}",
			'email_others'	=>"{$GLOBALS['duplicator_opts']['email_others']}",
			'max_time'		=>$GLOBALS['duplicator_opts']['max_time'],
			'max_memory'	=>$GLOBALS['duplicator_opts']['max_memory'],
			'dir_bypass'	=>"{$GLOBALS['duplicator_opts']['dir_bypass']}",
			'log_level'		=>'0',
			'dbiconv'		=>"{$GLOBALS['duplicator_opts']['dbiconv']}",
			'skip_ext'		=>"{$GLOBALS['duplicator_opts']['skip_ext']}",
			'rm_snapshot'	=>"{$GLOBALS['duplicator_opts']['rm_snapshot']}");
				
		update_option('duplicator_version_plugin', 	DUPLICATOR_VERSION);
		update_option('duplicator_options', serialize($duplicator_opts));

		//CLEANUP LEGACY
		//PRE 0.2.9
		delete_option('duplicator_version_database');
		$wpdb->query("ALTER TABLE `{$table_name}` CHANGE bid id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT");
		$wpdb->query("ALTER TABLE `{$table_name}` DROP COLUMN ver_db");
		$wpdb->query("ALTER TABLE `{$table_name}` DROP COLUMN ver_plug");
		$wpdb->query("ALTER TABLE `{$table_name}` DROP COLUMN status");
		$wpdb->query("ALTER TABLE `{$table_name}` DROP COLUMN type");	
		
		//Setup All Directories
		duplicator_init_snapshotpath();
	}
	
	/* UPDATE 
	register_activation_hook is not called when a plugin is updated
	so we need to use the following function */
	function duplicator_update() {
		if (DUPLICATOR_VERSION != get_option("duplicator_version_plugin")) {
			duplicator_activate();
		}
	}


	/* DEACTIVATION 
	Only called when plugin is deactivated */
	function duplicator_deactivate() {
		//No actions needed yet
	}
	
	
	/* UNINSTALL 
	Uninstall all duplicator logic */
	function duplicator_uninstall() {
		global $wpdb;
		$table_name = $wpdb->prefix . "duplicator";
		$wpdb->query("DROP TABLE `{$table_name}`");
	
		delete_option('duplicator_version_plugin');
		delete_option('duplicator_options');
		
		if ($GLOBALS['duplicator_opts']['rm_snapshot']) {
		
			$ssdir = duplicator_safe_path(DUPLICATOR_SSDIR_PATH);
		
			//Sanity check for strange setup
			$check = glob("{$ssdir}/wp-config.php");
			if (count($check) == 0) {

				//PHP is sometimes flaky so lets do a sanity check
				foreach (glob("{$ssdir}/*_database.sql")  as $file) { if (strstr($file, '_database.sql'))  {@unlink("{$file}");} }
				foreach (glob("{$ssdir}/*_installer.php") as $file) { if (strstr($file, '_installer.php')) {@unlink("{$file}");} }
				foreach (glob("{$ssdir}/*_package.zip")   as $file) { if (strstr($file, '_package.zip'))   {@unlink("{$file}");} }
				foreach (glob("{$ssdir}/*.log") as $file) 			{ if (strstr($file, '.log'))   		   {@unlink("{$file}");} }
				
				//Check for core files and only continue removing data if the snapshots directory
				//has not been edited by 3rd party sources, this helps to keep the system stable
				$files = glob("{$ssdir}/*");
				if(is_array($files) && count($files) == 3)
				{
					$defaults = array("{$ssdir}/index.php", "{$ssdir}/robots.txt", "{$ssdir}/dtoken.php");
					$compare = array_diff($defaults, $files);
					
					if (count($compare) == 0) {
						foreach($defaults as $file) {@unlink("{$file}");}
						@unlink("{$ssdir}/.htaccess");
						@rmdir($ssdir);
					}
				//No packages have ever been created
				} else if(is_array($files) && count($files) == 1) {
					$defaults = array("{$ssdir}/index.php");
					$compare  = array_diff($defaults, $files);
					if (count($compare) == 0) {
						@unlink("{$ssdir}/index.php");
						@rmdir($ssdir);
					}
				} 
			
			}
		}
	}
	
	
	/* META LINK ADDONS
	Adds links to the plugins manager page */
	function duplicator_meta_links( $links, $file ) {
		$plugin = plugin_basename(__FILE__);
		// create link
		if ( $file == $plugin ) {
			$links[] = '<a href="' . DUPLICATOR_HELPLINK . '" title="' . __( 'FAQ', 'wpduplicator' ) . '" target="_blank">' . __( 'FAQ', 'wpduplicator' ) . '</a>';
			$links[] = '<a href="' . DUPLICATOR_GIVELINK . '" title="' . __( 'Partner', 'wpduplicator' )  . '" target="_blank">' . __( 'Partner', 'wpduplicator' )  . '</a>';
			$links[] = '<a href="' . DUPLICATOR_CERTIFIED .'" title="' . __( 'Approved Hosts', 'wpduplicator'  ) . '"  target="_blank">' . __( 'Approved Hosts', 'wpduplicator'  ) . '</a>';
			return $links;
		}
		return $links;
	}
	
	//HOOKS & ACTIONS
	load_plugin_textdomain('wpduplicator' , FALSE, basename( dirname( __FILE__ ) ) . '/lang/' );
	register_activation_hook(__FILE__ ,	    'duplicator_activate');
	register_deactivation_hook(__FILE__ ,	'duplicator_deactivate');
	register_uninstall_hook(__FILE__ , 		'duplicator_uninstall');
	
	add_action('plugins_loaded', 						'duplicator_update');
	add_action('admin_init', 							'duplicator_init' );
	add_action('admin_menu', 							'duplicator_menu');
	add_action('wp_ajax_duplicator_system_check',		'duplicator_system_check');
	add_action('wp_ajax_duplicator_system_directory',	'duplicator_system_directory');
	add_action('wp_ajax_duplicator_delete',				'duplicator_delete');
	add_action('wp_ajax_duplicator_create',				'duplicator_create');
	add_action('wp_ajax_duplicator_settings',			'duplicator_settings');
	add_filter('plugin_action_links', 					'duplicator_manage_link', 10, 2 );
	add_filter('plugin_row_meta', 						'duplicator_meta_links', 10, 2 );


	/**
	 *  DUPLICATOR_INIT
	 *  Init routines  */
	function duplicator_init() {
	   /* Register our stylesheet. */
	   wp_register_style('jquery-ui', 		  DUPLICATOR_PLUGIN_URL . 'css/jquery-ui.css', null , "1.8.21" );
	   wp_register_style('duplicator_style',  DUPLICATOR_PLUGIN_URL . 'css/style.css' );
	}

	/**
	 *  DUPLICATOR_VIEWS
	 *  Inlcude all visual elements  */
	function duplicator_main_page() {include 'inc/page.main.php';}
	//Diagnostics Page
	function duplicator_diag_page() {include 'inc/page.diag.php';}
	//Support Page
	function duplicator_support_page() {include 'inc/page.support.php';}
	//All About Page
	function duplicator_about_page() {include 'inc/page.about.php';}
	
	/**
	 *  DUPLICATOR_MENU
	 *  Loads the menu item into the WP tools section and queues the actions for only this plugin */
	function duplicator_menu() {	
		//Main Menu
		$page_main = add_menu_page('Duplicator', 'Duplicator', "import", basename(__FILE__), 'duplicator_main_page', plugins_url('duplicator/img/create.png'));
		add_submenu_page(basename(__FILE__), __('Dashboard', 'wpduplicator'),  __('Dashboard', 'wpduplicator'), "import" , basename(__FILE__), 'duplicator_main_page');
		//Sub Menus
		$page_diag  	= add_submenu_page(basename(__FILE__), __('Diagnostics', 'wpduplicator'), __('Diagnostics', 'wpduplicator'), 'import', 'duplicator_diag_page', 'duplicator_diag_page');
		$page_support 	= add_submenu_page(basename(__FILE__), __('Support', 'wpduplicator'), __('Support', 'wpduplicator'), 'import', 'duplicator_support_page', 'duplicator_support_page');
		$page_about 	= add_submenu_page(basename(__FILE__), __('All About', 'wpduplicator'), __('All About', 'wpduplicator'), 'import', 'duplicator_about_page', 'duplicator_about_page');
		

		//Apply scripts and styles
		add_action('admin_print_scripts-' . $page_main, 'duplicator_scripts');
		add_action('admin_print_styles-'  . $page_main, 'duplicator_styles' );
		add_action('admin_print_styles-'  . $page_diag, 'duplicator_styles' );
		add_action('admin_print_styles-'  . $page_about, 'duplicator_styles' );
		add_action('admin_print_styles-'  . $page_support, 'duplicator_styles' );
	}

	/**
	 *  DUPLICATOR_SCRIPTS
	 *  Loads the required javascript libs only for this plugin  */
	function duplicator_scripts() {
		wp_enqueue_script("jquery-ui", DUPLICATOR_PLUGIN_URL . "js/jquery-ui.min.js", array( 'jquery' ), "1.8.21");
	}

	/**
	 *  DUPLICATOR_STYLES
	 *  Loads the required css links only for this plugin  */
	function duplicator_styles() {
	   wp_enqueue_style('jquery-ui');
	   wp_enqueue_style('duplicator_style');
	}
	
	/**
	 *  DUPLICATOR_MANAGE_LINK
	 *  Adds the manage link in the plugins list */
	function duplicator_manage_link($links, $file) {
		static $this_plugin;
		if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
		 
		if ($file == $this_plugin){
			$settings_link = '<a href="admin.php?page=duplicator.php">'. __("Manage", 'wpduplicator') .'</a>';
		    array_unshift($links, $settings_link);
		}
		return $links;
	}
	
	//Use WordPress Debugging log file. file is written to wp-content/debug.log
	//trace with tail command to see real-time issues.
	if(!function_exists('duplicator_debug')){
		function duplicator_debug( $message ) {
			if( WP_DEBUG === true ){
			  if( is_array( $message ) || is_object( $message ) ){
				error_log( print_r( $message, true ) );
			  } else {
				error_log( $message );
			  }
			}
		}
	}

}
?>