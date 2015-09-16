<?php 
/*
Plugin Name: Wordpress plugin to ovveride the gallery shortcode
Plugin URI:  https://github.com/error500/mwf_gallery
Description: Plugin that override the gallery shortcode 
Version:     0.1
Author:      Error500
Author URI:  https://github.com/error500
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

*/


class Mwf_Gallery {
	public function __construct()
    {
    	
    	
		remove_shortcode('gallery', 'gallery_shortcode'); // removes the original shortcode
		add_shortcode('gallery', array($this,'mwf_gallery_shortcode'));
        
    }



	// Shortcodes
	function mwf_shortcode_photoset_flickr($atts){
		$retval ="";	
		extract( shortcode_atts( array(
			'photoset_id' => 'undefined'
	 	), $atts ) );
		
		
		
		$retval .='<div id="mwf_container" >';
	  	$retval .='<div id="mwf_photos" class="loading">';
	  	
	    foreach ($photos as $photo) {
	      if(isset($photo['url_l'])) {
	          $retval .='<a class="mwf_photo fancybox" rel="mwf" title="'.$photo['title'].'" href="'.$photo['url_l'].'">'.
	                  '<img src="'.$photo['url_m'].'" alt="'.$photo['title'].'"  />'.
	                  '<span class="title">'.$photo['title'].'</span>'.
	                '</a>';
	      }
	    }

		$retval .='</div>';  
		$retval .='</div>';
		$this->mwf_enqueue_scripts();
		
		return $retval;

	}


	 /**
	 *     @type string $order      Order of the images in the gallery. Default 'ASC'. Accepts 'ASC', 'DESC'.
	 *     @type string $orderby    The field to use when ordering the images. Default 'menu_order ID'.
	 *                              Accepts any valid SQL ORDERBY statement.
	 *     @type int    $id         Post ID.
	 *     @type int    $columns    Number of columns of images to display. Default 3.
	 *     @type string $size       Size of the images to display. Default 'thumbnail'.
	 *     @type string $ids        A comma-separated list of IDs of attachments to display. Default empty.
	 *     @type string $include    A comma-separated list of IDs of attachments to include. Default empty.
	 *     @type string $exclude    A comma-separated list of IDs of attachments to exclude. Default empty.
	 *     @type string $link       What to link each image to. Default empty (links to the attachment page).
	 *                              Accepts 'file', 'none'.
	 * }
	 * @return string HTML content to display gallery.
	 */
	function mwf_gallery_shortcode( $attr ) {
		$post = get_post();

		static $instance = 0;
		$instance++;

		if ( ! empty( $attr['ids'] ) ) {
			// 'ids' is explicitly ordered, unless you specify otherwise.
			if ( empty( $attr['orderby'] ) ) {
				$attr['orderby'] = 'post__in';
			}
			$attr['include'] = $attr['ids'];
		}

		$output = apply_filters( 'post_gallery', '', $attr, $instance );
		if ( $output != '' ) {
			return $output;
		}

//		$html5 = current_theme_supports( 'html5', 'gallery' );
		
		$atts = shortcode_atts( array(
			'order'      => 'ASC',
			'orderby'    => 'menu_order ID',
			'id'         => $post ? $post->ID : 0,
//			'columns'    => 3,
			'size'       => 'thumbnail',
//			'include'    => '',
//			'exclude'    => '',
//			'link'       => ''
		), $attr, 'gallery' );

		$id = intval( $atts['id'] );

		if ( ! empty( $atts['include'] ) ) {
			$_attachments = get_posts( array( 'include' => $atts['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );

			$attachments = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[$val->ID] = $_attachments[$key];

			}
		} elseif ( ! empty( $atts['exclude'] ) ) {
			$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $atts['exclude'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
		} else {
			$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
		}

		if ( empty( $attachments ) ) {
			return '';
		}

		if ( is_feed() ) {
			$output = "\n";
			foreach ( $attachments as $att_id => $attachment ) {
				$output .= wp_get_attachment_link( $att_id, $atts['size'], true ) . "\n";

			}
			return $output;
		}
		
		// HTML d'ouverture
		$gallery_div = "<div  id='mwf_container' >";
		$gallery_div .= " <div id='mwf_photos'  class='loading'>";

		//Styles
		$output = apply_filters( 'gallery_style', $gallery_style . $gallery_div );

		//Boucle de construction "output"
		$i = 0;
		foreach ( $attachments as $id => $attachment ) {
				
			$attr = ( trim( $attachment->post_excerpt ) ) ? array( 'aria-describedby' => "$selector-$id" ) : '';

			if ( ! empty( $atts['link'] ) && 'file' === $atts['link'] ) {
				$image_output = wp_get_attachment_link( $id, $atts['size'], false, false, false, $attr );
			} elseif ( ! empty( $atts['link'] ) && 'none' === $atts['link'] ) {
				$image_output = wp_get_attachment_image( $id, $atts['size'], false, $attr );
			} else {
				$image_output = wp_get_attachment_link( $id, $atts['size'], true, false, false, $attr );
			}
			
			$image_output_full = wp_get_attachment_image( $id, 'full', true, false, false, $attr );
			//var_dump($image_output_full); 
			$output .= "
				<a class='mwf_photo fancybox' rel='mwf'>
					$image_output_full";
			$output .= "<span class='title' id='$selector-$id'>
						" . wptexturize($attachment->post_excerpt) . "
						</span>";
			$output .= "</a>";

		}



		$output .= "</div>\n</div>\n";
		$this->mwf_enqueue_scripts();
		return $output;
	}
	function mwf_enqueue_scripts() {
		wp_enqueue_style(  'mwf_gallery', plugins_url('/css/mwf_gallery.css',__FILE__ ));
		wp_enqueue_style(  'fancybox', plugins_url('/js/fancybox/jquery.fancybox.css',__FILE__ ));
		wp_register_script( 'images-loaded', plugins_url('/js/imagesloaded/imagesloaded.pkgd.min.js',__FILE__),null,null, true );
		wp_register_script( 'fancybox', plugins_url('/js/fancybox/jquery.fancybox.pack.js',__FILE__) ,null,null, true );
		
		wp_enqueue_script(
			'mwf_script',
			plugins_url('/js/mwf_script.js',__FILE__),
			array( 'jquery','images-loaded','fancybox','jquery-masonry' ),null,true);
	}
}


// Masonry doesn't work with embeded jQuery version... so i change it here
if( !is_admin() ){
	wp_deregister_script('jquery');
	wp_register_script('jquery', plugins_url('js/jquery/jquery.min.js',__FILE__), null,null,true );
	wp_enqueue_script('jquery');
}


new Mwf_Gallery();



