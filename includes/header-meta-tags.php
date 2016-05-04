<?php

// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;

/*
 * Create twitter card and open graph tags
 */

class MASHSB_HEADER_META_TAGS {

    protected $postID = 0;
    protected $imageURL;
    protected $post_title;
    protected $post_featured_image;
    protected $post_description;
    protected $og_title = '';
    protected $og_description;
    protected $og_image;
    protected $fb_author_url;
    protected $fb_app_id;
    protected $twitter_title;
    protected $twitter_description;
    protected $twitter_image;
    protected $twitter_site;
    protected $twitter_creator;
    // Yoast Data
    protected $yoast_og_title;
    protected $yoast_og_description;
    protected $yoast_og_image;
    protected $yoast_seo_title;
    protected $yoast_seo_description;
    protected $yoast_twitter_title;
    protected $yoast_twitter_description;
    protected $yoast_twitter_image;
    protected $yoast_twitter_creator;
    // yoast social settings
    protected $yoast = array();
    // open-graph add-on data - outdated - only for compatibility
    //protected $addon_og_title;
    //protected $addon_og_description;
    //protected $addon_twitter_title;

    public function __construct() {
        global $post;

        // Die when $post is not set
        if( !get_the_ID() ) {
            return;
        }
        // Create Open Graph tags only on single blog post pages
        if( !is_singular() ) {
            return;
        }

        $this->postID = get_the_ID();
        $this->post_title = $this->get_title();
        $this->post_featured_image = $this->get_featured_image();
        $this->post_description = $this->get_excerpt_by_id( $this->postID );
        $this->get_og_data();
        // Do not want to support the old stuff so let's disable this
        //$this->get_og_add_on_data();
        $this->remove_jetpack_og();
        $this->remove_simple_podcast_press_og();
        $this->get_yoast_data();
        $this->render_header_meta();
    }

    /**
     * DEPRECATED 
     * @param type $content
     * @return string
     */
    function remove_sw_meta_tags( $content ) {
        return '';
    }

    /**
     * Get relevant main open graph and twitter data and use it for later functions
     * 
     * @return void
     */
    public function get_og_data() {
        $this->og_title = htmlspecialchars( get_post_meta( $this->postID, 'mashsb_og_title', true ) );
        $this->og_description = htmlspecialchars( get_post_meta( $this->postID, 'mashsb_og_description', true ) );
        $this->og_image = $this->get_image_url();
        $this->twitter_title = htmlspecialchars( get_post_meta( $this->postID, 'mashsb_custom_tweet', true ) );
        $this->twitter_creator = $this->get_twitter_creator();
        $this->twitter_site = mashsb_get_twitter_username();
    }

    /**
     * Get open graph add-on data
     * 
     * @deprecated
     * @return void
     */
    public function get_og_add_on_data() {
        $this->addon_og_title = htmlspecialchars( get_post_meta( $this->postID, '_og_title', true ) );
        $this->addon_og_description = htmlspecialchars( get_post_meta( $this->postID, '_og_description', true ) );
        $this->addon_twitter_title = htmlspecialchars( get_post_meta( $this->postID, 'mashog_tw_title', true ) );
    }

    /**
     * Get Yoast open graph and social data
     * 
     * @global array $wpseo_og WP SEO (Yoast open graph) settings 
     * @return void
     */
    public function get_yoast_data() {
        if( !defined( 'WPSEO_VERSION' ) ) {
            return;
        }

        global $wpseo_og;
        if( has_action( 'wpseo_head', array($wpseo_og, 'opengraph') ) ) {
            // Yoast open graph tags
            $this->yoast_og_title = get_post_meta( $this->postID, '_yoast_wpseo_opengraph-title', true );
            $this->yoast_og_description = get_post_meta( $this->postID, '_yoast_wpseo_opengraph-description', true );
            $this->yoast_og_image = get_post_meta( $this->postID, '_yoast_wpseo_opengraph-image', true );

            // Yoast twitter card data
            $this->yoast_twitter_title = get_post_meta( $this->postID, '_yoast_wpseo_twitter-title', true );
            $this->yoast_twitter_description = get_post_meta( $this->postID, '_yoast_wpseo_twitter-description', true );
            $this->yoast_twitter_image = get_post_meta( $this->postID, '_yoast_wpseo_twitter-image', true );

            // Yoast SEO title and description
            $this->yoast_seo_title = get_post_meta( $this->postID, '_yoast_wpseo_title', true );
            $this->yoast_seo_description = get_post_meta( $this->postID, '_yoast_wpseo_metadesc', true );

            // Remove Yoast open graph and twitter cards data from head of site
            remove_action( 'wpseo_head', array($wpseo_og, 'opengraph'), 30 );
            remove_action( 'wpseo_head', array('WPSEO_Twitter', 'get_instance'), 40 );

            // Get Yoast social settings
            $this->yoast = get_option( 'wpseo_social' );
        }
    }

    /**
     * Remove Jetpack Open Graph tags
     * 
     * @return void
     */
    public function remove_jetpack_og() {
        if( class_exists( 'JetPack' ) ) {
            remove_action( 'wp_head', 'jetpack_og_tags' );
        }
    }

    /**
     * Get the title
     * 
     * @return string
     */
    public function get_title() {
        return $this->replace_quote_characters( htmlspecialchars_decode( get_the_title() ) );
    }

    /**
     * Get the og title
     * 
     * @return string
     */
    public function get_og_title() {

        if( !empty( $this->og_title ) ) {
            return $this->og_title;
        }

        if( defined( 'WPSEO_VERSION' ) ) {
            if( !empty( $this->yoast_og_title ) ) {
                return $this->yoast_og_title;
            }
            if( !empty( $this->yoast_seo_title ) ) {
                return $this->yoast_seo_title;
            }
        }
        // Default return value
        return $this->post_title;
    }

    /**
     * Get the og description
     * 
     * @return string
     */
    public function get_og_description() {
        
        if( !empty( $this->og_description ) ) {
            return $this->og_description;
        }

        if( defined( 'WPSEO_VERSION' ) ) {
            if( !empty( $this->yoast_og_description ) ) {
                return $this->yoast_og_description;
            }
        }
        // Default return value
        return $this->post_description;
    }

    /**
     * Get the excerpt
     *
     * @param int $post_id
     * @since 2.5.9
     * @return string
     */
    function get_excerpt_by_id( $post_id ) {
        // Check if the post has an excerpt
        if( has_excerpt() ) {
            $excerpt_length = apply_filters( 'excerpt_length', 35 );
            return trim( get_the_excerpt() );
        }

        $the_post = get_post( $post_id ); //Gets post ID
        $the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
        $excerpt_length = 35; //Sets excerpt length by words
        $the_excerpt = strip_tags( strip_shortcodes( $the_excerpt ) ); //Strips tags and images
        $words = explode( ' ', $the_excerpt, $excerpt_length + 1 );
        if( count( $words ) > $excerpt_length ) {
            array_pop( $words );
            $the_excerpt = implode( ' ', $words );
        }

        return trim( wp_strip_all_tags( $the_excerpt ) );
    }

    /**
     * Get the og image
     * 
     * @return string
     */
    public function get_og_image() {
        if( !empty( $this->og_image ) ) {
            return $this->og_image;
        }

        if( defined( 'WPSEO_VERSION' ) ) {
            if( !empty( $this->yoast_og_image ) ) {
                return $this->yoast_og_image;
            }
        }
        return $this->post_featured_image;
    }

    /**
     * Get image size 
     * 
     * @param string image id
     * @return mixed array[0] = width, array[1] = height | boolean false
     */
    public function get_og_image_size() {
        if( empty( $this->get_og_image() ) ) {
            return;
        }
        $upload_dir = wp_upload_dir();
        $img_src = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $this->get_og_image() );
        return getimagesize( $img_src );
    }

    /**
     * Get the twitter image
     * 
     * @return string
     */
    public function get_twitter_image() {
        if( !empty( $this->twitter_image ) ) {
            return $this->twitter_image;
        }

        if( defined( 'WPSEO_VERSION' ) ) {
            if( !empty( $this->yoast_twitter_image ) ) {
                return $this->yoast_twitter_image;
            }
        }
        return $this->post_featured_image;
    }

    /**
     * Get the feature image
     * 
     * @return mixed string | boolean false on failure or no featured image available
     */
    public function get_featured_image() {
        // Return post thumbnail
        // rawurlencode() Must be tested for images with non lating characters
        //return rawurlencode( wp_get_attachment_url( get_post_thumbnail_id( $this->postID ) ) );
        return wp_get_attachment_url( get_post_thumbnail_id( $this->postID ) );
    }

    /**
     * Get custom open graph image url from mashsb og settings
     * 
     * @return string  
     */
    public function get_image_url() {
        $og_image = $this->get_og_image_id();
        if( $og_image ) {
            //return rawurlencode( wp_get_attachment_url( $og_image ) );
            return wp_get_attachment_url( $og_image );
        }
    }

    /**
     * Get the image id
     * 
     * @return int
     */
    public function get_og_image_id() {
        return get_post_meta( $this->postID, 'mashsb_og_image', true );
    }

    /**
     * Get the facebook author url
     * 
     * @return string
     */
    public function get_fb_author_url() {
        // MASHSB facebook author url
        if( get_the_author_meta( 'mashsb_fb_author', $this->get_post_author( $this->postID ) ) ) {
            return get_the_author_meta( 'mashsb_fb_author', $this->get_post_author( $this->postID ) );
        }
        // Yoast facebook author url
        if( get_the_author_meta( 'facebook', $this->get_post_author( $this->postID ) ) && defined( 'WPSEO_VERSION' ) ) {
            return get_the_author_meta( 'facebook', $this->get_post_author( $this->postID ) );
        }
    }

    /**
     * Get the twitter creator tag site:creator
     * 
     * @return string
     */
    public function get_twitter_creator() {
        // MASHSB facebook author url
        $twitter_handle = get_the_author_meta( 'mashsb_twitter_handle', $this->get_post_author( $this->postID ) );
        if( $twitter_handle ) {
            return $twitter_handle;
        }
        // Yoast facebook author url
        $yoast_twitter_handle = get_the_author_meta( 'twitter', $this->get_post_author( $this->postID ) );
        if( $yoast_twitter_handle ) {
            return $yoast_twitter_handle;
        }
    }

    /**
     * Get facebook publisher url
     * 
     * @global array $mashsb_options
     * @return string
     */
    public function get_fb_publisher_url() {
        global $mashsb_options;
        if( !empty( $mashsb_options['fb_publisher_url'] ) ) {
            return $mashsb_options['fb_publisher_url'];
        }
        if( !empty( $this->yoast['facebook_site'] ) ) {
            return $this->yoast['facebook_site'];
        }
    }

    /**
     * Get facebook app id
     * 
     * @global array $mashsb_options
     * @return string
     */
    public function get_fb_app_id() {
        global $mashsb_options;
        if( !empty( $mashsb_options['fb_app_id'] ) ) {
            return $mashsb_options['fb_app_id'];
        }
        if( !empty( $this->yoast ) && !empty( $this->yoast['fbadminapp'] ) ) {
            return $this->yoast['fbadminapp'];
        }
    }

    /**
     * Get the post author
     * 
     * @param int $post_id
     * @return string
     */
    public function get_post_author( $post_id = 0 ) {
        $post = get_post( $post_id );
        return $post->post_author;
    }

    /**
     * Get twitter title
     * 
     * @return string
     */
    public function get_twitter_title() {
        if( !empty( $this->twitter_title ) ) {
            return $this->twitter_title;
        }

        if( defined( 'WPSEO_VERSION' ) ) {
            if( !empty( $this->yoast_twitter_title ) ) {
                return $this->yoast_twitter_title;
            }
            if( !empty( $this->yoast_seo_title ) ) {
                return $this->yoast_seo_title;
            }
        }
        // Default return value
        return $this->post_title;
    }

    /**
     * Get twitter description
     * 
     * @return string
     */
    public function get_twitter_description() {
        if( defined( 'WPSEO_VERSION' ) ) {
            if( !empty( $this->yoast_twitter_description ) ) {
                return $this->yoast_twitter_description;
            }
        }
        // Default return value
        return $this->post_description;
    }

    /**
     * Remove Simple Podcast Press open graph tags
     * 
     * @global array $ob_wp_simplepodcastpress
     */
    public function remove_simple_podcast_press_og() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if( is_plugin_active( 'simple-podcast-press/simple-podcast-press.php' ) ) {
            global $ob_wp_simplepodcastpress;
            remove_action( 'wp_head', array($ob_wp_simplepodcastpress, 'spp_open_graph'), 1 );
        }
    }

    /**
     * Return special quote characters
     * 
     * @param string $content
     * @todo write a test
     * @return string
     */
    public function replace_quote_characters( $string ) {
        $string = str_replace( '"', '\'', $string );
        $string = str_replace( '&#8216;', '\'', $string );
        $string = str_replace( '&#8217;', '\'', $string );
        $string = str_replace( '&#8220;', '\'', $string );
        $string = str_replace( '&#8221;', '\'', $string );
        return $string;
    }

    /**
     * Render Meta Output
     * 
     * @return string HTML
     */
    public function render_header_meta() {

        $html = PHP_EOL . '<!-- Open Graph Meta Tags & Twitter Card generated by MashShare ' . MASHSB_VERSION . ' - https://mashshare.net -->';
        $html .= PHP_EOL . '<meta property="og:type" content="article" /> ';
        $html .= PHP_EOL . '<meta property="og:title" content="' . $this->get_og_title() . '" />';
        $html .= PHP_EOL . '<meta property="og:description" content="' . $this->get_og_description() . '" />';
        $html .= PHP_EOL . '<meta property="og:image" content="' . $this->get_og_image() . '" />';
        $html .= PHP_EOL . '<meta property="og:url" content="' . get_permalink() . '" />';
        $html .= PHP_EOL . '<meta property="og:site_name" content="' . get_bloginfo( 'name' ) . '" />';
        $html .= PHP_EOL . '<meta property="article:author" content="' . $this->get_fb_author_url() . '" />';
        $html .= PHP_EOL . '<meta property="article:publisher" content="' . $this->get_fb_publisher_url() . '" />';
        $html .= PHP_EOL . '<meta property="fb:app_id" content="' . $this->get_fb_app_id() . '" />';
        $html .= PHP_EOL . '<meta property="article:published_time" content="' . get_post_time( 'c' ) . '" />';
        $html .= PHP_EOL . '<meta property="article:modified_time" content="' . get_post_modified_time( 'c' ) . '" />';
        $html .= PHP_EOL . '<meta property="og:updated_time" content="' . get_post_modified_time( 'c' ) . '" />';
        if( !empty( $this->get_og_image_size()[0] ) ) {
            $html .= PHP_EOL . '<meta property="og:image:width" content="' . $this->get_og_image_size()[0] . '" />';
            $html .= PHP_EOL . '<meta property="og:image:height" content="' . $this->get_og_image_size()[1] . '" />';
        }
        // Large Summary Twitter Card
        if( $this->get_twitter_image() ) {
            $html .= PHP_EOL . '<meta name="twitter:card" content="summary_large_image">';
            $html .= PHP_EOL . '<meta name="twitter:title" content="' . $this->get_twitter_title() . '">';
            $html .= PHP_EOL . '<meta name="twitter:description" content="' . $this->get_twitter_description() . '">';
            $html .= PHP_EOL . '<meta name="twitter:image" content="' . $this->get_twitter_image() . '">';
            if( !empty( $this->twitter_site ) ) {
                $html .= PHP_EOL . '<meta name="twitter:site" content="@' . $this->twitter_site . '">';
            }
            if( !empty( $this->twitter_creator ) ) {
                $html .= PHP_EOL . '<meta name="twitter:creator" content="@' . str_replace( '@', '', $this->twitter_creator ) . '">';
            }

            // Small Summary Twitter Cards
        } else {
            $html .= PHP_EOL . '<meta name="twitter:card" content="summary">';
            $html .= PHP_EOL . '<meta name="twitter:title" content="' . $this->get_twitter_title() . '">';
            $html .= PHP_EOL . '<meta name="twitter:description" content="' . $this->get_twitter_description() . '">';
            if( !empty( $this->twitter_site ) ) {
                $html .= PHP_EOL . '<meta name="twitter:site" content="@' . $this->twitter_site . '">';
            }
            if( !empty( $this->twitter_creator ) ) {
                $html .= PHP_EOL . '<meta name="twitter:creator" content="@' . str_replace( '@', '', $this->twitter_creator ) . '">';
            }
        }
        $html .= PHP_EOL . '<!-- Open Graph Meta Tags & Twitter Card generated by MashShare ' . MASHSB_VERSION . ' - http://mashshare.net -->' . PHP_EOL . PHP_EOL;

        echo apply_filters( 'mashsb_meta_tags', $html );
    }

}

// end class

/**
 * Init - Execute only once
 * 
 * @global MASHSB_HEADER_META_TAGS $mashsb_meta_tags
 * @return object
 */
function mashsb_meta_tags_init() {
    global $mashsb_meta_tags;

    if( !is_null( $mashsb_meta_tags ) ) {
        return $mashsb_meta_tags;
    }

    $mashsb_meta_tags = new MASHSB_HEADER_META_TAGS();
    return $mashsb_meta_tags;
}

add_action( 'wp_head', 'mashsb_meta_tags_init', 1 );

//Remove Social Warfare tags open graph tags (Soory Social Warfare guys)
add_filter( 'sw_meta_tags', '__return_false', 99 );

