<?php
/**
 * Widget
 *
 * @package     LeaflyReviews\Widget
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

/*
 * SimpleCache v1.4.1
 *
 * By Gilbert Pellegrom
 * http://dev7studios.com
 *
 * Free to use and abuse under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */
class WidgetCache {
	
	// Path to cache folder (with trailing /)
	public $cache_path = 'wp-content/plugins/leafly-reviews/cache/';
	// Length of time to cache a file (in seconds)
	public $cache_time = 3600;
	// Cache file extension
	public $cache_extension = '.cache';

	// This is just a functionality wrapper function
	public function get_data($widget, $url)
	{
		if($data = $this->get_cache($widget)){
			return $data;
		} else {
			$data = $this->do_curl($url);
			$this->set_cache($widget, $data);
			return $data;
		}
	}

	public function set_cache($widget, $data)
	{
		file_put_contents($this->cache_path . $this->safe_filename($widget) . $this->cache_extension, $data);
	}

	public function get_cache($widget)
	{
		if($this->is_cached($widget)){
			$filename = $this->cache_path . $this->safe_filename($widget) . $this->cache_extension;
			return file_get_contents($filename);
		}

		return false;
	}

	public function is_cached($widget)
	{
		$filename = $this->cache_path . $this->safe_filename($widget) . $this->cache_extension;

		if(file_exists($filename) && (filemtime($filename) + $this->cache_time >= time())) return true;

		return false;
	}

	//Helper function for retrieving data from url
	public function do_curl($url)
	{
		if(function_exists("curl_init")){
			$appid = get_option("app_id");
			$appkey = get_option("app_key");

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER,array('app_id: '. $appid .'','app_key: '. $appkey .''));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			$content = curl_exec($ch);
			curl_close($ch);
			return $content;
		} else {
			return file_get_contents($url);
		}
	}

	//Helper function to validate filenames
	private function safe_filename($filename)
	{
		return preg_replace('/[^0-9a-z\.\_\-]/i','', strtolower($filename));
	}
}

/**
 * Leafly Reviews Widget
 *
 * @since       1.0.0
 */
class leaflyreviews_widget extends WP_Widget {

    /**
     * Constructor
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function leaflyreviews_widget() {
        parent::WP_Widget(
            false,
            __( 'Leafly Reviews', 'leafly-reviews' ),
            array(
                'description'  => __( 'Display your recent dispensary reviews from leafly.', 'leafly-reviews' )
            )
        );
    }

    /**
     * Widget definition
     *
     * @access      public
     * @since       1.0.0
     * @see         WP_Widget::widget
     * @param       array $args Arguments to pass to the widget
     * @param       array $instance A given widget instance
     * @return      void
     */
    public function widget( $args, $instance ) {
        if( ! isset( $args['id'] ) ) {
            $args['id'] = 'leafly_reviews_widget';
        }

        $title = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );

        echo $args['before_widget'];

        if( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        do_action( 'leafly_reviews_before_widget' );

        if( $instance['slug'] ) {
				$cache = new WidgetCache();
				$cache->cache_path = 'wp-content/plugins/leafly-reviews/cache/';
				$cache->cache_time = 3600;

				if($data = $cache->get_cache('widget')){
					$body = json_decode($data,true);
				} else {
					$data = $cache->do_curl( 'http://data.leafly.com/locations/'. $instance['slug'] .'/reviews?skip=0&take=100' );
					$cache->set_cache('widget', $data);
					$body = json_decode($data,true);
				}
				
				if ($data == "Authentication parameters missing") {
					echo $data;
				} else {
				$i = 1;

				foreach( $body['reviews'] as $review) {
					echo "<div class='leafly-reviews-plugin-meta'>";
					
					// Display the username who left the review
					echo "<p><span class='leafly-reviews-plugin-meta-username'>";
						// Display avatar if selected in the widget
						if('on' == $instance['avatar'] ) {
							echo "<img src='". $review['avatar'] ."' alt='". $review['username'] ."' class='leafly-reviews-plugin-meta-avatar' />";
						}
					echo "<strong>". $review['username'] ." </strong>";
						// Display star rating for the review
						if('on' == $instance['stars'] ) {
							echo "<span class='leafly-reviews-plugin-meta-image'><img class='leafly-reviews-plugin-meta-rating' src='". $review['starImage'] ." alt='Dispensary Review' /></span>";
						}
					echo "</span></p>"; // end username display

					if('on' == $instance['comments'] ) {
						// Display reviewer comments
						echo "<p><span class='leafly-reviews-plugin-meta-item'><strong>Comments: </strong><br />". $review['comments'] ."</span></p>";
					}
					
					if('on' == $instance['ratings'] ) {
						echo "<p>";
						// Display MEDS rating
						echo "<span class='leafly-reviews-plugin-meta-item'><strong>Meds: </strong>" . ( empty( $review['meds'] ) ? "not yet rated<br />" : $review['meds'] . " out of 5 stars</span><br />" );
						// Display SERVICE rating
						echo "<span class='leafly-reviews-plugin-meta-item'><strong>Service: </strong>" . ( empty( $review['service'] ) ? "not yet rated<br />" : $review['service'] . " out of 5 stars</span><br />" );
						// Display ATMOSPHERE rating
						echo "<span class='leafly-reviews-plugin-meta-item'><strong>Atmosphere: </strong>" . ( empty( $review['atmosphere'] ) ? "not yet rated<br />" : $review['atmosphere'] . " out of 5 stars</span><br />" );
						echo "</p>";
					}
					
					if('on' == $instance['recommend'] ) {
						// Display user recommendation if they say YES
						if ( $review['wouldRecommend'] == true ) {
							echo "<span class='leafly-reviews-plugin-meta-item'><strong>Would recommend: </strong>Yes</span><br />";
						}
					}

					if('on' == $instance['shopagain'] ) {
						// Display if user would shop again if they say YES
						if ( $review['shopAgain'] == true ) {
							echo "<span class='leafly-reviews-plugin-meta-item'><strong>Would shop again: </strong>Yes</span><br />";
						}
					}
					
					echo "</div>";
					
					// Check review count
					if ($i++ == $instance['limit']) break;
				}

				if('on' == $instance['viewall'] ) {
					// Display if user would shop again if they say YES
					echo "<span class='leafly-reviews-plugin-meta-item'><a class='leafly-reviews-plugin-viewall' href='https://www.leafly.com/dispensary-info/". $instance['slug'] ."/reviews' target='_blank'>View all reviews &rarr;</a></span>";
				}

            }
        } else {
            _e( 'No location has been specified!', 'leafly-reviews' );
        }

        do_action( 'leafly_reviews_after_widget' );
        
        echo $args['after_widget'];
    }


    /**
     * Update widget options
     *
     * @access      public
     * @since       1.0.0
     * @see         WP_Widget::update
     * @param       array $new_instance The updated options
     * @param       array $old_instance The old options
     * @return      array $instance The updated instance options
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['title']      	= strip_tags( $new_instance['title'] );
        $instance['slug']   		= strip_tags( $new_instance['slug'] );
        $instance['limit']   		= strip_tags( $new_instance['limit'] );
        $instance['avatar']		= $new_instance['avatar'];
        $instance['stars']		= $new_instance['stars'];
        $instance['ratings']		= $new_instance['ratings'];
        $instance['recommend']		= $new_instance['recommend'];
        $instance['shopagain']		= $new_instance['shopagain'];
        $instance['comments']		= $new_instance['comments'];
        $instance['viewall']		= $new_instance['viewall'];

        return $instance;
    }


    /**
     * Display widget form on dashboard
     *
     * @access      public
     * @since       1.0.0
     * @see         WP_Widget::form
     * @param       array $instance A given widget instance
     * @return      void
     */
    public function form( $instance ) {
        $defaults = array(
            'title'     => 'Leafly Reviews',
            'slug'  	=> '',
            'limit'  	=> '5',
            'avatar' 	=> '',
            'stars' 	=> '',
            'ratings' 	=> '',
            'recommend' => '',
            'shopagain' => '',
            'comments'  => '',
	    'viewall'	=> ''
        );

        $instance = wp_parse_args( (array) $instance, $defaults );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'leafly-reviews' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'slug' ) ); ?>"><?php _e( 'Location slug (ex: denver-relief):', 'leafly-reviews' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'slug' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'slug' ) ); ?>" type="text" value="<?php echo $instance['slug']; ?>" />
        </p>
		
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php _e( 'Amount of reviews to show:', 'leafly-reviews' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" type="number" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" min="1" max="999" value="<?php echo $instance['limit']; ?>" />
        </p>
		
	    <p>
			<input class="checkbox" type="checkbox" <?php checked($instance['avatar'], 'on'); ?> id="<?php echo $this->get_field_id('avatar'); ?>" name="<?php echo $this->get_field_name('avatar'); ?>" /> 
			<label for="<?php echo esc_attr( $this->get_field_id( 'avatar' ) ); ?>"><?php _e( 'Display user avatar?', 'leafly-reviews' ); ?></label>
        </p>

	    <p>
			<input class="checkbox" type="checkbox" <?php checked($instance['stars'], 'on'); ?> id="<?php echo $this->get_field_id('stars'); ?>" name="<?php echo $this->get_field_name('stars'); ?>" /> 
			<label for="<?php echo esc_attr( $this->get_field_id( 'stars' ) ); ?>"><?php _e( 'Display star rating image?', 'leafly-reviews' ); ?></label>
        </p>

	    <p>
			<input class="checkbox" type="checkbox" <?php checked($instance['ratings'], 'on'); ?> id="<?php echo $this->get_field_id('ratings'); ?>" name="<?php echo $this->get_field_name('ratings'); ?>" /> 
			<label for="<?php echo esc_attr( $this->get_field_id( 'ratings' ) ); ?>"><?php _e( 'Display detailed ratings?', 'leafly-reviews' ); ?></label>
        </p>

	    <p>
			<input class="checkbox" type="checkbox" <?php checked($instance['recommend'], 'on'); ?> id="<?php echo $this->get_field_id('recommend'); ?>" name="<?php echo $this->get_field_name('recommend'); ?>" /> 
			<label for="<?php echo esc_attr( $this->get_field_id( 'recommend' ) ); ?>"><?php _e( 'Display if reviewer recommends you?', 'leafly-reviews' ); ?></label>
        </p>

	    <p>
			<input class="checkbox" type="checkbox" <?php checked($instance['shopagain'], 'on'); ?> id="<?php echo $this->get_field_id('shopagain'); ?>" name="<?php echo $this->get_field_name('shopagain'); ?>" /> 
			<label for="<?php echo esc_attr( $this->get_field_id( 'shopagain' ) ); ?>"><?php _e( 'Display if reviewer would shop again?', 'leafly-reviews' ); ?></label>
        </p>

	    <p>
			<input class="checkbox" type="checkbox" <?php checked($instance['comments'], 'on'); ?> id="<?php echo $this->get_field_id('comments'); ?>" name="<?php echo $this->get_field_name('comments'); ?>" /> 
			<label for="<?php echo esc_attr( $this->get_field_id( 'comments' ) ); ?>"><?php _e( 'Display reviewer comments?', 'leafly-reviews' ); ?></label>
        </p>

	    <p>
			<input class="checkbox" type="checkbox" <?php checked($instance['viewall'], 'on'); ?> id="<?php echo $this->get_field_id('viewall'); ?>" name="<?php echo $this->get_field_name('viewall'); ?>" /> 
			<label for="<?php echo esc_attr( $this->get_field_id( 'viewall' ) ); ?>"><?php _e( 'Display link to all reviews on Leafly?', 'leafly-reviews' ); ?></label>
        </p>

		<?php
    }
}


/**
 * Register the new widget
 *
 * @since       1.0.0
 * @return      void
 */
function leaflyreviews_register_widget() {
    register_widget( 'leaflyreviews_widget' );
}
add_action( 'widgets_init', 'leaflyreviews_register_widget' );
