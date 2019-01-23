<?php
/**
 * WordPress eXtended RSS file parser implementations
 *
 * @package Thinkery
 * @subpackage Importer
 */

/**
 * This parses the Thinkery XML file.
 */
class Thinkery_XML_Parser {
	function parse( $file ) {
		$things = array();

		$internal_errors = libxml_use_internal_errors(true);

		$dom = new DOMDocument;
		$old_value = null;
		if ( function_exists( 'libxml_disable_entity_loader' ) ) {
			$old_value = libxml_disable_entity_loader( true );
		}
		$success = $dom->loadXML( file_get_contents( $file ) );
		if ( ! is_null( $old_value ) ) {
			libxml_disable_entity_loader( $old_value );
		}

		if ( ! $success || isset( $dom->doctype ) ) {
			return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this WXR file', 'wordpress-importer' ), libxml_get_errors() );
		}

		$xml = simplexml_import_dom( $dom );
		unset( $dom );

		// halt if loading produces an error
		if ( ! $xml )
			return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this WXR file', 'wordpress-importer' ), libxml_get_errors() );

		foreach ( $xml->xpath( '//thinkery/thing' ) as $thing ) {
			$post = array(
				'post_title' => (string) $thing->title,
				'url' => (string) $thing->url,
				'post_type' => Thinkery_Things::CPT,
				'post_content' => (string) $thing->html,
				'post_date' => date( 'Y-m-d H:i:s', strtotime( (string) $thing->date ) ),
			);
			$posts[] = $post;
		}

		return array(
			'posts' => $posts,
		);
	}
}
