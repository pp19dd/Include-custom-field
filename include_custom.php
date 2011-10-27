<?php
/*
Plugin Name: Include custom field
Plugin URI: http://pp19dd.com/include-custom-field/
Description: Shortcode that lets you <strong>[include custom]</strong> fields inside a post. To use: create a custom field (ex: "my table"), put HTML in the value, and reference it in a post as <strong>[include "my table"]</strong>.  You can borrow from another post with <strong>[include global="my table"]</strong>. Caveat/bonus: this is unfiltered HTML, shortcodes can be recursive, so, be careful.
Version: 1.0
Author: Dino Beslagic
Author URI: http://pp19dd.com
License: No license, use at own risk.
*/

/*
	This is a shortcode that lets you include RAW html inside posts from its custom fields.
	To use, create a custom field in the post (ex: "my table") and put this in a WP post:
		[include "my table"].
		
	If needed, you can perform global includes from other posts this way:
		[include global="my table"]
	
	Warning/extra feature: shortcodes can be recursive. If you have these custom fields:
		Name	Value
		"One"	First sentence.
		"Two"	Second sentence.
		"Three"	Test [include one two] ing.
	
	Putting [include three] in a WP post will produce:
		"Test First sentence. Second sentence. ing."		
*/

function shortcode_include_custom_field( $atts, $content=null, $code="" ) {
	global $post;

	$html = '';
	
	foreach( $atts as $k => $v ) {
	
		$post_id = null;
		
		// inefficient, but only native way to get a global meta by key.
		if( strtolower($k) == 'global' ) {
			$query = new WP_Query(array(
				'post_type' => 'any',
				'post_status' => 'any',
				'posts_per_page' => 1,
				'meta_key' => $v
			));
			
			if( !empty( $query->posts ) ) $post_id = $query->posts[0]->ID;
		} else {
			$post_id = $post->ID;
		}
		
		if( is_null( $post_id ) ) continue;
		$field = get_post_custom_values( $v, $post_id );
		if( is_null( $field ) ) continue;
		
		// return is always an array
		$html .= @implode("", $field);
	}
	return( $html );
}

add_shortcode( 'include', 'shortcode_include_custom_field' );

?>