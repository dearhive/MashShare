<?php

namespace Mashshare;

class cache {

   private $options;

   public function __construct() {
      $this->options = get_option( 'mashsb_settings' );
      add_action( 'init', 'force_cache_refresh' );
   }

   /**
    * Force cache refresh via GET REQUEST
    * 
    * @global array $this->options
    * @return boolean true for cache refresh
    */
   public function force_cache_refresh() {

      // Needed for testing (phpunit)
      if( MASHSB_DEBUG || isset( $this->options['disable_cache'] ) ) {
         mashsb()->logger->info( 'mashsb_force_cache_refresh() -> Debug mode enabled' );
         return true;
      }

      $caching_method = !empty( $this->options['caching_method'] ) ? $this->options['caching_method'] : 'refresh_loading';

      // Old method and less performant - Cache is rebuild during pageload
      if( $caching_method == 'refresh_loading' ) {
         if( $this->is_cache_refresh() ) {
            return true;
         }
      }

      // New method - Cache will be rebuild after complete pageloading and will be initiated via ajax.
      if( isset( $_GET['mashsb-refresh'] ) && $caching_method == 'async_cache' ) {
         MASHSB()->logger->info( 'Force Cache Refresh' );
         return true;
      }
   }

   /**
    * Check if cache is expired and page must be refreshed
    * 
    * @global array $post
    * @return boolean 
    */
   public function is_cache_refresh() {
      global $post;


      // Debug mode or cache activated
      if( MASHSB_DEBUG || isset( $this->options['disable_cache'] ) ) {
         MASHSB()->logger->info( 'mashsb_is_cache_refresh: MASHSB_DEBUG - refresh Cache' );
         return true;
      }

      // if it's a crawl deactivate cache
      if( isset( $_SERVER['HTTP_USER_AGENT'] ) && preg_match( '/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'] ) ) {
         return false;
      }

      /*
       * Deactivate share count on:
       * - 404 pages
       * - search page
       * - empty url
       * - disabled permalinks
       * - admin pages
       * 
        Exit here to save cpu time
       */
//$test = is_search();
//$test2 = is_404();
//$test3 = is_admin();
//$test4 = mashsb_is_enabled_permalinks();

      if( is_404() || is_search() || is_admin() || !mashsb_is_enabled_permalinks() ) {
         return false;
      }

      // New cache on singular pages
      // 
      // Refreshing cache on blog posts like categories will lead 
      // to high load and multiple API requests so we only check 
      // the main url on these other pages
      if( is_singular() && isset( $post->ID ) ) {
         // last updated timestamp 
         $last_updated = get_post_meta( $post->ID, 'mashsb_timestamp', true );
         if( !empty( $last_updated ) ) {
            MASHSB()->logger->info( 'mashsb_is_cache_refresh - is_singular() url: ' . get_permalink( $post->ID ) . ' : last updated:' . date( 'Y-m-d H:i:s', $last_updated ) );
         }
      } else if( mashsb_get_main_url() ) {

         // Get transient timeout and calculate last update time
         $url = mashsb_get_main_url();
         $transient = '_transient_timeout_mashcount_' . md5( $url );
         $last_updated = get_option( $transient ) - mashsb_get_expiration();
         if( !empty( $last_updated ) ) {
            MASHSB()->logger->info( 'mashsb_is_cache_refresh() mashsb_get_main_url() url: ' . $url . ' last updated:' . date( 'Y-m-d H:i:s', $last_updated ) );
         }
      } else {
         // No valid URL so do not refresh cache
         MASHSB()->logger->info( 'mashsb_is_cache_refresh: No valid URL - do not refresh cache' );
         return false;
      }

      // No timestamp so let's create cache for the first time
      if( empty( $last_updated ) ) {
         MASHSB()->logger->info( 'mashsb_is_cache_refresh: No Timestamp. Refresh Cache' );
         return true;
      }

      // The caching expiration
      $expiration = mashsb_get_expiration();
      $next_update = $last_updated + $expiration;
      MASHSB()->logger->info( 'mashsb_is_cache_refresh. Next update ' . date( 'Y-m-d H:i:s', $next_update ) . ' current time: ' . date( 'Y-m-d H:i:s', time() ) );

      // Refresh Cache when last update plus expiration is older than current time
      if( ($last_updated + $expiration) <= time() ) {
         MASHSB()->logger->info( 'mashsb_is_cache_refresh: Refresh Cache!' );
         return true;
      }
   }

}
