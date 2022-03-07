<?php

// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;

/*
 * Create twitter card and open graph tags
 */

class MASHSB_HEADER_META_TAGS {

    protected $postID   = 0;
    protected $imageURL;
    protected $post_title;
    protected $post_featured_image;
    protected $post_description;
    protected $og_title = '';
    protected $og_description;
    protected $og_image;
    protected $og_type;
    protected $fb_author_url;
    protected $fb_app_id;
    protected $twitter_title;
    protected $twitter_description;
    protected $twitter_image;
    protected $twitter_site;
    protected $twitter_creator;
    protected $pinterest_description;
    protected $pinterest_image;
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
    protected $yoast    = array();
    protected $post;

    public function __construct() {
        global $post;

        // Die when $post is not set
        if( !get_the_ID() ) {
            return;
        }
        // Create Open Graph tags only on singular pages and frontpage
        if( !is_singular() ) {
            return false;
        }

        $this->post                  = $post;
        $this->postID                = get_the_ID();
        $this->post_title            = $this->get_title();
        $this->post_featured_image   = $this->get_featured_image();
        $this->post_description      = $this->sanitize_data( $this->get_excerpt_by_id( $this->postID ) );
        $this->pinterest_image       = $this->get_pinterest_image_url();
        $this->pinterest_description = $this->get_pinterest_description();

        $this->get_og_data();
        // We do not want to support the old stuff so let's disable this. Hopefully no one demands it!
        //$this->get_og_add_on_data();
        $this->remove_jetpack_og();
        $this->remove_simple_podcast_press_og();
        $this->get_yoast_data();
        $this->render_header_meta();
    }

    /**
     * Get relevant main open graph, twitter and pinterest data and use it for later functions
     * 
     * @return void
     */
    public function get_og_data() {
        $this->og_title        = $this->sanitize_data( get_post_meta( $this->postID, 'mashsb_og_title', true ) );
        $this->og_description  = $this->sanitize_data( get_post_meta( $this->postID, 'mashsb_og_description', true ) );
        $this->og_image        = $this->get_image_url();
        $this->og_type         = $this->sanitize_data( get_post_meta( $this->postID, 'mashsb_og_type', true ) );
        $this->twitter_title   = $this->sanitize_data( get_post_meta( $this->postID, 'mashsb_custom_tweet', true ) );
        $this->twitter_creator = $this->get_twitter_creator();
        $this->twitter_site    = mashsb_get_twitter_username();
    }

    public function sanitize_data( $string ) {
        return htmlspecialchars( preg_replace( "/\r|\n/", " ", $string ) );
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
        if( has_action( 'wpseo_head', array($wpseo_og, 'opengraph') ) && function_exists( 'wpseo_replace_vars' ) ) {
            // Yoast open graph tags
            $this->yoast_og_title       = get_post_meta( $this->postID, '_yoast_wpseo_opengraph-title', true );
            $this->yoast_og_description = get_post_meta( $this->postID, '_yoast_wpseo_opengraph-description', true );
            $this->yoast_og_image       = get_post_meta( $this->postID, '_yoast_wpseo_opengraph-image', true );

            $this->yoast_og_title       = wpseo_replace_vars( $this->yoast_og_title, $this->post );
            $this->yoast_og_description = wpseo_replace_vars( $this->yoast_og_description, $this->post );
            $this->yoast_og_image       = wpseo_replace_vars( $this->yoast_og_image, $this->post );



            // Yoast twitter card data
            $this->yoast_twitter_title       = get_post_meta( $this->postID, '_yoast_wpseo_twitter-title', true );
            $this->yoast_twitter_description = get_post_meta( $this->postID, '_yoast_wpseo_twitter-description', true );
            $this->yoast_twitter_image       = get_post_meta( $this->postID, '_yoast_wpseo_twitter-image', true );

            $this->yoast_twitter_title       = wpseo_replace_vars( $this->yoast_twitter_title, $this->post );
            $this->yoast_twitter_description = wpseo_replace_vars( $this->yoast_twitter_description, $this->post );
            $this->yoast_twitter_image       = wpseo_replace_vars( $this->yoast_twitter_image, $this->post );

            // Yoast SEO title and description
            $this->yoast_seo_title       = get_post_meta( $this->postID, '_yoast_wpseo_title', true );
            $this->yoast_seo_description = get_post_meta( $this->postID, '_yoast_wpseo_metadesc', true );

            $this->yoast_seo_title       = wpseo_replace_vars( $this->yoast_seo_title, $this->post );
            $this->yoast_seo_description = wpseo_replace_vars( $this->yoast_seo_description, $this->post );

            // Remove Yoast open graph and twitter cards data from head of site
            if( $this->is_open_graph() ) {
                remove_action( 'wpseo_head', array($wpseo_og, 'opengraph'), 30 );
            }

            if( $this->is_twitter_card() ) {
                remove_action( 'wpseo_head', array('WPSEO_Twitter', 'get_instance'), 40 );
            }

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
        if( class_exists( 'JetPack' ) && $this->is_open_graph() ) {
            add_filter( 'jetpack_enable_opengraph', '__return_false', 99 );
        }
    }

    /**
     * Get the title
     * 
     * @return string
     */
    public function get_title() {
        //return mashsb_get_document_title();
        return $this->replace_quote_characters( htmlspecialchars_decode( mashsb_get_document_title() ) );
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
     * Get the og type
     * 
     * @return string
     */
    public function get_og_type() {

        if( !empty( $this->og_type ) ) {
            return $this->og_type;
        }

        // Default return value
        return 'article';
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

        $the_post       = get_post( $post_id ); //Gets post ID
        $the_excerpt    = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
        $excerpt_length = 35; //Sets excerpt length by words
        $the_excerpt    = strip_tags( strip_shortcodes( $the_excerpt ) ); //Strips tags and images
        $words          = explode( ' ', $the_excerpt, $excerpt_length + 1 );
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
     * @return mixed array|bool array[0] = width, array[1] = height | false no result
     */
    public function get_og_image_size() {
        $og_image = $this->get_og_image();
        if( empty( $og_image ) ) {
            return;
        }

        $upload_dir = wp_upload_dir();
        $img_src    = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $this->get_og_image() );

        $imagesize = is_readable( $img_src ) ? getimagesize( $img_src ) : '';

        if( !empty( $imagesize ) ) {
            return $imagesize;
        }
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
        return wp_get_attachment_url( get_post_thumbnail_id( $this->postID ) );
    }

    /**
     * Get custom open graph image url from mashsb og settings
     * 
     * @return string  
     */
    public function get_image_url() {
        $og_image = get_post_meta( $this->postID, 'mashsb_og_image', true );
        if( $og_image ) {
            //return rawurlencode( wp_get_attachment_url( $og_image ) );
            return wp_get_attachment_url( $og_image );
        }
    }

    /**
     * Get Pinterst image url from mashsb meta box settings
     * 
     * @return string  
     */
    public function get_pinterest_image_url() {
        $image = get_post_meta( $this->postID, 'mashsb_pinterest_image', true );

        if( $image ) {
            //return rawurlencode( wp_get_attachment_url( $og_image ) );
            return wp_get_attachment_url( $image );
        }

        $og_image = get_post_meta( $this->postID, 'mashsb_og_image', true );
        if( $og_image ) {
            //return rawurlencode( wp_get_attachment_url( $og_image ) );
            return wp_get_attachment_url( $og_image );
        }

        return $this->post_featured_image;
    }

    /**
     * Get pinterest description
     * 
     * @return string
     */
    public function get_pinterest_description() {
        $desc = get_post_meta( $this->postID, 'mashsb_pinterest_description', true );
        if( !empty( $desc ) ) {
            return $desc;
        }

        if( !empty( $this->og_description ) ) {
            return $this->og_description;
        }

        // Default return value
        return $this->post_description;
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
        return false;
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
        // Return Yoast twitter title
        if( defined( 'WPSEO_VERSION' ) ) {
            if( !empty( $this->yoast_twitter_title ) ) {
                return $this->yoast_twitter_title;
            }
            if( !empty( $this->yoast_seo_title ) ) {
                return $this->yoast_seo_title;
            }
        }

        // Return MashShare Twitter title
        if( !empty( $this->twitter_title ) ) {
            return $this->twitter_title;
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
        if( !$this->is_open_graph() ) {
            return;
        }
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if( is_plugin_active( 'simple-podcast-press/simple-podcast-press.php' ) ) {
            global $ob_wp_simplepodcastpress;
            remove_action( 'wp_head', array($ob_wp_simplepodcastpress, 'spp_open_graph'), 1 );
        }
    }

    /**
     * Return special quote characters
     * 
     * @param string $string
     * @todo write a test
     * @return string
     */
    public function replace_quote_characters( $string ) {
        $string = str_replace( '"', '\'', $string );
        $string = str_replace( '&#8216;', '\'', $string );
        $string = str_replace( '&#8217;', '\'', $string );
        $string = str_replace( '&#8220;', '\'', $string );
        $string = str_replace( '&#8221;', '\'', $string );
        $string = str_replace( '“', '&quot;', $string );
        $string = str_replace( '”', '&quot;', $string );
        return $string;
    }

    /**
     * Check if twitter card is enabled
     * 
     * @global array $mashsb_options
     * @return boolean
     */
    public function is_twitter_card() {
        global $mashsb_options;
        if( isset( $mashsb_options['twitter_card'] ) ) {
            return true;
        }
    }

    /**
     * Check if mashshare open graph meta tags are enabled
     * 
     * @global array $mashsb_options
     * @return boolean
     */
    public function is_open_graph() {
        global $mashsb_options;
        if( isset( $mashsb_options['open_graph'] ) ) {
            return true;
        }
    }

    /**
     * Render Open Graph Meta Output
     * 
     * @return string HTML
     */
    public function render_open_graph_meta() {

        if( !$this->is_open_graph() ) {
            $html = '';
            return $html;
        }

        $opengraph = PHP_EOL . '<!-- Open Graph Meta Tags generated by MashShare ' . MASHSB_VERSION . ' - https://mashshare.net -->';
        $opengraph .= PHP_EOL . '<meta property="og:type" content="' . $this->get_og_type() . '" /> ';
        if( $this->get_og_title() ) {
            $opengraph .= PHP_EOL . '<meta property="og:title" content="' . $this->get_og_title() . '" />';
        }
        if( $this->get_og_description() ) {
            $opengraph .= PHP_EOL . '<meta property="og:description" content="' . $this->get_og_description() . '" />';
        }
        if( $this->get_og_image() ) {
            $opengraph .= PHP_EOL . '<meta property="og:image" content="' . $this->get_og_image() . '" />';
        }
        $opengraph .= PHP_EOL . '<meta property="og:url" content="' . get_permalink() . '" />';
        $opengraph .= PHP_EOL . '<meta property="og:site_name" content="' . get_bloginfo( 'name' ) . '" />';
        if( $this->get_fb_author_url() ) {
            $opengraph .= PHP_EOL . '<meta property="article:author" content="' . $this->get_fb_author_url() . '" />';
        }
        if( $this->get_fb_publisher_url() ) {
            $opengraph .= PHP_EOL . '<meta property="article:publisher" content="' . $this->get_fb_publisher_url() . '" />';
        }
        if( $this->get_fb_app_id() ) {
            $opengraph .= PHP_EOL . '<meta property="fb:app_id" content="' . $this->get_fb_app_id() . '" />';
        }
        $opengraph .= PHP_EOL . '<meta property="article:published_time" content="' . get_post_time( 'c' ) . '" />';
        $opengraph .= PHP_EOL . '<meta property="article:modified_time" content="' . get_post_modified_time( 'c' ) . '" />';
        $opengraph .= PHP_EOL . '<meta property="og:updated_time" content="' . get_post_modified_time( 'c' ) . '" />';
        $opengraph .= PHP_EOL . '<!-- Open Graph Meta Tags generated by MashShare ' . MASHSB_VERSION . ' - https://www.mashshare.net -->';

        return $opengraph;
    }

    /**
     * Render Twitter Card Meta Output
     * 
     * @return string HTML
     */
    public function render_twitter_card_meta() {

        if( !$this->is_twitter_card() ) {
            $html = '';
            return $html;
        }

        $twittercard = PHP_EOL . '<!-- Twitter Card generated by MashShare ' . MASHSB_VERSION . ' - https://www.mashshare.net -->';

        $imagesize = $this->get_og_image_size();
        if( is_array( $imagesize ) && isset( $imagesize[0] ) && isset( $imagesize[1] ) ) {
            $twittercard .= PHP_EOL . '<meta property="og:image:width" content="' . $imagesize[0] . '" />';
            $twittercard .= PHP_EOL . '<meta property="og:image:height" content="' . $imagesize[1] . '" />';
        }
        // Large Summary Twitter Card
        if( $this->get_twitter_image() ) {
            $twittercard .= PHP_EOL . '<meta name="twitter:card" content="summary_large_image">';
            $twittercard .= PHP_EOL . '<meta name="twitter:title" content="' . $this->get_twitter_title() . '">';
            $twittercard .= PHP_EOL . '<meta name="twitter:description" content="' . $this->get_twitter_description() . '">';
            $twittercard .= PHP_EOL . '<meta name="twitter:image" content="' . $this->get_twitter_image() . '">';
            if( !empty( $this->twitter_site ) ) {
                $twittercard .= PHP_EOL . '<meta name="twitter:site" content="@' . $this->twitter_site . '">';
            }
            if( !empty( $this->twitter_creator ) ) {
                $twittercard .= PHP_EOL . '<meta name="twitter:creator" content="@' . str_replace( '@', '', $this->twitter_creator ) . '">';
            }

            // Small Summary Twitter Cards
        } else {
            $twittercard .= PHP_EOL . '<meta name="twitter:card" content="summary">';
            $twittercard .= PHP_EOL . '<meta name="twitter:title" content="' . $this->get_twitter_title() . '">';
            $twittercard .= PHP_EOL . '<meta name="twitter:description" content="' . $this->get_twitter_description() . '">';
            if( !empty( $this->twitter_site ) ) {
                $twittercard .= PHP_EOL . '<meta name="twitter:site" content="@' . $this->twitter_site . '">';
            }
            if( !empty( $this->twitter_creator ) ) {
                $twittercard .= PHP_EOL . '<meta name="twitter:creator" content="@' . str_replace( '@', '', $this->twitter_creator ) . '">';
            }
        }
        $twittercard .= PHP_EOL . '<!-- Twitter Card generated by MashShare ' . MASHSB_VERSION . ' - https://www.mashshare.net -->' . PHP_EOL . PHP_EOL;
        return $twittercard;
    }

    /**
     * Render Meta Output
     * 
     * @return string HTML
     */
    public function render_header_meta() {
        //echo apply_filters( 'mashsb_meta_tags', $html );
        echo apply_filters( 'mashsb_opengraph_meta', $this->render_open_graph_meta() );
        echo apply_filters( 'mashsb_twittercard_meta', $this->render_twitter_card_meta() );
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
    global $mashsb_meta_tags, $mashsb_options;

    // Do not show meta boxes
    if( isset( $mashsb_options['user_roles_for_sharing_options'] ) && in_array( 'disable', $mashsb_options['user_roles_for_sharing_options'] ) ) {
        return;
    }

    if( !is_null( $mashsb_meta_tags ) ) {
        return $mashsb_meta_tags;
    }

    $mashsb_meta_tags = new MASHSB_HEADER_META_TAGS();
    return $mashsb_meta_tags;
}

add_action( 'wp_head', 'mashsb_meta_tags_init', 1 );

// Remove Social Warfare tags open graph tags (Sorry Social Warfare guys - You do a great job)
add_filter( 'sw_meta_tags', '__return_false', 99 );

