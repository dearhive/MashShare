<?php

class sharecount extends WP_UnitTestCase {

   function setUp() {
      parent::setUp();
      global $mashsb_options;
      
      $_POST = array();
      
      $mashsb_options['disable_cache'] = 'true';
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

   function tearDown() {
      global $wpdb, $wp_query, $post;
      $this->expectedDeprecated();
      $wpdb->query( 'ROLLBACK' );
      $wp_query = new WP_Query();
      $post = null;
      remove_filter( 'query', array($this, '_create_temporary_tables') );

      //delete transients
      delete_transient( 'mashsb_rate_limit' );
      delete_transient( 'mashsb_limit_req' );
   }

   public function test_ajax_facebook_counts() {
      $id = $this->factory->post->create( array('post_type' => 'page') );
      $this->go_to( get_permalink( $id ) );
      
      $_POST['postid'] = get_post( $id ); 
      
      $data = array(
              'comment_count' => 1000,
              'share_count' => 1000 
          );
      $_POST['shares'] = $data;


      /**
       * Test Original function mashsb_set_fb_sharecount()
       */
      #################################################################

      $postId = isset( $_POST['postid'] ) ? $_POST['postid'] : false;

      // Ajax result
      $result = isset( $_POST['shares'] ) ? $_POST['shares'] : false;
      $comment_count = isset( $result['comment_count'] ) ? $result['comment_count'] : 0;
      $share_count = isset( $result['share_count'] ) ? $result['share_count'] : 0;

      if( !$postId || empty( $postId ) ) {
         wp_die( 'no post id' );
      }

      // Cache results
      $cacheJsonShares = mashsb_get_jsonshares_post_meta( $postId );
      $cacheTotalShares = mashsb_get_total_shares_post_meta( $postId );

      // New shares
      $cacheJsonShares['facebook_total'] = $share_count + $comment_count;
      $cacheJsonShares['facebook_likes'] = $share_count;
      $cacheJsonShares['facebook_comments'] = $comment_count;

      // Update shares but only if new shares are larger than cached values
      // Performance reasons AND to ensure that shares does not get lost if page permalink is changed
      update_post_meta( $postId, 'mashsb_jsonshares', json_encode( $cacheJsonShares ) );

      $newTotalShares = mashsb_get_total_shares( $postId );
      if( is_numeric($newTotalShares) && $newTotalShares > $cacheTotalShares ) {
         update_post_meta( $postId, 'mashsb_shares', $newTotalShares );
      }

      ################################################
      
      /**
       * Start the test
       */


      $this->assertGreaterThan( 2, $cacheJsonShares['facebook_total'] );
      $this->assertGreaterThan( 2, $cacheJsonShares['facebook_likes'] );
      $this->assertGreaterThan( 2, $cacheJsonShares['facebook_comments'] );
   }

   public function test_sharecounts() {
      sleep( 2 );

      $url = 'http://google.com';
      $mash = mashsbGetShareObj( $url );
      $shares = $mash->getALLCounts();
      //$this->assertGreaterThan( 1000, $shares->facebook_shares, ' count is ' . $shares->facebook_shares );
      $this->assertGreaterThan( 2, $shares->facebook_total, ' count is ' . $shares->facebook_total );
      $this->assertGreaterThan( 2, $shares->pinterest, ' count is ' . $shares->pinterest );
      $this->assertGreaterThan( 2, $shares->twitter, ' count is ' . $shares->twitter );
      //$this->assertGreaterThan( 2, $shares->linkedin, ' count is ' . $shares->linkedin );
      $this->assertGreaterThan( 2, $shares->buffer, ' count is ' . $shares->buffer );
   }

}
