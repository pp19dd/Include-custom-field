#!/usr/bin/php
<?php
// ========================================================================
// command line utility to pipe STDIN into a WordPress custom field
// See http://pp19dd.com/wordpress-plugin-include-custom-field/
// ========================================================================
require( "wp-config.php" );

if( $argc == 1 ) {
echo "\nThis program redirects STDIN into a WordPress custom field.\n";
echo "If a post ID is not specified, program will attempt to find\n";
echo "a matching post with a unique key (make sure key is unique)\n\n";
echo "\nUsage: ls | ./wordpress-meta.php [post_ID] \"[post meta name]\"\n\n";
echo "\tWhere post_ID is a number, and meta name is a key\n";
echo "\tIf meta name/key is a complex string, put it in quotes\n";
echo "\t\tex: cat /ads/current.txt | ./wordpress-meta.php \"global ads\"\n";
echo "\t\tex: uptime | ./wordpress-meta.php 1433 uptime\n\n";
die;
}

if( $argc > 3 )
	die( "Error: too many args. Try putting custom field name in quotes." );

if( $argc == 2 && is_numeric($argv[1]) )
	die( "Error: custom field name missing, only got a number.\n" );

// assume no error after this point :/
if( $argc == 2 ) {

	// discover the post ID
	$meta_key = $argv[1];
	$query = new WP_Query(array(
		'post_type' => 'any',
		'post_status' => 'any',
		'posts_per_page' => 1,
		'meta_key' => $meta_key
	));
	if( empty( $query->posts ) ) {
		die( "Post not found with that meta key\n" );
	}
	$post_id = $query->posts[0]->ID;
	
} elseif( $argc == 3 ) {

	// one is post id, other is the key (limitation, key can't be numeric.)
	if( is_numeric($argv[1]) ) {
		$post_id = $argv[1];
		$meta_key = $argv[2];
	} else {
		$post_id = $argv[2];
		$meta_key = $argv[1];
	}
}

$text = '';
$fp = fopen( 'php://stdin', 'r');
while( !feof( $fp ) ) $text .= fgets( $fp, 4096 );
fclose( $fp );

update_post_meta( $post_id, $meta_key, $text );

// status - post id, meta key, string length
printf(
	"Updated, post id = %s, meta_key='%s', length=%s\n",
	$post_id, $meta_key, strlen( $text )
); 

?>