<?php

class WSUWP_Markdown_Parser {
	/**
	 * @since 0.0.1
	 *
	 * @var WSUWP_Markdown_Parser
	 */
	private static $instance;

	/**
	 * Contains the Markdown parser object.
	 *
	 * @since 0.0.1
	 *
	 * @var WPCom_GHF_Markdown_Parser
	 */
	private static $parser;

	/**
	 * Maintain and return the one instance. Initiate hooks when
	 * called the first time.
	 *
	 * @since 0.0.1
	 *
	 * @return \WSUWP_Markdown_Parser
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_Markdown_Parser();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks to include.
	 *
	 * @since 0.0.1
	 */
	public function setup_hooks() {}

	/**
	 * Gets the Markdown parser object and instantiates if needed.
	 *
	 * Forked from Automattic's Jetpack Markdown module.
	 *
	 * @since 0.0.1
	 *
	 * @return WPCom_GHF_Markdown_Parser
	 */
	public function get_parser() {

		if ( ! self::$parser ) {
			// Replace the Jetpack require statement with a more generic one.
			require_once( dirname( __DIR__ ) . '/lib/markdown/extra.php' );
			require_once( dirname( __DIR__ ) . 'lib/markdown/gfm.php' );

			self::$parser = new WPCom_GHF_Markdown_Parser;
		}

		return self::$parser;
	}

	/**
	 * Transforms Markdown content into HTML.
	 *
	 * Forked from Automattic's Jetpack Markdown module.
	 *
	 * @since 0.0.1
	 *
	 * @param  string $text  Content to be run through Markdown
	 * @param  array  $args  Arguments, with keys:
	 *                       id: provide a string to prefix footnotes with a unique identifier
	 *                       unslash: when true, expects and returns slashed data
	 *                       decode_code_blocks: when true, assume that text in fenced code blocks is already
	 *                         HTML encoded and should be decoded before being passed to Markdown, which does
	 *                         its own encoding.
	 * @return string        Markdown-processed content
	 */
	public function transform( $text, $args = array() ) {
		$args = wp_parse_args( $args, array(
			'id' => false,
			'unslash' => true,
			'decode_code_blocks' => ! $this->get_parser()->use_code_shortcode,
		) );
		// probably need to unslash
		if ( $args['unslash'] ) {
			$text = wp_unslash( $text );
		}

		/**
		 * Filter the content to be run through Markdown, before it's transformed by Markdown.
		 *
		 * @module markdown
		 *
		 * @since 2.8.0
		 *
		 * @param string $text Content to be run through Markdown
		 * @param array $args Array of Markdown options.
		 */
		$text = apply_filters( 'wpcom_markdown_transform_pre', $text, $args );
		// ensure our paragraphs are separated
		$text = str_replace( array( '</p><p>', "</p>\n<p>" ), "</p>\n\n<p>", $text );
		// visual editor likes to add <p>s. Buh-bye.
		$text = $this->get_parser()->unp( $text );
		// sometimes we get an encoded > at start of line, breaking blockquotes
		$text = preg_replace( '/^&gt;/m', '>', $text );
		// prefixes are because we need to namespace footnotes by post_id
		$this->get_parser()->fn_id_prefix = $args['id'] ? $args['id'] . '-' : '';
		// If we're not using the code shortcode, prevent over-encoding.
		if ( $args['decode_code_blocks'] ) {
			$text = $this->get_parser()->codeblock_restore( $text );
		}
		// Transform it!
		$text = $this->get_parser()->transform( $text );
		// Fix footnotes - kses doesn't like the : IDs it supplies
		$text = preg_replace( '/((id|href)="#?fn(ref)?):/', '$1-', $text );
		// Markdown inserts extra spaces to make itself work. Buh-bye.
		$text = rtrim( $text );
		/**
		 * Filter the content to be run through Markdown, after it was transformed by Markdown.
		 *
		 * @module markdown
		 *
		 * @since 2.8.0
		 *
		 * @param string $text Content to be run through Markdown
		 * @param array $args Array of Markdown options.
		 */
		$text = apply_filters( 'wpcom_markdown_transform_post', $text, $args );

		// probably need to re-slash
		if ( $args['unslash'] ) {
			$text = wp_slash( $text );
		}

		return $text;
	}
}
