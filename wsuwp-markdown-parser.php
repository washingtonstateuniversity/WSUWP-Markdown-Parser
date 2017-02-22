<?php
/*
Plugin Name: WSUWP Markdown Parser
Version: 0.0.1
Description: Provides markdown parsing when requested by another plugin or theme.
Author: washingtonstateuniversity, jeremyfelt, automattic
Author URI: https://web.wsu.edu/
Plugin URI: https://github.com/washingtonstateuniversity/WSUWP-Markdown-Parser
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// The core plugin class.
require dirname( __FILE__ ) . '/includes/class-wsuwp-markdown-parser.php';

add_action( 'after_setup_theme', 'WSUWP_Markdown_Parser' );
/**
 * Start things up.
 *
 * @return \WSUWP_Markdown_Parser
 */
function WSUWP_Markdown_Parser() {
	return WSUWP_Markdown_Parser::get_instance();
}

