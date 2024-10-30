<?php
/**
 * Shortcodes
 *
 * @package     LeaflyReviews\Shortcodes
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
class ShortcodeCache {
	
	// Path to cache folder (with trailing /)
	public $cache_path = 'wp-content/plugins/leafly-reviews/cache/';
	// Length of time to cache a file (in seconds)
	public $cache_time = 3600;
	// Cache file extension
	public $cache_extension = '.cache';

	// This is just a functionality wrapper function
	public function get_data($shortcode, $url)
	{
		if($data = $this->get_cache($shortcode)){
			return $data;
		} else {
			$data = $this->do_curl($url);
			$this->set_cache($shortcode, $data);
			return $data;
		}
	}

	public function set_cache($shortcode, $data)
	{
		file_put_contents($this->cache_path . $this->safe_filename($shortcode) . $this->cache_extension, $data);
	}

	public function get_cache($shortcode)
	{
		if($this->is_cached($shortcode)){
			$filename = $this->cache_path . $this->safe_filename($shortcode) . $this->cache_extension;
			return file_get_contents($filename);
		}

		return false;
	}

	public function is_cached($shortcode)
	{
		$filename = $this->cache_path . $this->safe_filename($shortcode) . $this->cache_extension;

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
 * LeaflyReviews Shortcode
 *
 * @since       1.0.0
 * @param       array $atts Shortcode attributes
 * @param       string $content
 * @return      string $return The LeaflyReviews
 */
 
function leafly_reviews_shortcode($atts){
	
	extract(shortcode_atts(array(
		'slug' => '',
		'limit' => '5',
		'avatar' => 'yes',
		'stars' => 'yes',
		'ratings' => 'yes',
		'recommend' => 'yes',
		'shopagain' => 'yes',
		'comments' => 'yes',
		'viewall' => 'yes',
	), $atts));
	
	ob_start();

        if( $slug !== '' ) {
				$cache = new ShortcodeCache();
				$cache->cache_path = 'wp-content/plugins/leafly-reviews/cache/';
				$cache->cache_time = 3600;

				if($data = $cache->get_cache('shortcode')){
					$body = json_decode($data,true);
				} else {
					$data = $cache->do_curl( 'http://data.leafly.com/locations/'. $slug .'/reviews?skip=0&take=100' );
					$cache->set_cache('shortcode', $data);
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
						if('yes' == $avatar ) {
							echo "<img src='". $review['avatar'] ."' alt='". $review['username'] ."' class='leafly-reviews-plugin-meta-avatar' />";
						}
					echo "<strong>". $review['username'] ." </strong>";
						// Display star rating for the review
						if('yes' == $stars ) {
							echo "<span class='leafly-reviews-plugin-meta-image'><img class='leafly-reviews-plugin-meta-rating' src='". $review['starImage'] ." alt='Dispensary Review' /></span>";
						}
					echo "</span></p>"; // end username display

					if('yes' == $comments ) {
						// Display reviewer comments
						echo "<p><span class='leafly-reviews-plugin-meta-item'><strong>Comments: </strong><br />". $review['comments'] ."</span></p>";
					}
					
					if('yes' == $ratings ) {
						echo "<p>";
						// Display MEDS rating
						echo "<span class='leafly-reviews-plugin-meta-item'><strong>Meds: </strong>" . ( empty( $review['meds'] ) ? "not yet rated<br />" : $review['meds'] . " out of 5 stars</span><br />" );
						// Display SERVICE rating
						echo "<span class='leafly-reviews-plugin-meta-item'><strong>Service: </strong>" . ( empty( $review['service'] ) ? "not yet rated<br />" : $review['service'] . " out of 5 stars</span><br />" );
						// Display ATMOSPHERE rating
						echo "<span class='leafly-reviews-plugin-meta-item'><strong>Atmosphere: </strong>" . ( empty( $review['atmosphere'] ) ? "not yet rated<br />" : $review['atmosphere'] . " out of 5 stars</span><br />" );
						echo "</p>";
					}
					
					if('yes' == $recommend ) {
						// Display user recommendation if they say YES
						if ( $review['wouldRecommend'] == true ) {
							echo "<span class='leafly-reviews-plugin-meta-item'><strong>Would recommend: </strong>Yes</span><br />";
						}
					}

					if('yes' == $shopagain ) {
						// Display if user would shop again if they say YES
						if ( $review['shopAgain'] == true ) {
							echo "<span class='leafly-reviews-plugin-meta-item'><strong>Would shop again: </strong>Yes</span><br />";
						}
					}
					
					echo "</div>";
					
					// Check review count
					if ($i++ == $limit) break;
				}

				if('yes' == $viewall ) {
					// Display a link to Leafly profile
					echo "<span class='leafly-reviews-plugin-meta-item'><a class='leafly-reviews-plugin-viewall' href='https://www.leafly.com/dispensary-info/". $slug ."/reviews' target='_blank'>View all reviews &rarr;</a>";
				}
            }
        } else {
            _e( 'No location has been specified!', 'leafly-reviews' );
        }
		
		$output_string=ob_get_contents();
		ob_end_clean();

		return $output_string;

}

add_shortcode('leaflyreviews', 'leafly_reviews_shortcode');
 
?>
