<?php

/**
 * Implements the A-Z Index widget
 * @package WordPress
 * @subpackage UMW A-Z Index
 * @version 0.2
 */

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You do not have permission to access this file directly.' );
	}
}

namespace UMW_AZ_Index {

	class widget extends \WP_Widget {

		var $icons = array();

		/**
		 * Constructor
		 *
		 * @return void
		 * @since 1.0
		 */
		function __construct() {
			parent::__construct( 'umw_az_index_widget', $name = __( 'UMW A to Z Index', 'umw-az-index' ) );
			/*$this->icons = $umw_widgets_icons;*/
		}

		/**
		 * Widget function
		 *
		 * @return void
		 * @since 1.0
		 */
		function widget( $args, $instance ) {
			extract( $args );
			$title = apply_filters( 'widget_title', $instance['title'] );
			echo $before_widget;
			if ( $title ) {
				echo $before_title . $title . $after_title;
			}
			echo $this->content( $instance );
			echo $after_widget;
		}

		/**
		 * Content function
		 *
		 * @return string
		 * @since 1.0
		 */
		function content( $instance ) {
			$obj = shortcode::instance();

			return $obj->do_nav_shortcode( $instance );
		}

		/**
		 * Update function
		 *
		 * @return array
		 * @since 1.0
		 */
		function update( $new_instance, $old_instance ) {
			$instance                   = $old_instance;
			$instance['title']          = strip_tags( $new_instance['title'] );
			$instance['shortcode_page'] = strip_tags( $new_instance['shortcode_page'] );

			return $instance;
		}

		/**
		 * Form function
		 *
		 * @return void
		 * @since 1.0
		 */
		function form( $instance ) {
			$title          = esc_attr( $instance['title'] );
			$shortcode_page = esc_attr( $instance['shortcode_page'] );
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'umw-az-index' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
					   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
					   value="<?php echo $title; ?>"/>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'shortcode_page' ); ?>"><?php _e( 'URL of Page With Shortcode:', 'umw-az-index' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'shortcode_page' ); ?>"
					   name="<?php echo $this->get_field_name( 'shortcode_page' ); ?>" type="text"
					   value="<?php echo $shortcode_page; ?>"/><br/>
			<p>
				<a class="thickbox"
				   href="/wp-content/plugins/umw-widgets/azindex-help.php?TB_iframe=1&amp;width=400&amp;height=300"><?php _e( 'A to Z
				Index Shortcode Help', 'umw-az-index' ) ?></a>
			</p>
			</p>
			<?php
		}

	} // class \UMW_AZ_Index\widget

}
