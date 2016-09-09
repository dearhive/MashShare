<?php

class TemplateFunctions extends WP_UnitTestCase {

    /**
     * 
     */
    function setUp() {
        parent::setUp();
        global $mashsb_options;
        
        $mashsb_options['disable_cache'] = 'true';
        $mashsb_options['fb_access_token'] = 'EAAHag2FMn2UBAJwIjdG2jDB39ZAr3lCriDwXgqhk2hDxk1O1iM5Vk2WvxIXop6ZAUikeKcpBXWUQk2jxG8FXag4cEzXJDBggkWIGuEq9ECc6HAyG1UQrgaDr6w8M0tsT6tlHwBpjVGACyQectEU3CdFAgbX32Q83qGZAgv4cbWH39eb3ejc';

        // enable permalinks
        update_option( 'permalink_structure', '/%postname%/');
        
        require_once MASHSB_PLUGIN_DIR . 'includes/libraries/RolingCurlX.php';
        require_once(MASHSB_PLUGIN_DIR . 'includes/mashengine.php');
    }

    /**
     * to understand tearDown, view Setup_Teardown_Example.php
     */
    function tearDown() {
        parent::tearDown();
    }
    
    
    
    public function test_mashsb_is_cache_refresh(){
        $this->assertTrue( mashsb_is_cache_refresh() );
    }
    
    public function test_mashsb_is_enabled_permalinks(){
        $this->assertTrue( mashsb_is_enabled_permalinks() );
    }

    public function test_mashengine_FBTW() {
        global $mashsb_options;

        $url = 'http://google.com';
        
        $mashsbSharesObj = new mashengine($url);
        $shares = $mashsbSharesObj->getFBTWCounts()->total;

        $this->assertGreaterThan(1000, $shares);
    }

    public function test_mashengine_all_counts() {
        global $mashsb_options, $post;
        $mashsb_options['disable_cache'] = 'true';
        $mashsb_options['fb_access_token'] = 'EAAHag2FMn2UBAJwIjdG2jDB39ZAr3lCriDwXgqhk2hDxk1O1iM5Vk2WvxIXop6ZAUikeKcpBXWUQk2jxG8FXag4cEzXJDBggkWIGuEq9ECc6HAyG1UQrgaDr6w8M0tsT6tlHwBpjVGACyQectEU3CdFAgbX32Q83qGZAgv4cbWH39eb3ejc';
        $url = 'http://google.com';
        
        $mash = new mashengine($url);
        $shares = $mash->getALLCounts()->total;
        
        $this->assertGreaterThan(1000, $shares);
    }
    
    public function test_getSharedcount(){
        global $mashsb_options, $post;
        $mashsb_options['fb_access_token'] = 'EAAHag2FMn2UBAJwIjdG2jDB39ZAr3lCriDwXgqhk2hDxk1O1iM5Vk2WvxIXop6ZAUikeKcpBXWUQk2jxG8FXag4cEzXJDBggkWIGuEq9ECc6HAyG1UQrgaDr6w8M0tsT6tlHwBpjVGACyQectEU3CdFAgbX32Q83qGZAgv4cbWH39eb3ejc';
        $mashsb_options['mashsb_sharemethod'] = 'mashengine';
        $mashsb_options['caching_method'] = 'refresh_loading';
        $mashsb_options['mashsharer_cache'] = 0;
        $mashsb_options['disable_cache'] = 'true';
        
        $args = array('post_type' => 'post');
        $id = $this->factory->post->create($args);
        $post = get_post($id); // Wee need the post object for testing
        $this->go_to(get_permalink($id));
        
        $url = 'http://google.com';
        $url2 = 'https://google.com';
        
        $shares = getSharedcount($url);      
        $shares2 = getSharedcount($url2);  
        
        $this->assertGreaterThan(1000, $shares);
        $this->assertGreaterThan(1000, $shares2);
    }
    public function test_getSharedcount_async_cache(){
        global $mashsb_options, $post;
        
        $mashsb_options['fb_access_token'] = 'EAAHag2FMn2UBAJwIjdG2jDB39ZAr3lCriDwXgqhk2hDxk1O1iM5Vk2WvxIXop6ZAUikeKcpBXWUQk2jxG8FXag4cEzXJDBggkWIGuEq9ECc6HAyG1UQrgaDr6w8M0tsT6tlHwBpjVGACyQectEU3CdFAgbX32Q83qGZAgv4cbWH39eb3ejc';
        $mashsb_options['mashsb_sharemethod'] = 'mashengine';
        $mashsb_options['caching_method'] = 'async_cache';
        $mashsb_options['mashsharer_cache'] = 0;
        $mashsb_options['disable_cache'] = 'true';
        
        $args = array('post_type' => 'post');
        $id = $this->factory->post->create($args);
        $post = get_post($id); // Wee need the post object for testing
        $this->go_to(get_permalink($id));

        $url = 'http://google.com';
        $url2 = 'https://google.com';
        
        $shares = getSharedcount($url);      
        $shares2 = getSharedcount($url2); 
        
        $this->assertGreaterThan(1000, $shares);
        $this->assertGreaterThan(1000, $shares2);
    }

    public function test_is_active_on_page() {
        global $post, $mashsb_options;

        $args = array('post_type' => 'page');
        $id = $this->factory->post->create($args);
        $post = get_post($id); // Wee need the post object for testing
        $mashsb_options['post_types'] = array('page');
        $this->go_to(get_permalink($id));
        $this->assertTrue(mashsbGetActiveStatus());
    }

    public function test_is_active_on_post() {
        global $post, $mashsb_options;

        $args = array('post_type' => 'post');
        $id = $this->factory->post->create($args);
        $post = get_post($id); // Wee need the post object for testing
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
        );
        $id = $this->factory->post->create($args);
        $post = get_post($id); // We need the post object for testing

        update_option('show_on_front', 'page');
        update_option('page_on_front', $id);

        $mashsb_options['frontpage'] = 'true';
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
        $post = get_post($id); // Wee need the post object for testing
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
        $post = get_post($id); // Wee need the post object for testing

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
        $post = get_post($id); // Wee need the post object for testing
        
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
