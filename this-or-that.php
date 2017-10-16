<?php

/*
Plugin Name: This-or-That by Andr&eacute; Boekhorst
Plugin URI: http://andreboekhorst.nl/wordpress/this-or-that-plugin/
Description: Let your visitors vote between posts and images to create honest ranking lists. Uses the ELO Algorithm and works with your site's existing content.
Version: 1.0.4
Author: André Boekhorst
Author URI: http://www.andreboekhorst.nl
License: GPL2
*/

/*  Copyright 2013  ANDRE BOEKHORST

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class ThisThat {

	const KFACTOR = 12;
	const DEFAULT_RATING = 1400;

	public function __construct(){

		$this->pluginURL = plugin_dir_url( __FILE__ );
		$this->pluginDIR = dirname(__FILE__);

		add_shortcode('thisorthat', array($this, 'shortcode_show_compare') );
		add_shortcode('thisorthat_ranking', array($this, 'shortcode_rankings') );

		add_action( 'init', array($this, 'thisthat_router' ), 100 );
		add_action( 'init', array($this, 'addCustomPostType' ), 100 );

		if( !is_admin() ){
			add_action( 'wp_enqueue_scripts', array($this, 'enqueue_css_js') );
		}

		add_action( 'admin_head', array($this, 'custom_post_type_icon' ) );

	}

	public function thisthat_router(){
		if( !isset( $_GET["thisthat"] ) ) {
			try {
				parse_str(parse_url($_GET['q'])['query'], $query);
				$_GET["thisthat"] = $query["thisthat"];
			} catch (Exception $e) {}
		}

		if( isset( $_GET["thisthat"] ) ){
			if( isset($_GET["_wpnonce"]) ){
				if ( ! wp_verify_nonce( $_GET["_wpnonce"]) ) {
					wp_die( __('This-or-That plugin: Nonce error', 'this-or-that') );
				}
			} else {
					wp_die( __('This-or-That plugin: No nonce', 'this-or-that') );
			}

			$thisthat_string = htmlspecialchars( $_GET["thisthat"] );
			$thisthat_array = explode( ',', $thisthat_string );

			if( count($thisthat_array) !== 3 ){
				wp_die( __('This or That plugin: You need to have 3 arguments', 'this-or-that') );
			}

			$postid_A = $thisthat_array[0];
			$postid_B = $thisthat_array[1];
			$winnerID = $thisthat_array[2];
			// Save the new values!
			$result = $this->_processScores($postid_A, $postid_B, $winnerID);

			// If it works, just show a refresh of the current page with new items, else give an error.
			// We use jQuery to make an Ajax request. While saving the results, it simultaniously fetches
			// the new 'items' and appends it to the page. (See the javascript file for more info).
			if($result != true){
				wp_die( __('Couldn\'t save the new score', 'this-or-that') );
			}

		}

	}

	protected function _processScores( $postid_A, $postid_B, $winnerID ){

		$ratingA 	= $this->getPostRating( $postid_A );
		$winsA 		= $this->getPostWins( $postid_A );
		$lossesA 	= $this->getPostLosses( $postid_A );

		$ratingB 	= $this->getPostRating( $postid_B );
		$winsB 		= $this->getPostWins( $postid_B );
		$lossesB 	= $this->getPostLosses( $postid_B );

		if($winnerID == $postid_A){

			$scoreA = 1;
			$scoreB = 0;

			$winsA++;
			$lossesB++;

			update_post_meta( (int) $postid_A, 'thisorthat_wins', (string) $winsA );
			update_post_meta( (int) $postid_B, 'thisorthat_losses', (string) $lossesB );

		} elseif( $winnerID == $postid_B){

			$scoreA = 0;
			$scoreB = 1;

			$lossesA++;
			$winsB++;

			update_post_meta( (int) $postid_A, 'thisorthat_losses', (string) $lossesA );
			update_post_meta( (int) $postid_B, 'thisorthat_wins', (string) $winsB );

		} else {

			wp_die('This or That plugin: Somethings wrong with your arguments.');

		}

		//update ratings
		$expectedScores = $this->_getExpected($ratingA, $ratingB);
		$newRatings = $this ->_getNewRatings($ratingA, $ratingB, $expectedScores['a'], $expectedScores['b'], $scoreA, $scoreB);

		update_post_meta( (int) $postid_A, 'thisorthat_rating', (string) $newRatings['a'] );
		update_post_meta( (int) $postid_B, 'thisorthat_rating', (string) $newRatings['b'] );

		return true;

	}

	protected function _getExpected($ratingA, $ratingB){

		$expectedScoreA = 1 / ( 1 + ( pow( 10 , ( $ratingB - $ratingA ) / 400 ) ) );
        $expectedScoreB = 1 / ( 1 + ( pow( 10 , ( $ratingA - $ratingB ) / 400 ) ) );

        return array (
            'a' => $expectedScoreA,
            'b' => $expectedScoreB
        );

	}

	protected function _getNewRatings($ratingA, $ratingB, $expectedA, $expectedB, $scoreA, $scoreB){
		// score is 1, 0.5, or 0
		$newRatingA = $ratingA + ( self::KFACTOR * ( $scoreA - $expectedA ) );
        $newRatingB = $ratingB + ( self::KFACTOR * ( $scoreB - $expectedB ) );

        return array (
            'a' => round($newRatingA),
            'b' => round($newRatingB)
        );
	}

	protected function getPostRating( $postID ){

		$post_rating = get_post_meta( $postID, 'thisorthat_rating', true );

		if( $post_rating == '' ){
			$post_rating = 1400;
		}

		return (int) $post_rating;

	}

	protected function getPostWins( $postID ){

		$post_wins = get_post_meta( $postID, 'thisorthat_wins', true );

		if( $post_wins == '' ){
			$post_wins = 0;
		}

		return (int) $post_wins;

	}

	protected function getPostLosses( $postID ){

		$post_losses = get_post_meta( $postID, 'thisorthat_losses', true );

		if( $post_losses == '' ){
			$post_losses = 0;
		}

		return (int) $post_losses;

	}

	public function shortcode_show_compare( $atts ){

		extract( shortcode_atts( array(
			'posttype' 		=> 'this-or-that',
			'taxonomy'		=> 'thisorthat_category', //Moeten nog een regel voor komen
			'category'		=> '',
			'thumb_size'	=> 'large',
			'style'			=> '',
			'show_image' 	=> 'true',
			'show_title'	=> 'true',
			'show_excerpt'	=> 'false',
			'show_score'	=> 'false'
		), $atts ) );

		// Change default taxonomy when using normal 'posts'
		if( $posttype == 'post' && $taxonomy == 'thisorthat_category'){
			$taxonomy = 'category';
		}

		$thisthat_query = new WP_Query(array(
	        'post_type' 		=> $posttype,
	        'orderby' 			=> 'rand',
	        'category_name'		=> '',
	        'posts_per_page' 	=> '2',
	        $taxonomy 			=> $category
	    ));

		$classes[] = $style;

	    if ( $thisthat_query->have_posts() ){ ?>

	    	<?php ob_start(); ?>

	    	<?php
	    		$posts = $thisthat_query->posts;
	    		$post_a = $posts[0]->ID;
	    		$post_b = $posts[1]->ID;
	    	?>

	    	<div id="this-or-that" class="<?php echo implode(' ', $classes); ?>">
				<div class="this-or-that-wrapper">

		    	<?php while ( $thisthat_query->have_posts() ) : ?>

		    		<?php $thisthat_query->the_post(); ?>
					<?php $url = '?thisthat=' . $post_a . ',' . $post_b . ',' . get_the_ID(); ?>

					<div class="this-or-that_column">

		    			<div class="this-or-that_item">


							<?php if ( has_post_thumbnail() && $show_image == 'true' ) : ?>
								<div class="this-or-that_thumbwrapper">
								   <a href="<?php echo wp_nonce_url($url); ?>" title="<?php the_title_attribute(); ?>" class="this-or-that-btn" rel="nofollow">
								   <?php the_post_thumbnail( $thumb_size ); ?>
								   </a>
								</div>
							<?php endif; ?>


				    		<?php if ( $show_score == 'true' ) : ?>
				    			<h3>Ranking: <?php echo get_post_meta( get_the_ID(), 'thisorthat_rating', true); ?></h3>
				    		<?php endif; ?>

			    			<?php if ( $show_title == 'true' ) : ?>
				    			<h3><a href="<?php echo wp_nonce_url($url); ?>" class="this-or-that-btn" rel="nofollow"><?php echo get_the_title(); ?></a></h3>
				    		<?php endif; ?>

			    			<?php if ( $show_excerpt == 'true' ) : ?>
				    			<?php the_excerpt(); ?>
				    		<?php endif; ?>

			    		</div><!-- .this-or-that_item -->

			    	</div><!-- .this-or-that_column -->

		    	<?php endwhile; ?>

		    	</div><!-- .this-or-that-wrapper -->
	    	</div><!-- "#this_or-that" -->

	    	<?php $output = ob_get_contents(); ?>
			<?php ob_end_clean(); ?>

	    <?php }

		wp_reset_query();

		return $output;

	}

	public function shortcode_rankings( $atts ){

		extract( shortcode_atts( array(
			'posttype' 	=> 'this-or-that',
			'length'	=> '10',
			'taxonomy'		=> 'thisorthat_category',
			'category'		=> '',

			'show_title'	=> 'true',
			'show_image'	=> 'true',
			'show_excerpt'	=> 'true',
			'show_score'	=> 'false',

			'thumb_size'	=> 'medium'

		), $atts ) );

		// Change default taxonomy when using normal 'posts'
		if( $posttype == 'post' && $taxonomy == 'thisorthat_category'){
			$taxonomy = 'category';
		}

		$thisthat_query = new WP_Query(array(
	        'post_type' 		=> $posttype,
	        'meta_key'			=> 'thisorthat_rating',
	        'orderby' 			=> 'thisorthat_rating',
	        'category_name'		=> '',
	        'posts_per_page' 	=> $length,
	        $taxonomy 			=> $category

	    ));

	    if ($thisthat_query->have_posts() ) : ?>

	   		<?php ob_start(); ?>
<?php
// echo "blah";
// 		    		print_r([$atts, $show_score]);

		    ?>
	    	<ol class="this_that_ranking_list">

				<?php $count = 1; ?>

				<?php while ( $thisthat_query->have_posts() ) : $thisthat_query->the_post(); ?>

					<li>

						<div class="thisorthat_left">
							<?php if ( has_post_thumbnail() && $show_image == 'true' ) : ?>
								<div class="thisorthat_thumb">
									<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( $thumb_size ); ?></a>
								</div>
							<?php endif; ?>
						</div>

						<div class="thisorthat_right">

							<?php if ( $show_title == 'true' ) : ?>
							<h3><a href="<?php the_permalink(); ?>"><span class="count"><?php echo $count ?></span>. <span class="title"><?php the_title(); ?></span></a></h3>
							<?php endif; ?>

							<?php if ( $show_score == 'true' ) : ?>
							<div class="thisorthat_meta">
								<span class="thisorthat_rating">Rating: <?php echo get_post_meta( get_the_ID(), 'thisorthat_rating', true); ?></span>
								<span class="thisorthat_wins">Wins: <?php echo get_post_meta( get_the_ID(), 'thisorthat_wins', true); ?></span>
								<span class="thisorthat_losses">Losses: <?php echo get_post_meta( get_the_ID(), 'thisorthat_losses', true); ?></span>
							</div>
							<?php endif; ?>

							<?php if ( $show_excerpt == 'true' ) : ?>
							<div class="thisorthat_description">
								<?php the_excerpt(); ?>
							</div>
							<?php endif; ?>

						</div>

					</li>

					<?php $count++; ?>

				<?php endwhile; ?>

			</ol>

			<?php wp_reset_query(); ?>

		<?php
		$output = ob_get_contents();
	    	ob_end_clean();
		?>
		<?php endif;

		return $output;
	}

	public function enqueue_css_js(){
//		wp_register_script( 'jQuery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js', null, null, true );
		wp_enqueue_script('this-or-that', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js');
		wp_enqueue_style( 'this-or-that', $this->pluginURL . 'css/this-or-that.css' );
		wp_enqueue_script( 'this-or-that', $this->pluginURL . 'js/this-or-that.js', array('jquery', 'jquery-effects-core', 'jquery-effects-scale'), '1.0', true );
	}

	public function addCustomPostType(){

		$labels = array(
			'name' => __('This-or-That', 'this-or-that'),
			'singular_name' => __('item', 'this-or-that'),
			'add_new' => __('Add item', 'this-or-that'),
			'add_new_item' => __('Add new item', 'this-or-that'),
			'edit_item' => __('Edit item', 'this-or-that'),
			'new_item' => __('New item', 'this-or-that'),
			'all_items' => __('All items', 'this-or-that'),
			'view_item' => __('View items', 'this-or-that'),
			'search_items' => __('Search items', 'this-or-that'),
			'not_found' =>  __('No items found', 'this-or-that'),
			'not_found_in_trash' => __('No items found in trash', 'this-or-that'),
			'menu_name' => __('This-or-That', 'this-or-that')
		);

		$posttype_args = array(
			'labels' => $labels,
			//'menu_icon' => $this->pluginURL . '/img/icon-adminmenu16-sprite.png',  // Icon Path
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => true,
			'menu_position' => 100,
			'supports' => array('title','editor','author','thumbnail','excerpt', 'custom-fields' )
		);

		// Add new taxonomy, NOT hierarchical (like tags)
		$tax_labels = array(
			'name'                       => _x( 'Categories', 'this-or-that' ),
			'singular_name'              => _x( 'Category', 'this-or-that' ),
			'menu_name'                  => __( 'Categories', 'this-or-that' ),
		);

		register_post_type('this-or-that', $posttype_args);

		$tax_args = array(
			'hierarchical'          => false,
			'labels'                => $tax_labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'writer' ),
		);

		register_taxonomy( 'thisorthat_category', 'this-or-that', $tax_args );


		/* Add rating column in post overview */

		add_filter( 'manage_edit-this-or-that_columns', 'thisorthat_edit_columns' );

		function thisorthat_edit_columns( $columns ) {
			$new_columns = array(
				'thisorthat_rating' => __('Rating', 'this-or-that'),
				'thisorthat_wins' => __('Wins', 'this-or-that'),
				'thisorthat_losses' => __('Losses', 'this-or-that'),
			);
		    return array_merge($columns, $new_columns);
		}

		add_action( 'manage_this-or-that_posts_custom_column', 'thisorthat_custom_columns' );

		function thisorthat_custom_columns( $column ) {


	 		global $post;

			switch ( $column ) {

				case 'thisorthat_rating' :

					if ( get_post_meta($post->ID, "thisorthat_rating", $single = true) != "" ) {
						echo get_post_meta($post->ID, "thisorthat_rating", $single = true);
					}  else {
						echo '—';
					}

				break;

				case 'thisorthat_wins' :

					if ( get_post_meta($post->ID, "thisorthat_wins", $single = true) != "" ) {
						echo get_post_meta($post->ID, "thisorthat_wins", $single = true);
					}  else {
						echo '0';
					}

				break;

				case 'thisorthat_losses' :


					if ( get_post_meta($post->ID, "thisorthat_losses", $single = true) != "" ) {
						echo get_post_meta($post->ID, "thisorthat_losses", $single = true);
					}  else {
						echo '0';
					}

				break;

			}

		}

		/* Make columns sortable */

		add_filter( 'manage_edit-this-or-that_sortable_columns', 'my_movie_sortable_columns' );

		function my_movie_sortable_columns( $columns ) {
			$columns['thisorthat_rating'] = 'thisorthat_rating';
			$columns['thisorthat_wins'] = 'thisorthat_wins';
			$columns['thisorthat_losses'] = 'thisorthat_losses';
			return $columns;
		}

		// Only run our customization on the 'edit.php' page in the admin.
		add_action( 'load-edit.php', 'my_edit_movie_load' );

		function my_edit_movie_load() {
			add_filter( 'request', 'my_sort_thisorthat' );
		}

		function my_sort_thisorthat( $vars ) {

			if ( isset($vars['post_type']) && 'this-or-that' == $vars['post_type'] ) {

				if ( isset($vars['orderby']) ){

					switch ( $vars['orderby'] ){

						case 'thisorthat_rating' :
							$vars = array_merge( $vars, array('meta_key' => 'thisorthat_rating','orderby' => 'meta_value_num') );
						break;

						case 'thisorthat_wins' :
							$vars = array_merge( $vars, array('meta_key' => 'thisorthat_wins','orderby' => 'meta_value_num') );
						break;

						case 'thisorthat_losses' :
							$vars = array_merge( $vars, array('meta_key' => 'thisorthat_losses','orderby' => 'meta_value_num') );
						break;

					}

				}

			}

			return $vars;
		}


	}

	public function custom_post_type_icon() {
	    ?>
	    <style>
	        /* Admin Menu - 16px */
	        #menu-posts-this-or-that .wp-menu-image {
	            background: url(<?php echo $this->pluginURL; ?>/img/icon-adminmenu16-sprite.png) no-repeat 6px 6px !important;
	        }
	        #menu-posts-this-or-that:hover .wp-menu-image, #menu-posts-this-or-that.wp-has-current-submenu .wp-menu-image {
	            background-position: 6px -26px !important;
	        }
	        /* Post Screen - 32px */
	        .icon32-posts-this-or-that {
	            background: url(<?php echo $this->pluginURL; ?>/img/icon-adminpage32.png) no-repeat left top !important;
	        }
	        @media
	        only screen and (-webkit-min-device-pixel-ratio: 1.5),
	        only screen and (   min--moz-device-pixel-ratio: 1.5),
	        only screen and (     -o-min-device-pixel-ratio: 3/2),
	        only screen and (        min-device-pixel-ratio: 1.5),
	        only screen and (                min-resolution: 1.5dppx) {

	            /* Admin Menu - 16px @2x */
	            #menu-posts-this-or-that .wp-menu-image {
	                background-image: url('<?php echo $this->pluginURL; ?>/img/icon-adminmenu16-sprite_2x.png') !important;
	                -webkit-background-size: 16px 48px !important;
	                -moz-background-size: 16px 48px !important;
	                background-size: 16px 48px !important;
	            }
	            /* Post Screen - 32px @2x */
	            .icon32-posts-this-or-that {
	                background-image: url('<?php echo $this->pluginURL; ?>/img/icon-adminpage32_2x.png') !important;
	                -webkit-background-size: 32px 32px !important;
	                -moz-background-size: 32px 32px !important;
	                background-size: 32px 32px !important;
	            }
	        }
	    </style>
		<?php
	}

}

$thisthat = new ThisThat();



?>
