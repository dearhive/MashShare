<?php

class TemplateFunctions extends WP_UnitTestCase {

    /**
     * 
     */
    function setUp() {
        parent::setUp();
    }

    /**
     * to understand tearDown, view Setup_Teardown_Example.php
     */
    function tearDown() {
        parent::tearDown();
        //wp_delete_post( $this->post_id );
    }

//    public function test_sharedcount() {
//
//        $apikey = '95439f12a0d187fcb0e6872ab0dcac0f177f5c5d';
//        $url = 'http://google.com';
//        // include sharedcount.class.php id needed
//        mashsbGetShareObj($url);
//
//        $sharedcount = new mashsbSharedcount($url, 10, $apikey);
//        $shares = $sharedcount->get_sharedcount();
//        $this->assertGreaterThan(0, $shares);
//    }

    public function test_mashengine_FBTW() {
        $url = 'http://google.com';
        if ( !class_exists('RollingCurlX') )
            require_once MASHSB_PLUGIN_DIR . 'includes/libraries/RolingCurlX.php';
        if ( !class_exists('mashengine') )
            require_once(MASHSB_PLUGIN_DIR . 'includes/mashengine.php');
        $mashsbSharesObj = new mashengine($url);
        $shares = $mashsbSharesObj->getFBTWCounts()->total;
        $this->assertGreaterThan(0, $shares);
    }

    public function test_mashengine_all_counts() {
        global $post;
        $url = 'http://google.com';
        $mash = new mashengine($url);
        $shares = $mash->getALLCounts()->total;
        $this->assertGreaterThan(0, $shares);
    }
    
    public function test_getSharedcount(){
        global $mashsb_options, $post;
        //$mashsb_options['mashsharer_cache'] = 2;
        $mashsb_options['mashsb_sharemethod'] = 'mashengine';
        $mashsb_options['caching_method'] = 'refresh_loading';
        $mashsb_options['mashsharer_cache'] = 0;
        $mashsb_options['disable_cache'] = true;
        $args = array('post_type' => 'post');
        $id = $this->factory->post->create($args);
        //$post = get_post($id);
        $this->go_to(get_permalink($id));
        //add_option( 'permalink_structure' , '/%postname%/' );
        $url = 'http://google.com';
        $url2 = 'https://google.com';
        $shares = getSharedcount($url);      
        $shares2 = getSharedcount($url);      
        $this->assertGreaterThan(20000, $shares);
        $this->assertGreaterThan(20000, $shares2);
        //$this->assertGreaterThan(get_option( 'permalink_structure'), $shares);
        //$this->assertGreaterThan(mashsb_is_enabled_permalinks(), $shares);
    }
    public function test_getSharedcount_async_cache(){
        global $mashsb_options, $post;
        $mashsb_options['mashsb_sharemethod'] = 'mashengine';
        $mashsb_options['caching_method'] = 'async_cache';
        $mashsb_options['mashsharer_cache'] = 0;
        $args = array('post_type' => 'post');
        $id = $this->factory->post->create($args);
        $this->go_to(get_permalink($id));
        //$post = get_post($id);
        $url = 'http://google.com';
        $url2 = 'https://google.com';
        $shares = getSharedcount($url);      
        $shares2 = getSharedcount($url2);      
        $this->assertGreaterThan(20000, $shares);
        $this->assertGreaterThan(20000, $shares2);
    }

    public function test_is_active_on_page() {
        global $post, $mashsb_options;

        $args = array('post_type' => 'page');
        $id = $this->factory->post->create($args);
        $post = get_post($id);
        $mashsb_options['post_types'] = array('page');
        $this->go_to(get_permalink($id));
        $this->assertTrue(mashsbGetActiveStatus());
    }

    public function test_is_active_on_post() {
        global $post, $mashsb_options;

        $args = array('post_type' => 'post');
        $id = $this->factory->post->create($args);
        $post = get_post($id);
        $mashsb_options['post_types'] = array('post');
        $this->go_to(get_permalink($id));
        $this->assertTrue(mashsbGetActiveStatus());
    }

    public function test_is_active_on_frontpage() {
        global $post, $mashsb_options;

        // create page
        $args = array(
            'post_name' => 'test page',
            'post_title' => 'page title',
            'post_status' => 'publish',
            'post_type' => 'page',
                //'post_content' => '[mashshare]'
        );
        $id = $this->factory->post->create($args);
        $post = get_post($id);

        update_option('show_on_front', 'page');
        update_option('page_on_front', $id);

        $mashsb_options['frontpage'] = true;
        $this->go_to(home_url());

        $this->assertTrue(mashsbGetActiveStatus());
    }

    public function test_is_active_on_content_shortcode() {
        global $post, $mashsb_options;

        $args = array(
            'post_name' => 'test page',
            'post_title' => 'page title',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[mashshare]'
        );
        $id = $this->factory->post->create($args);
        $post = get_post($id);
        $this->go_to(get_permalink($id));

        $this->assertTrue(mashsbGetActiveStatus());
    }

    public function test_get_fakecount_for_english() {
        global $post;
        
        $args = array(
            'post_name' => 'test page',
            'post_title' => 'this is title',
            'post_status' => 'publish',
            'post_type' => 'page',
        );
        $id = $this->factory->post->create($args);
        $post = get_post($id);

        $this->assertEquals(0.30, mashsb_get_fake_factor() );

    }
    
        public function test_get_fakecount_for_hebraic() {
        global $post;
        
        $args = array(
            'post_name' => 'test page',
            'post_title' => 'בריאות/כמה כוסות קפה מומלץ לשתות ביום',
            'post_status' => 'publish',
            'post_type' => 'page',
        );
        $id = $this->factory->post->create($args);
        $post = get_post($id);
        
        $this->assertEquals(0.60, mashsb_get_fake_factor() );

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
