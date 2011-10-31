<?php
/*
Plugin Name: Include custom field
Plugin URI: http://pp19dd.com/wordpress-plugin-include-custom-field/
Description: Shortcode that lets you <strong>[include custom]</strong> fields inside a post. To use: create a custom field (ex: "my table"), put HTML in the value, and reference it in a post as <strong>[include "my table"]</strong>.  You can borrow from another post with <strong>[include global="my table"]</strong>. Caveat/bonus: this is unfiltered HTML, shortcodes can be recursive, so, be careful.
Version: 1.1
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

function include_custom_options_init() {
	register_setting(
		'icf_options_grp',
		'icf_options'
	);
}

function include_custom_options_add_page() {
	add_options_page(
		'Include Custom Fields',
		'Include Custom Fields',
		'manage_options',
		'icf_options',
		'include_custom_options_page'
	);
}

function include_custom_options_page() {

	$options = get_option('icf_options');

?>
<div class="wrap">
<h2>Include Custom Fields Options</h2>

<p>This plugin creates a shortcode that will let you [include] custom fields inside posts, pages and text widgets.  Usage:</p>

<ol>
	<li>Create a custom field, call it "My Table".</li>
	<li>Place HTML inside the custom field value (ex: complex embed code.)</li>
	<li>Inside the post, simply write [include="My Table"].</li>
	<li>If including from another post, you can [include global="My Table"].</li>
</ol>

<p>See more usage examples and command-line utilities on <a href="http://pp19dd.com/wordpress-plugin-include-custom-field/">the project homepage</a>.</p>

<form method="post" action="options.php">
<?php settings_fields('icf_options_grp'); ?>

<div style="margin-top:3em">
<fieldset>
	<label for="icf_enable_widget_shortcodes">
		<input id="icf_enable_widget_shortcodes" name="icf_options[widget_shortcode]" type="checkbox" value="1" <?php checked('1', $options['widget_shortcode']); ?> />
		
		Enable shortcodes in plain <strong>Text widgets</strong> <em>(Uses add_filter('widget_text', 'do_shortcode'))</em>
	</label>
</fieldset>
</div>
<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</form>
</div>
<?php

}

function shortcode_include_custom_field( $atts, $content=null, $code="" ) {
	global $post;

	$html = '';
	
	foreach( $atts as $k => $v ) {
	
		$post_id = null;
		
		// normally keys are 0, 1, 2.. unless a special key is specified
		// with something like global="test"
		switch( strtolower(trim($k)) ) {
			case 'global':
				$query = new WP_Query(array(
					'post_type' => 'any',
					'post_status' => 'any',
					'posts_per_page' => 1,
					'meta_key' => $v
				));
				
				if( !empty( $query->posts ) ) $post_id = $query->posts[0]->ID;
			break;
			
			case 'file':
				// todo: add jailed file support for expert users
			break;
			
			default:
				// assume we're reading from a post meta field
				$post_id = $post->ID;
			break;
		}
		
		// post id not found - skip this entry
		if( is_null( $post_id ) ) continue;
		
		// get meta key ($v) from post
		$field = get_post_custom_values( $v, $post_id );
		
		// field not found, skip this entry
		if( is_null( $field ) ) continue;
		
		// return value is always an array, so implode
		$html .= @implode("", $field);
	}
	return( $html );
}

// options screen
add_action('admin_init', 'include_custom_options_init' );
add_action('admin_menu', 'include_custom_options_add_page' );

// meat and potatoes
add_shortcode( 'include', 'shortcode_include_custom_field' );

// optional widget processing for text widgets
$icf_options = get_option('icf_options');

// whiny error checking routines
if(
	is_array( $icf_options ) &&
	isset($icf_options['widget_shortcode'] ) && 
	$icf_options['widget_shortcode'] == '1' 
) {
	add_filter('widget_text', 'do_shortcode');
}

?>