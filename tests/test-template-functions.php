<?php

class TemplateFunctions extends WP_UnitTestCase {

    /**
     * 
     */
    function setUp() {
        parent::setUp();
        global $mashsb_options;
        $mashsb_options['disable_cache'] = 'true';
        $mashsb_options['fb_access_token'] = 'EAAHag2FMn2UBAAU6ceKGLZCN6PAJ5cWFQ9ZAVoG32qqTRCG0UrosOsOZB8JwsjAJU8RiSuD4gTxWpNwvvc9SRLrVOHcSMkMpnosLvxR3VZCZCEHBmTVKcrJAoRZB6hjrhZCeYxGQwiyoClx7Y0igevbEfcwfwltKkUgfzoCzscqHyaOq2Nwn26k';
        $mashsb_options['mashsb_sharemethod'] = 'mashengine';
        $mashsb_options['caching_method'] = 'refresh_loading';
        $mashsb_options['mashsharer_cache'] = 0;
        $mashsb_options['disable_cache'] = 'true';
        $mashsb_options['facebook_count_mode'] = 'total';
        // enable permalinks
        update_option( 'permalink_structure', '/%postname%/' );
        require_once MASHSB_PLUGIN_DIR . 'includes/libraries/RolingCurlX.php';
        require_once(MASHSB_PLUGIN_DIR . 'includes/mashengine.php');
    }

    /**
     * to understand tearDown, view Setup_Teardown_Example.php
     */
    function tearDown() {
        //parent::tearDown();
        global $wpdb, $wp_query, $post;
        $this->expectedDeprecated();
        $wpdb->query( 'ROLLBACK' );
        $wp_query = new WP_Query();
        $post = null;
        remove_filter( 'query', array($this, '_create_temporary_tables') );
        
        //delete transients
        delete_transient( 'mashsb_rate_limit' );
        delete_transient( 'mashsb_limit_req' );
        //sleep(0);
    }

    public function test_mashsb_is_cache_refresh() {
        global $mashsb_options;
        $mashsb_options['disable_cache'] = 'true';
        $this->assertTrue( mashsb_is_cache_refresh() );
    }

    public function test_mashsb_force_cache_refresh() {
        global $mashsb_options;
        $mashsb_options['disable_cache'] = 'true';
        $this->assertTrue( mashsb_force_cache_refresh() );
    }

    public function test_mashsb_is_enabled_permalinks() {
        $this->assertTrue( mashsb_is_enabled_permalinks() );
    }

//    public function test_is_singular() {
//        // Prepare test
//        $post_id = $this->factory->post->create(array(
//            'post_title' => 'Hello World',
//            'post_name' => 'hello-world',
//            'post_type' => 'post',
//            'post_status' => 'publish'
//                ));
//        $this->go_to(get_permalink($post_id));
//        //$post = get_post($post_id);
//        //$this->assertTrue(is_singular());
//        $this->assertQueryTrue( 'is_single', 'is_singular' );
//    }
    public function test_mashengine_FBTW() {
        $url = 'http://google.com';
        $mashsbSharesObj = new mashengine( $url );
        $shares = $mashsbSharesObj->getALLCounts()->total;
        $this->assertGreaterThan( 1000, ( int ) $shares );
    }

    public function test_mashengine_all_counts() {
        global $mashsb_options;
        $url = 'http://google.com';
        $mash = new mashengine( $url );
        $shares = $mash->getALLCounts()->total;
        $this->assertGreaterThan( 1000, ( int ) $shares );
    }

    public function test_getSharedcount() {
        //global $mashsb_options, $post;
        global $mashsb_options;

        $id = $this->factory->post->create( array('post_type' => 'post') );
        $this->go_to( get_permalink( $id ) );
        //$post = get_post( $id ); // We need the post object for testing

        $url = 'http://google.com';
        $url2 = 'https://google.com';
        $shares = getSharedcount( $url );
        $shares2 = getSharedcount( $url2 );

        $this->assertGreaterThan( 1000, is_singular() );
        $this->assertGreaterThan( 1000, $shares );
        $this->assertGreaterThan( 1000, $shares2 );
    }

    public function test_getSharedcountJson() {
        global $mashsb_options, $post;

        //$mashsb_options['fb_access_token'] = 'EAAHag2FMn2UBAAU6ceKGLZCN6PAJ5cWFQ9ZAVoG32qqTRCG0UrosOsOZB8JwsjAJU8RiSuD4gTxWpNwvvc9SRLrVOHcSMkMpnosLvxR3VZCZCEHBmTVKcrJAoRZB6hjrhZCeYxGQwiyoClx7Y0igevbEfcwfwltKkUgfzoCzscqHyaOq2Nwn26k';
        $mashsb_options['mashsb_sharemethod'] = 'mashengine';
        $mashsb_options['caching_method'] = 'refresh_loading';
        $mashsb_options['mashsharer_cache'] = 0;
        $mashsb_options['disable_cache'] = 'true';

        $id = $this->factory->post->create( array('post_type' => 'post') );
        $this->go_to( get_permalink( $id ) );
        $post = get_post( $id ); // We need the post object for testing
        // Delete previous shares
        //delete_post_meta($id, 'mashsb_jsonshares');


        $url = 'http://google.com';
        // Get the share Object
        $mashsbSharesObj = mashsbGetShareObj( $url );
        // Get the share count Method
        $mashsbShareCounts = mashsbGetShareMethod( $mashsbSharesObj );
        //$this->assertTrue($mashsbShareCounts);


        $encode_data = json_encode( $mashsbShareCounts );

        $decode_data = json_decode( $encode_data, true );
        $this->assertArrayHasKey( 'facebook_total', $decode_data );


        $facebook_shares = $mashsbShareCounts->facebook_total;
        $this->assertGreaterThan( 1000, $facebook_shares );
    }

    public function test_rate_limit() {
        $url = 'http://graph.facebook.com/?id=http://www.google.com';

        $curl_handle = curl_init();
        curl_setopt( $curl_handle, CURLOPT_URL, $url );
        curl_setopt( $curl_handle, CURLOPT_CONNECTTIMEOUT, 2 );
        curl_setopt( $curl_handle, CURLOPT_RETURNTRANSFER, 1 );
        $buffer = curl_exec( $curl_handle );
        curl_close( $curl_handle );
        if( empty( $buffer ) ) {
            $return = "Nothing returned from url.<p>";
        } else {
            $return = $buffer;
        }
        //$this->assertTrue( $return );
    }

    public function test_getSharedcount_async_cache() {
        global $mashsb_options, $post;

        //delete transients
        delete_transient( 'mashsb_rate_limit' );
        delete_transient( 'mashsb_limit_req' );
        $mashsb_options['fb_access_token'] = 'EAAHag2FMn2UBAAU6ceKGLZCN6PAJ5cWFQ9ZAVoG32qqTRCG0UrosOsOZB8JwsjAJU8RiSuD4gTxWpNwvvc9SRLrVOHcSMkMpnosLvxR3VZCZCEHBmTVKcrJAoRZB6hjrhZCeYxGQwiyoClx7Y0igevbEfcwfwltKkUgfzoCzscqHyaOq2Nwn26k';

        $mashsb_options['mashsb_sharemethod'] = 'mashengine';
        $mashsb_options['caching_method'] = 'async_cache';
        $mashsb_options['mashsharer_cache'] = 0;
        $mashsb_options['disable_cache'] = 'true';
        $args = array('post_type' => 'post');
        $id = $this->factory->post->create( $args );
        $this->go_to( get_permalink( $id ) );
        $post = get_post( $id ); // Wee need the post object for testing
        $url = 'http://google.com';
        $url2 = 'https://google.com';
        $shares = getSharedcount( $url );
        $shares2 = getSharedcount( $url2 );
        $this->assertGreaterThan( 1000, ( int ) $shares );
        $this->assertGreaterThan( 1000, ( int ) $shares2 );
    }

    public function test_is_active_on_page() {
        global $post, $mashsb_options;
        // Prepare test
        $post_id = $this->factory->post->create( array(
            'post_title' => 'Hello World',
            'post_name' => 'hello-world',
            'post_type' => 'page',
            'post_status' => 'publish'
        ) );
        $this->go_to( get_permalink( $post_id ) );
        //$id = $this->factory->post->create($args);
        $mashsb_options['post_types'] = array('page');
        $this->go_to( get_permalink( $post_id ) );
        $post = get_post( $post_id ); // Wee need the post object for testing
        $this->assertTrue( mashsbGetActiveStatus() );
    }

    public function test_is_active_on_post() {
        global $post, $mashsb_options;
        $mashsb_options['post_types'] = array('post');
        // Prepare test
        $post_id = $this->factory->post->create( array(
            'post_type' => 'post'
        ) );
        //$id = $this->factory->post->create($args);
        $this->go_to( get_permalink( $post_id ) );
        $post = get_post( $post_id ); // We need the post object for testing
        $this->assertTrue( mashsbGetActiveStatus() );
    }

    function test_is_active_on_frontpage() {
        global $mashsb_options;
        $mashsb_options['frontpage'] = 'true';
        $page_on_front = $this->factory->post->create( array(
            'post_type' => 'page',
        ) );
        $page_for_posts = $this->factory->post->create( array(
            'post_type' => 'page',
        ) );
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $page_on_front );
        update_option( 'page_for_posts', $page_for_posts );
        $this->go_to( '/' );
        $this->assertQueryTrue( 'is_front_page', 'is_page', 'is_singular' );
        $this->assertTrue( mashsbGetActiveStatus() );
        $this->go_to( get_permalink( $page_for_posts ) );
        $this->assertQueryTrue( 'is_home', 'is_posts_page' );
        update_option( 'show_on_front', 'posts' );
        delete_option( 'page_on_front' );
        delete_option( 'page_for_posts' );
    }

    public function test_is_active_on_content_shortcode() {
        global $post, $mashsb_options;
        $mashsb_options['$loadall'] = 'true';
        $args = array(
            'post_name' => 'test page',
            'post_title' => 'page title',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[mashshare]'
        );
        $id = $this->factory->post->create( $args );
        $this->go_to( get_permalink( $id ) );
        $post = get_post( $id ); // Wee need the post object for testing
        $this->assertTrue( mashsbGetActiveStatus() );
    }

    public function test_get_fakecount_for_english() {
        global $post;
        $args = array(
            'post_name' => 'test page',
            'post_title' => 'this is title',
            'post_status' => 'publish',
            'post_type' => 'page',
        );
        $id = $this->factory->post->create( $args );
        $post = get_post( $id ); // Wee need the post object for testing
        $this->assertEquals( 0.30, mashsb_get_fake_factor() );
    }

    public function test_get_fakecount_for_hebraic() {
        global $post;
        $args = array(
            'post_name' => 'test page',
            'post_title' => 'בריאות/כמה כוסות קפה מומלץ לשתות ביום',
            'post_status' => 'publish',
            'post_type' => 'page',
        );
        $id = $this->factory->post->create( $args );
        $post = get_post( $id ); // Wee need the post object for testing
        $this->assertEquals( 0.60, mashsb_get_fake_factor() );
    }

    public function test_mashsb_hide_shares() {
        global $mashsb_options;
        $mashsb_options['hide_sharecount'] = 60;
        $shares = '';
        $this->assertTrue( mashsb_hide_shares( $shares ) );
        $shares = 0;
        $this->assertTrue( mashsb_hide_shares( $shares ) );
        $shares = 0.1;
        $this->assertTrue( mashsb_hide_shares( $shares ) );
        $shares = 60;
        $this->assertFalse( mashsb_hide_shares( $shares ) ); // Must be false        
        $shares = 65;
        $this->assertFalse( mashsb_hide_shares( $shares ) ); // Must be false
    }

}
