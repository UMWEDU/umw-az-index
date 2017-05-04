<?php
/**
 * Implements the UMW A-Z Index shortcode (and instantiates the widget)
 *
 * @version 0.2
 * @package WordPress
 * @subpackage UMW A-Z Index
 */

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace UMW_AZ_Index {

	class shortcode {
		/**
		 * @var \UMW_AZ_Index\shortcode $instance holds the single instance of this class
		 * @access private
		 */
		private static $instance;

		/**
		 * @var string $version holds the version number for the plugin
		 * @access public
		 */
		public $version = '0.2';

		/**
		 * Creates the \UMW_AZ_Index\shortcode object
		 *
		 * @access private
		 * @since  2.0
		 */
		private function __construct() {
			add_action( 'plugins_loaded', array( $this, 'startup' ) );
		}

		/**
		 * Returns the instance of this class.
		 *
		 * @access  public
		 * @since   2.0
		 * @return  \UMW_AZ_Index\shortcode
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

		/**
		 * Performs the initial setup procedures for this class
		 *
		 * @access public
		 * @since  0.2
		 * @return void
		 */
		public function startup() {
			add_shortcode( 'azindex', array( $this, 'do_shortcode' ) );
			add_shortcode( 'umw_azindex', array( $this, 'do_shortcode' ) );
			add_shortcode( 'aznav', array( $this, 'do_nav_shortcode' ) );
			add_shortcode( 'umw_aznav', array( $this, 'do_nav_shortcode' ) );

			add_action( 'widgets_init', array( $this, 'widgets_init' ) );
		}

		/**
		 * Instantiate and register the widget(s) for this plugin
		 *
		 * @access public
		 * @since  0.1
		 * @return void
		 */
		function widgets_init() {
			if ( ! class_exists( 'widget' ) ) {
				require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/classes/class-umw-azindex-widget.php' );
			}

			register_widget( '\UMW_AZ_Index\widget' );

			return;
		}

		/**
		 * Processes the azindex shortcode
		 *
		 * @param array $args the list of shortcode attributes
		 *
		 * @access public
		 * @since  0.1
		 * @return string
		 */
		public function do_shortcode( $args = array() ) {
			global $wpdb, $blog_id;

			if ( isset( $GLOBALS['post'] ) && is_object( $GLOBALS['post'] ) ) {
				$current_post_id = $GLOBALS['post']->ID;
			} else {
				$current_post_id = 0;
			}

			$post_type = $args['post_type'];
			if ( ! @strlen( $post_type ) ) {
				$post_type = 'post,page';
			}
			$show_excerpt = $args['excerpt'];
			if ( ! @strlen( $show_excerpt ) ) {
				$show_excerpt = 'true';
			}
			$frags = explode( ',', $post_type );
			$tmp   = array();
			foreach ( $frags as $frag ) {
				$tmp[] = "'" . $wpdb->escape( $frag ) . "'";
			}
			$post_type = implode( ',', $tmp );
			unset( $frags, $tmp );

			// only allow letters to be sent via get string

			$letter = $this->clean_letter( $_GET['letter'] );
			$sql    = $wpdb->prepare( "SELECT ID, post_title, post_type, post_excerpt, post_content, post_date FROM {$wpdb->posts} WHERE post_status=%s AND post_type IN (" . $post_type . ") AND post_title LIKE %s AND ID != %d ORDER BY post_title", 'publish', $letter . '%', $current_post_id );
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
							$title   = get_the_title( $post_result['ID'] );
							$link    = get_permalink( $post_result['ID'] );
							$excerpt = empty( $post_result['post_excerpt'] ) ? $this->truncate_excerpt( $post_result['post_content'], 100 ) : $post_result['post_excerpt'];
							$excerpt = apply_filters( 'the_excerpt', $excerpt, $post_result['ID'] );
							?>

							<h2><a href="<?php echo $link; ?>"><?php echo $title; ?></a></h2>
							<p>
								<span
										class="date"><?php echo mysql2date( get_option( 'date_format' ), $post_result['post_date'] ); ?></span>
							</p>
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

		/**
		 * Truncate an excerpt to a specific number of words
		 *
		 * @param string $content the content being truncated
		 * @param int $len the maximum number of words allowed in the content
		 *
		 * @access public
		 * @since  0.1
		 * @return string the updated excerpt
		 */
		public function truncate_excerpt( $content, $len ) {
			$content = strip_tags( $content );
			if ( str_word_count( $content ) <= $len ) {
				return $content;
			}

			$content = explode( ' ', $content );
			$content = array_slice( $content, 0, ( $len - 1 ) );

			return implode( ' ', $content );
		}

		/**
		 * Ensure that a character really is an alphabetical character
		 *
		 * @param string $letter the character being checked
		 *
		 * @access public
		 * @since  0.1
		 * @return string the single alphabetical character
		 */
		public function clean_letter( $letter ) {
			global $wpdb;
			$letter = @substr( preg_replace( "/[^a-z]/i", "", $letter ), 0, 1 );
			if ( ! @strlen( $letter ) ) {
				$letter = 'A';
			}
			$letter = $wpdb->escape( $letter );

			return $letter;
		}

		/**
		 * Process the aznav shortcode
		 *
		 * @param array $args the list of shortcode attributes
		 *
		 * @access public
		 * @since  0.1
		 * @return string the processed code
		 */
		public function do_nav_shortcode( $args = array() ) {
			$letter = $this->clean_letter( $_GET['letter'] );
			ob_start();
			?>
			<ul class="umw-az-index">
				<?php foreach ( range( 'A', 'Z' ) as $i ): ?>
					<li<?php if ( $letter == $i ): ?> class="selected-letter"<? endif; ?>><a
								href="<?php echo $args['shortcode_page']; ?>?letter=<?php echo $i; ?>"><?php echo $i; ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php
			return ob_get_clean();
		}
	}
}