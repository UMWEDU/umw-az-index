<?php
/*
Plugin Name: UMW A-Z Index
Plugin URI: http://www.umw.edu/
Description: Implements an A-Z Index shortcode and widget
Author: UMW
Version: 0.1
Author: Curtiss Grymala
Requires at least: 4.2
Tested up to: 4.5
*/

if( !class_exists( 'UMWAZIndexWidget' ) ) {
        require_once( dirname( __FILE__ ) . '/classes/class-umw-azindex-widget.php' );
}
add_action( 'widgets_init', create_function( '', 'return register_widget( "UMWAZIndexWidget" );' ) );

// A to Z index shortcode

add_shortcode( 'azindex', 'umw_azindex' );
add_shortcode( 'umw_azindex', 'umw_azindex' );
add_shortcode( 'aznav', 'umw_aznav' );
add_shortcode( 'umw_aznav', 'umw_aznav' );

/**
 *Display A to Z Index
 *
 *@return string
 *@since 1.0
 */
function umw_azindex ( $args = array() ) {
	global $wpdb, $blog_id;
	
	if( isset( $GLOBALS['post'] ) && is_object( $GLOBALS['post'] ) )
		$current_post_id = $GLOBALS['post']->ID;
	else
		$current_post_id = 0;

	$post_type = $args['post_type'];
	if( ! @strlen( $post_type ) ) {
		$post_type = 'post,page';
	}
	$show_excerpt = $args['excerpt'];
	if( ! @strlen( $show_excerpt ) ) {
		$show_excerpt = 'true';
	}
	$frags = explode( ',', $post_type );
	$tmp = array();
	foreach( $frags as $frag ) {
		$tmp[] = "'" . $wpdb->escape( $frag ) . "'";
	}
	$post_type = implode( ',', $tmp );
	unset( $frags, $tmp );

	// only allow letters to be sent via get string

	$letter = clean_letter( $_GET['letter'] );
	$sql = $wpdb->prepare( "SELECT ID, post_title, post_type, post_excerpt, post_content, post_date FROM {$wpdb->posts} WHERE post_status=%s AND post_type IN (" . $post_type . ") AND post_title LIKE %s AND ID != %d ORDER BY post_title", 'publish', $letter . '%', $current_post_id );
			/*$sql = "SELECT p.ID AS post_id, p.post_title, p.post_type, p.post_excerpt, p.post_content, p.post_date FROM " . $wpdb->posts . " AS p
					WHERE p.post_status='publish' AND p.post_type IN(" . $post_type . ")
		AND p.post_title LIKE '${letter}%'
					ORDER BY p.post_title";*/
	$post_results = $wpdb->get_results( $sql, ARRAY_A );
	
	ob_start();
?>
<?php if ( count( $post_results ) ): ?>
	<ul class="az-index-entry-list">
<?php foreach ( $post_results as $post_result ) : ?>
		<li>
<?php
$title = get_the_title( $post_result['ID'] );
$link = get_permalink( $post_result['ID'] );
$excerpt = empty( $post_result['post_excerpt'] ) ? umw_truncate_excerpt( $post_result['post_content'], 100 ) : $post_result['post_excerpt'];
$excerpt = apply_filters( 'the_excerpt', $excerpt, $post_result['ID'] );
?>

			<h2><a href="<?php echo $link; ?>"><?php echo $title; ?></a></h2>
			<p><span class="date"><?php echo mysql2date( get_option( 'date_format' ), $post_result['post_date'] ); ?></span></p>
<?php if ( @strlen( $excerpt ) && $show_excerpt == 'true' ): ?>
			<div class="post-excerpt"><?php echo $excerpt; ?></div>
<?php endif; ?>
		</li>

<?php endforeach; ?>
	</ul>
<?php else: ?>
	<p>No posts or pages found.</p>
<?php endif; ?>
<?php
	return ob_get_clean();
}

function umw_truncate_excerpt( $content, $len ) {
	$content = strip_tags( $content );
	if( str_word_count( $content ) <= $len )
		return $content;
	
	$content = explode( ' ', $content );
	$content = array_slice( $content, 0, ($len-1) );
	return implode( ' ', $content );
}

function clean_letter ( $letter ) {
	global $wpdb;
	$letter = @substr( preg_replace( "/[^a-z]/i", "", $letter ), 0, 1 );
	if ( ! @strlen( $letter ) ) { $letter = 'A'; }
	$letter = $wpdb->escape( $letter );
	return $letter;
}

function umw_aznav ( $instance ) {
	$letter = clean_letter( $_GET['letter'] );
	ob_start();
?>
                <ul class="umw-az-index">
<?php foreach ( range( 'A', 'Z') as $i ): ?>
                        <li<?php if ( $letter == $i ): ?> class="selected-letter"<? endif; ?>><a href="<?php echo $instance['shortcode_page']; ?>?letter=<?php echo $i; ?>"><?php echo $i; ?></a></li>
<?php endforeach; ?>
                </ul>
<?php
	return ob_get_clean();
}

