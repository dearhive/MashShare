<?php
/**
 * Weclome Page Class
 *
 * @package     MASHSB
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;

/**
 * MASHSB_Welcome Class
 *
 * A general class for About and Credits page.
 *
 * @since 1.4
 */
class MASHSB_Welcome {

    /**
     * @var string The capability users should have to view the page
     */
    public $minimum_capability = 'manage_options';

    /**
     * Get things started
     *
     * @since 1.0.1
     */
    public function __construct() {
        add_action( 'admin_menu', array($this, 'add_admin_menus') );
        add_action( 'admin_head', array($this, 'admin_head'), 1000 );
        add_action( 'admin_init', array($this, 'welcome') );
    }

    /**
     * Register the Dashboard Pages which are later hidden but these pages
     * are used to render the Welcome and Credits pages.
     *
     * @access public
     * @since 1.4
     * @return void
     */
    public function add_admin_menus() {

        // Getting Started Page
        $mashsb_quickstart = add_submenu_page(
                'mashsb-settings', __( 'Quickstart', 'mashsb' ), __( 'Quickstart', 'mashsb' ), $this->minimum_capability, 'mashsb-getting-started', array($this, 'getting_started_screen')
        );
        // About Page
        add_submenu_page(
                'mashsb-settings', __( 'Welcome to MashShare', 'mashsb' ), __( 'Welcome to MashShare', 'mashsb' ), $this->minimum_capability, 'mashsb-about', array($this, 'about_screen')
        );

        // Changelog Page
        $mashsb_about = add_submenu_page(
                'mashsb-settings',__( 'MashShare Changelog', 'mashsb' ), __( 'MashShare Changelog', 'mashsb' ), $this->minimum_capability, 'mashsb-changelog', array($this, 'changelog_screen')
        );


        // Credits Page
        $mashsb_credits = add_submenu_page(
                'mashsb-settings',__( 'The people that build MashShare', 'mashsb' ), __( 'The people that build MashShare', 'mashsb' ), $this->minimum_capability, 'mashsb-credits', array($this, 'credits_screen')
        );
    }

    /**
     * Hide Individual Dashboard Pages
     *
     * @access public
     * @since 1.4
     * @return void
     */
    public function admin_head() {
        remove_submenu_page( 'mashsb-settings', 'mashsb-about' );
        remove_submenu_page( 'mashsb-settings', 'mashsb-changelog' );
        remove_submenu_page( 'mashsb-settings', 'mashsb-getting-started' );
        remove_submenu_page( 'mashsb-settings', 'mashsb-credits' );
        
        if ( !mashsb_is_admin_page() ){
            return false;
        }
        ?>
        
        <style type="text/css" media="screen">
        /*<![CDATA[*/

        .mashsb-about-wrap .mashsb-badge { float: right; border-radius: 4px; margin: 0 0 15px 15px; max-width: 100px; }
        .mashsb-about-wrap #mashsb-header { margin-bottom: 15px; }
        .mashsb-about-wrap #mashsb-header h1 { margin-bottom: 15px !important; }
        //.mashsb-about-wrap .about-text { margin: 0 0 15px; max-width: 670px; }
        .mashsb-about-wrap .about-text { margin: 0 0 15px; }
        .mashsb-about-wrap .mash-feature-section { margin-top: 20px; }
        .mashsb-about-wrap .mash-feature-section-content,
        .mashsb-about-wrap .mash-feature-section-media { width: 50%; box-sizing: border-box; }
        .mashsb-about-wrap .mash-feature-section-content { float: left; padding-right: 50px; }
        .mashsb-about-wrap .mash-feature-section-content h4 { margin: 0 0 1em; }
        .mashsb-about-wrap .mash-feature-section-media { float: right; text-align: right; margin-bottom: 20px; }
        .mashsb-about-wrap .mash-feature-section-media img { border: 1px solid #ddd; }
        .mashsb-about-wrap .mash-feature-section:not(.under-the-hood) .col { margin-top: 0; }
        /* responsive */
        @media all and ( max-width: 782px ) {
            .mashsb-about-wrap .mash-feature-section-content,
            .mashsb-about-wrap .mash-feature-section-media { float: none; padding-right: 0; width: 100%; text-align: left; }
            .mashsb-about-wrap .mash-feature-section-media img { float: none; margin: 0 0 20px; }
        }
        /*]]>*/
        </style>
        
        <?php
    }

    /**
     * Render Getting Started Screen
     *
     * @access public
     * @since 1.9
     * @return void
     */
    public function getting_started_screen() {
        global $mashsb_redirect;
        ?>
        <div class="wrap mashsb-about-wrap">
            <?php
            // load welcome message and content tabs
            $this->welcome_message();
            $this->tabs();
            ?>
            <?php if (isset($_GET['redirect'])) {?>
            <p class="about-description mashsb-notice" style="background-color:#00abed;color:white;padding:20px;margin-top:20px;border:3px solid white;"><?php _e( '<strong>Facebook</strong> and <strong>Twitter Share Buttons</strong> have been enabled on all your posts! <br>Use the instructions below to customize MashShare.<br><br>:</strong>MashShare uses new sharedcount.com integration to be GDPR compliant. 
Register for sharedcount.com at <a href="'.admin_url().'admin.php?page=mashsb-settings" style="color:white;">MashShare > Settings > Sharecount</a>', 'mashsb' ); ?></p>
            <?php } ?>
            <div class="changelog clear">
                <h1><?php _e( 'Create Your First Social Sharing Button', 'mashsb' ); ?></h1>
                <div class="mash-feature-section">
                    <div class="mash-feature-section-media">
                        <img style="display:none;" src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/social-networks-settings.png'; ?>" class="mashsb-welcome-screenshots"/>
                    </div>
                    <div class="mash-feature-section-content">
                        <h4>Step 1: Go to <a href="<?php echo admin_url( 'admin.php?page=mashsb-settings#mashsb_settingsservices_header' ) ?>" target="blank"><?php _e( 'Settings &rarr; Networks', 'mashsb' ); ?></a></h4>
                        <p><?php _e( 'The Social Network menu is your general access point for activating the desired share buttons and for customizing the share button label', 'mashsb' ); ?></p>
                        <h4>Step 2: Go to <a href="<?php echo admin_url( 'admin.php?page=mashsb-settings#mashsb_settingslocation_header' ) ?>" target="blank"><?php _e( 'Settings &rarr; Position', 'mashsb' ); ?></a></h4>
                        <p><?php _e( 'Select the location and exact position of the share buttons within your content', 'mashsb' ); ?></p>
                        <h4><?php _e('You are done! Easy, isn\'t it?', 'mashsb'); ?></h4>
                        <p></p>
                            
                    </div>
                </div>
            </div>

            <div class="changelog clear">
                <h1><?php _e( 'Create Most Shared Posts Widget', 'mashsb' ); ?></h1>
                <div class="mash-feature-section">
                    <div class="mash-feature-section-content">
                        <h4>Go to <a href="<?php echo admin_url( 'widgets.php' ) ?>" target="blank"><?php _e( 'Appearance &rarr; Widgets', 'mashsb' ); ?></a></h4>

                        <p><?php _e( 'Drag and drop the widget labeled "<i>MashShare - Most Shared Posts</i>" into the desired widget location and save it.', 'mashsb' ); ?></p>
                        <img style="display:none;" src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/most-shared-posts.png'; ?>"/>

                    </div>
                </div>
            </div>

            <div class="changelog clear">
                <h1><?php _e( 'Content Shortcodes', 'mashsb' ); ?></h1>
                <div class="mash-feature-section">
                    <div class="mash-feature-section-media">
                        <img style="display:none;" src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/shortcodes.png'; ?>"/>
                    </div>
                    <div class="mash-feature-section-content">
                        <p>
                            <?php _e( 'Add Share buttons manually with using shortcode <i style="font-weight:bold;">[mashshare]</i>.', 'mashsb' ); ?>
                        </p>
                        <?php _e( 'Paste the shortcode in content of your posts or pages with the post editor at the place you want the share buttons appear', 'mashsb' ); ?>
                        <p>
                            <?php echo sprintf(__( 'There are several parameters you can use for the shortcode. Get a  <a href="%s" target="blank">list of all available shortcode parameters</a>', 'mashsb'), 'http://docs.mashshare.net/article/67-shortcodes'); ?><br>
                        </p>
                    </div>
                </div>
            </div>
            <div class="changelog clear">
                <h1><?php _e( 'PHP Template Shortcode', 'mashsb' ); ?></h1>
                <div class="mash-feature-section">
                    <div class="mash-feature-section-media">
                    </div>
                    <div class="mash-feature-section-content">
                        <p>
                            <?php _e( 'Add MashShare directly into your theme template files with using the PHP code<br> <pre><i style="font-weight:bold;">&lt;?php do_shortcode(\'[mashshare]\'); ?&gt;</i></pre>', 'mashsb' ); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="changelog clear">
                <h1><?php _e( 'Need Help?', 'mashsb' ); ?></h1>
                <div class="mash-feature-section two-col">
                    <div>
                        <h4><?php _e( 'Great Support', 'mashsb' ); ?></h4>
                        <p><?php _e( 'We do our best to provide the best support we can. If you encounter a problem or have a question, simply <a href="https://www.mashshare.net/contact-developer/" target="blank">open a ticket</a>.', 'mashsb' ); ?></p>
                        <ul id="mash-social-admin-head">
                            <?php echo mashsb_share_buttons(); ?>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
        <?php
    }

    /**
     * Welcome message
     *
     * @access public
     * @since 2.5
     * @return void
     */
    public function welcome_message() {
        list( $display_version ) = explode( '-', MASHSB_VERSION );
        ?>
        <div id="mashsb-header">
            <!--<img class="mashsb-badge" src="<?php //echo  . 'assets/images/mashsb-logo.svg';  ?>" alt="<?php //_e( 'MashShare', 'mashsb' );  ?>" / >//-->
            <h1><?php printf( __( 'Welcome to MashShare %s', 'mashsb' ), $display_version ); ?></h1>
            <h1 class="about-text">
                <?php _e( 'Congrats for Choosing MashShare<br>MashShare has been Activated And is Ready to Increase Your Social Media Traffic!', 'mashsb' ); ?>
            </h1>
        </div>
        <?php
    }

    /**
     * Render About Screen
     *
     * @access public
     * @since 1.4
     * @return void
     */
    public function about_screen() {
        ?>
        <div class="wrap mashsb-about-wrap">
            <?php
            // load welcome message and content tabs
            $this->welcome_message();
            $this->tabs();
            ?>
            <div class="changelog">
                <div class="mash-feature-section">
                    <div class="mash-feature-section-content">
                        <!--
                        <h1><?php //_e( 'Use Facebook Connect to Skyrocket Share Count', 'mashsb' ); ?></h1>
                        <p><?php //_e( 'MashShare is the first Social Media plugin that uses the brandnew Facebook Connect Integration to bypass the regular facebook API limit which has been introduced recently. <p>It allows you up to 200 API calls per hour to the facebook server. This is more than enough for even huge traffic sites as MashShare is caching all share counts internally. <p>We are convinced that other social media plugins are going to copy our solution soon... and we will be proud of it;) <p> Your site becomes immediately better than the rest because you are the one whose website is running with full social sharing power. Other sites share count still stucks and are delayed and they do not know it;)', 'mashsb' ); ?></p>
                        <img src="<?php //echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/oauth.png'; ?>"/>
                        //-->
                        <p></p>
                        <h1><?php _e( 'A New Beautiful Sharing Widget', 'mashsb' ); ?></h1>
                        <p><?php _e( 'We have heard your wishes so the new widget contains the long requested post thumbnail and a beautiful css which gives your side bar sharing super power.', 'mashsb' ); ?></p>
                        <img src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/widget.png'; ?>"/>
                        <p></p>
                        <h1><?php _e( 'Better Customization Options', 'mashsb' ); ?></h1>
                        <p><?php _e( 'Select from 3 ready to use sizes to make sure that MashShare is looking great on your site. No matter if you prefer small, medium or large buttons.', 'mashsb' ); ?></p>
                        <img src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/different_sizes.gif'; ?>"/>
                        <p></p>
                        <h1><?php _e( 'Asyncronous Share Count Aggregation', 'mashsb' ); ?></h1>
                        <p><?php _e( 'With MashShare you get our biggest performance update. Use the new <i>Async Cache Refresh</i> method and your share counts will be aggregated only after page loading and never while page loads. This is a huge performance update.', 'mashsb' ); ?></p>
                        <img src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/async_cache_refresh.png'; ?>"/>
                        <p></p>
                        <h1><?php _e( 'Open Graph and Twitter Card Integration', 'mashsb' ); ?></h1>
                        <p><?php _e( 'Use open graph and twitter card to specify the content you like to share. If you are using Yoast, MashShare will use the Yoast open graph data instead and extend it with custom data to get the maximum out of your valuable content.', 'mashsb' ); ?></p>
                        <p></p>
                        
                        <img src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/social_sharing_settings.png'; ?>"/>
                        <p></p>
                        <h1><?php _e( 'Great Responsive Buttons', 'mashsb' ); ?></h1>
                        <p><?php _e( 'MashShare arrives you with excellent responsive support. So the buttons look great on mobile and desktop devices. If you want more customization options for mobile devices you can purchase the responsive Add-On', 'mashsb' ); ?></p>
                        <p></p>
                        <h1><?php _e( 'Share Count Dashboard', 'mashsb' ); ?></h1>
                        <p><?php _e( 'See the shares of your posts at a glance on the admin posts listing:', 'mashsb' ); ?></p>
                        <p></p>
                        <img alt="Share count dashboard" title="Share count dashboard" src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/dashboard.png'; ?>"/>
                        <p></p>
                        <h1><?php _e( 'A much cleaner user interface', 'mashsb' ); ?></h1>
                        <p><?php _e( 'We spent a lot of time to make useful first time settings and improved the user interface for an easier experience.', 'mashsb' ); ?></p>
                        <p></p>
                    </div>
                </div>
            </div>


            <div class="changelog">
                <h1><?php _e( 'Additional Updates', 'mashsb' ); ?></h1>
                <div class="mash-feature-section three-col">
                    <div class="col">
                        <h4><?php _e( 'Developer Friendly', 'mashsb' ); ?></h4>
                        <p><?php echo sprintf(__( 'Are you a theme developer and want to use MashShare as your build in share count aggregator? Read the <a href="%s" target="blank">developer instructions.</a>', 'mashsb' ), 'https://docs.mashshare.net/category/38-sample-functions'); ?></p>
                    </div>
                    <div class="col">
                        <h4><?php _e( 'Check Open Graph Settings', 'mashsb' ); ?></h4>
                        <p><?php _e( 'Use the <i>Validate Open Graph Data</i> button and check if the open graph data on your site is working as expected or conflicts with other open graph data.', 'mashsb' ); ?></p>
                    </div>
                    <div class="col">
                        <h4><?php _e( 'Use Yoast SEO Title', 'mashsb' ); ?></h4>
                        <p><?php _e( 'MashShare will use the YOAST SEO title if it is defined.', 'mashsb' ); ?></p>
                    </div>
                </div>
            </div>

            <div class="return-to-dashboard">
                <a href="<?php echo esc_url( admin_url( add_query_arg( array('page' => 'mashsb-settings&tab=visual#mashsb_settingslocation_header'), 'edit.php' ) ) ); ?>"><?php _e( 'Go to MashShare Settings', 'mashsb' ); ?></a> &middot;
                <a href="<?php echo esc_url( admin_url( add_query_arg( array('page' => 'mashsb-changelog'), 'admin.php' ) ) ); ?>"><?php _e( 'View the Full Changelog', 'mashsb' ); ?></a>
                <ul id="mash-social-admin-head">
                    <?php echo mashsb_share_buttons(); ?>
                </ul>
            </div>
            

        </div>
        <?php
    }

    /**
     * Navigation tabs
     *
     * @access public
     * @since 1.9
     * @return void
     */
    public function tabs() {
        $selected = isset( $_GET['page'] ) ? $_GET['page'] : 'mashsb-about';
        ?>
        <h1 class="nav-tab-wrapper">
            <a class="nav-tab <?php echo $selected == 'mashsb-about' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array('page' => 'mashsb-about'), 'admin.php' ) ) ); ?>">
                <?php _e( "What's New", 'mashsb' ); ?>
            </a>
            <a class="nav-tab <?php echo $selected == 'mashsb-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array('page' => 'mashsb-getting-started'), 'admin.php' ) ) ); ?>">
                <?php _e( 'Getting Started', 'mashsb' ); ?>
            </a>
            <a class="nav-tab <?php echo $selected == 'mashsb-credits' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array('page' => 'mashsb-credits'), 'admin.php' ) ) ); ?>">
                <?php _e( 'Credits', 'mashsb' ); ?>
            </a>
        </h1>
        <?php
    }

    /**
     * Render Credits Screen
     *
     * @access public
     * @since 3.0.0
     * @return void
     */
    public function credits_screen() {
        ?>
        <div class="wrap mashsb-about-wrap">
            <?php
            // load welcome message and content tabs
            $this->welcome_message();
            $this->tabs();
            ?>
            <h1 class="about-description"><?php _e( 'Mashshare is created by developers all over the world who aim to provide the #1 ecosystem for growing social media traffic through WordPress.', 'mashsb' ); ?></h1>

            <?php echo $this->contributors(); ?>
            <p class="small"><?php echo sprintf(__(' If you want to be credited here participate on the development and  make your pull request on <a href="%s" target="_blank">github</a>',' mashsb'), 'https://github.com/mashshare/Mashshare')?></p>
            <ul id="mash-social-admin-head">
                <?php echo mashsb_share_buttons(); ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Render Contributors List
     *
     * @since 3.0.0
     * @uses MASHSB_Welcome::get_contributors()
     * @return string $contributor_list HTML formatted list of all the contributors for MASHSB
     */
    public function contributors() {
        $contributors = $this->get_contributors();

        if( empty( $contributors ) )
            return '';

        $contributor_list = '<ul class="wp-people-group">';

        foreach ( $contributors as $contributor ) {
            $contributor_list .= '<li class="wp-person">';
            $contributor_list .= sprintf( '<a href="%s" style="margin-right:8px;" title="%s">', esc_url( 'https://github.com/' . $contributor->login ), esc_html( sprintf( __( 'View %s', 'mashsb' ), $contributor->login ) )
            );
            $contributor_list .= sprintf( '<img src="%s" width="64" height="64" class="gravatar" alt="%s" />', esc_url( $contributor->avatar_url ), esc_html( $contributor->login ) );
            $contributor_list .= '</a>';
            $contributor_list .= sprintf( '<a class="web" href="%s">%s</a>', esc_url( 'https://github.com/' . $contributor->login ), esc_html( $contributor->login ) );
            $contributor_list .= '</a>';
            $contributor_list .= '</li>';
        }

        $contributor_list .= '</ul>';

        return $contributor_list;
    }

    /**
     * Retreive list of contributors from GitHub.
     *
     * @access public
     * @since 3.0.0
     * @return array $contributors List of contributors
     */
    public function get_contributors() {
        $contributors = get_transient( 'mashsb_contributors' );

        if( false !== $contributors ){
            return $contributors;
        }

        $response = wp_remote_get( 'https://api.github.com/repos/mashshare/mashshare/contributors?per_page=999', array('sslverify' => false) );

        if( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ){
            return array();
        }

        $contributors = json_decode( wp_remote_retrieve_body( $response ) );

        if( !is_array( $contributors ) ){
            return array();
        }

        set_transient( 'mashsb_contributors', $contributors, 3600 );

        return $contributors;
    }

    /**
     * Parse the MASHSB readme.txt file
     *
     * @since 3.0.0
     * @return string $readme HTML formatted readme file
     */
    public function parse_readme() {
        $file = file_exists( MASHSB_PLUGIN_DIR . 'readme.txt' ) ? MASHSB_PLUGIN_DIR . 'readme.txt' : null;

        if( !$file ) {
            $readme = '<p>' . __( 'No valid changelog was found.', 'mashsb' ) . '</p>';
        } else {
            $readme = file_get_contents( $file );
            $readme = nl2br( esc_html( $readme ) );
            $readme = explode( '== Changelog ==', $readme );
            $readme = end( $readme );

            $readme = preg_replace( '/`(.*?)`/', '<code>\\1</code>', $readme );
            $readme = preg_replace( '/[\040]\*\*(.*?)\*\*/', ' <strong>\\1</strong>', $readme );
            $readme = preg_replace( '/[\040]\*(.*?)\*/', ' <em>\\1</em>', $readme );
            $readme = preg_replace( '/= (.*?) =/', '<h4>\\1</h4>', $readme );
            $readme = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="\\2">\\1</a>', $readme );
        }

        return $readme;
    }

    /**
     * Render Changelog Screen
     *
     * @access public
     * @since 2.0.3
     * @return void
     */
    public function changelog_screen() {
        ?>
        <div class="wrap mashsb-about-wrap">
            <?php
            // load welcome message and content tabs
            $this->welcome_message();
            $this->tabs();
            ?>
            <div class="changelog">
                <h4><?php _e( 'Full Changelog', 'mashsb' ); ?></h4>

                <div class="mash-feature-section">
                    <?php echo $this->parse_readme(); ?>
                </div>
            </div>

            <div class="return-to-dashboard">
                <a href="<?php echo esc_url( admin_url( add_query_arg( array('page' => 'mashsb-settings&tab=visual#mashsb_settingslocation_header'), 'edit.php' ) ) ); ?>"><?php _e( 'Go to MashShare Settings', 'mashsb' ); ?></a>
            </div>
        </div>
        <?php
    }

    /**
     * Sends user to the Settings page on first activation of MASHSB as well as each
     * time MASHSB is upgraded to a new version
     *
     * @access public
     * @since 1.0.1
     * @global $mashsb_options Array of all the MASHSB Options
     * @return void
     */
    public function welcome() {
        global $mashsb_options;

        // Bail if no activation redirect
        if( !get_transient( '_mashsb_activation_redirect' ) ){
            return;
        }
        
        // Delete the redirect transient
        delete_transient( '_mashsb_activation_redirect' );

        // Bail if activating from network, or bulk
        if( is_network_admin() || isset( $_GET['activate-multi'] ) )
            return;

        $upgrade = get_option( 'mashsb_version_upgraded_from' );

        if( !$upgrade ) { // First time install
            wp_safe_redirect( admin_url( 'admin.php?page=mashsb-getting-started&redirect=1' ) );
            exit;
        } else { // Update
            wp_safe_redirect( admin_url( 'admin.php?page=mashsb-about&redirect=1' ) );
            exit;
        }
    }

}

new MASHSB_Welcome();
