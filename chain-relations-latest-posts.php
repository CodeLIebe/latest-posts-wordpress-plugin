<?php
/*
Plugin Name: chain relations - latest Blog Posts
Plugin URI: 
Description: Zeigt eine Ãœbersicht von Posts in einer Widget-Area an.
Author: Elisabeth Fughe
Author URI: http://www.website-shop.com
Version: 0.1
*/
//include custom css
add_action('wp_enqueue_scripts','register_my_scripts');

function register_my_scripts(){
wp_enqueue_style( 'style1', plugins_url( 'css/style1.css' , __FILE__ ) );
//wp_enqueue_style( 'style2', plugins_url( 'css/style2.css' , __FILE__ ) );
}

// Register latest posts widget
function register_chain_relations_Latest_Posts_Widget() {
    register_widget( 'chain_relations_Latest_Posts_Widget' );
}
add_action( 'widgets_init', 'register_chain_relations_Latest_Posts_Widget' );


class chain_relations_Latest_Posts_Widget extends WP_Widget {

 /**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @var array
	 */
	protected $defaults;

/**
 * Register widget with WordPress.
 */
	function __construct() {

		$this->defaults = array(
			'title'          => '',
			'sdn_title'  	 => '',
			'category'       => '1',
			'size'           => '3',
		);

		$widget_ops = array(
			'classname'   => 'chain-relations-latest-posts',
			'description' => __( 'Zeigt eine Übersicht der letzten Posts an.', 'evolution' ),
		);

		$control_ops = array(
			'id_base' => 'chain-relations-latest-posts',
			'width'   => 200,
			'height'  => 250,
		);

		$this->WP_Widget( 'chain-relations-latest-posts', __( '[chain relations] Latest Posts', 'evolution' ), $widget_ops, $control_ops );

	}

/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget( $args, $instance ) {

		extract( $args );

		/** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $before_widget;

			if ( ! empty( $instance['title'] ) )
				echo '<h2 class="blue">' . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . '</h2>';

			if ( ! empty( $instance['sdn_title'] ) )
                echo '<p>' . $instance['sdn_title'] . '</p>';
            
         //args to display blog posts from category
            
            if ( $instance['category'] == 1 ){
                $selected_cat = 1;
            }
            else {
                $selected_cat = $instance['category'];
            }
            
            $args = array(
                'numberposts' => $instance['size'],
                'offset' => 0,
                'category' => $selected_cat,
                'orderby' => 'post_date',
                'order' => 'DESC',
            );

            $latest_posts = wp_get_recent_posts( $args );
            echo '<div style="margin:0.5rem">'; //-- outter wrapper
            foreach( $latest_posts as $post ){
                echo '<div class="blog-posts">' . 
                '<a href="'. get_permalink( $post["ID"] ) . '"> <img src="' . 
                 get_the_post_thumbnail_url( $post["ID"]) 
                . '" width="100%" heihgt="auto" /></a>
                <br />
                <a href="' . get_permalink($post["ID"]) . '" class="date" >' 
                . mysql2date('d F Y', $post["post_date"]) 
                .'</a>
                <br />
                <a href="' . get_permalink($post["ID"]) . '">' .   $post["post_title"].'</a>
                </div>';
            }
            wp_reset_query();
            echo '</div>'; //-- END outter wrapper
		echo $after_widget;
	}

	function form( $instance ) {

		/** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Titel', 'evolution' ); ?>:</label><br />
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
        <p>
			<label for="<?php echo $this->get_field_id( 'sdn_title' ); ?>"><?php _e( 'Description', 'evolution' ); ?>:</label><br />
			<input type="text" id="<?php echo $this->get_field_id( 'sdn_title' ); ?>" name="<?php echo $this->get_field_name( 'sdn_title' ); ?>" value="<?php echo esc_attr( $instance['sdn_title'] ); ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_name( 'category' ); ?>"><?php _e( 'Choose a category', 'evolution' ); ?>:</label><br /><?php 
                $args = array(
                    'name'            => $this->get_field_name( 'category' ),
                    'id'              => $this->get_field_id( 'category' ),
                    'hierarchical'    => 1,
                    'selected'        => $instance['category'],
                    'show_option_all' => __( 'All', 'evolution' )
                );
                wp_dropdown_categories( $args ); 
            ?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e( 'How many posts do you want to display?', 'evolution' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>"><?php
				$sizes = array( __( '3', 'evolution' ) => 3, __( '6', 'evolution' ) => 6, __( '9', 'evolution' ) => 9 );
		
				foreach ( (array) $sizes as $label => $size ) { ?>
					<option value="<?php echo absint( $size ); ?>"<?php selected( $size, $instance['size'] ); ?>><?php printf( '%s', $label, $size ); ?></option><?php } ?></select>
		</p><?php
	}

 /**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
function update( $new_instance, $old_instance ) {

		$new_instance['title']          = strip_tags( $new_instance['title'] );
        $new_instance['sdn_title']      = strip_tags( $new_instance['sdn_title'] );

		return $new_instance;

	}

}
?>