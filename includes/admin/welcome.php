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
        add_action( 'admin_menu', array($this, 'admin_menus') );
        add_action( 'admin_head', array($this, 'admin_head') );
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
    public function admin_menus() {
        // About Page
        add_dashboard_page(
                __( 'Welcome to MashShare', 'mashsb' ), __( 'Welcome to MashShare', 'mashsb' ), $this->minimum_capability, 'mashsb-about', array($this, 'about_screen')
        );

        // Changelog Page
        $mashsb_about = add_dashboard_page(
                __( 'MashShare Changelog', 'mashsb' ), __( 'MashShare Changelog', 'mashsb' ), $this->minimum_capability, 'mashsb-changelog', array($this, 'changelog_screen')
        );

        // Getting Started Page
        $mashsb_quickstart = add_submenu_page(
                'mashsb-settings', __( 'Quickstart', 'mashsb' ), __( 'Quickstart', 'mashsb' ), $this->minimum_capability, 'mashsb-getting-started', array($this, 'getting_started_screen')
        );

        // Credits Page
        $mashsb_credits = add_dashboard_page(
                __( 'The people that build MashShare', 'mashsb' ), __( 'The people that build MashShare', 'mashsb' ), $this->minimum_capability, 'mashsb-credits', array($this, 'credits_screen')
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
        remove_submenu_page( 'index.php', 'mashsb-about' );
        remove_submenu_page( 'index.php', 'mashsb-changelog' );
        remove_submenu_page( 'index.php', 'mashsb-getting-started' );
        remove_submenu_page( 'index.php', 'mashsb-credits' );
        ?>
        
        <style type="text/css" media="screen">
        /*<![CDATA[*/

        .mashsb-about-wrap .mashsb-badge { float: right; border-radius: 4px; margin: 0 0 15px 15px; max-width: 100px; }
        .mashsb-about-wrap #mashsb-header { margin-bottom: 15px; }
        .mashsb-about-wrap #mashsb-header h1 { margin-bottom: 15px !important; }
        .mashsb-about-wrap .about-text { margin: 0 0 15px; max-width: 670px; }
        .mashsb-about-wrap .feature-section { margin-top: 20px; }
        .mashsb-about-wrap .feature-section-content,
        .mashsb-about-wrap .feature-section-media { width: 50%; box-sizing: border-box; }
        .mashsb-about-wrap .feature-section-content { float: left; padding-right: 50px; }
        .mashsb-about-wrap .feature-section-content h4 { margin: 0 0 1em; }
        .mashsb-about-wrap .feature-section-media { float: right; text-align: right; margin-bottom: 20px; }
        .mashsb-about-wrap .feature-section-media img { border: 1px solid #ddd; }
        .mashsb-about-wrap .feature-section:not(.under-the-hood) .col { margin-top: 0; }
        /* responsive */
        @media all and ( max-width: 782px ) {
            .mashsb-about-wrap .feature-section-content,
            .mashsb-about-wrap .feature-section-media { float: none; padding-right: 0; width: 100%; text-align: left; }
            .mashsb-about-wrap .feature-section-media img { float: none; margin: 0 0 20px; }
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
        <div class="wrap about-wrap mashsb-about-wrap">
            <?php
            // load welcome message and content tabs
            $this->welcome_message();
            $this->tabs();
            ?>
            <?php if (isset($_GET['redirect'])) {?>
            <p class="about-description mashsb-notice notice-success"><?php _e( 'Facebook and Twitter Share Buttons are successfully enabled on all your posts! <br> Use the steps  below to customize MashShare.', 'mashsb' ); ?></p>
            <?php } ?>
            <div class="changelog">
                <h3><?php _e( 'Step 1: Creating Your First Social Sharing Button', 'mashsb' ); ?></h3>
                <div class="feature-section">
                    <div class="feature-section-media">
                        <img src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/social-networks-settings.png'; ?>" class="mashsb-welcome-screenshots"/>
                    </div>
                    <div class="feature-section-content">
                        <h4>Go to <a href="<?php echo admin_url( 'admin.php?page=mashsb-settings#mashsb_settingsservices_header' ) ?>" target="blank"><?php _e( 'Settings &rarr; Social Networks', 'mashsb' ); ?></a></h4>
                        <p><?php _e( 'The Social Network menu is your general access point for activating the desired share buttons and for customizing the share button label', 'mashsb' ); ?></p>
                        <h3><?php _e( 'Step 2: Set Share Button Location & Position', 'mashsb' ); ?></h3>
                        <h4>Go to <a href="<?php echo admin_url( 'admin.php?page=mashsb-settings#mashsb_settingslocation_header' ) ?>" target="blank"><?php _e( 'Settings &rarr; Location & Position', 'mashsb' ); ?></a></h4>
                        <p><?php _e( 'Specify the location and exact position of the share buttons within your content', 'mashsb' ); ?></p>
                        <h3><?php _e('You are done! Easy, isn\'t it?', 'mashsb'); ?></h3>
                        <p></p>
                            
                    </div>
                </div>
            </div>

            <div class="changelog">
                <h3><?php _e( 'Display a Most Shared Post Widget', 'mashsb' ); ?></h3>
                <div class="feature-section">
                    <div class="feature-section-media">
                        <img src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/most-shared-posts.png'; ?>"/>
                    </div>
                    <div class="feature-section-content">
                        <h4><a href="<?php echo admin_url( 'widgets.php' ) ?>" target="blank"><?php _e( 'Appearance &rarr; Widgets', 'mashsb' ); ?></a></h4>

                        <p><?php _e( 'Drag and drop the widget </br> "<i>MashShare - Most Shared Posts</i>" </br>into the desired widget location and save it', 'mashsb' ); ?></p>

                    </div>
                </div>
            </div>

            <div class="changelog">
                <h3><?php _e( 'Content Shortcodes', 'mashsb' ); ?></h3>
                <div class="feature-section">
                    <div class="feature-section-media">
                        <img src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/shortcodes.png'; ?>"/>
                    </div>
                    <div class="feature-section-content">
                        <p><?php _e( 'Add Share buttons manually with using the shortcode <i>[mashshare]</i> in content of your posts or pages.', 'mashsb' ); ?>
                        </p>
                            <?php echo sprintf(__( 'Find a list of all available shortcode parameters <a href="%s" target="blank">here</a>', 'mashsb'), 'https://www.mashshare.net/documentation/shortcodes/'); ?><br>
                        </p>
                    </div>
                </div>
            </div>

            <div class="changelog">
                <h2><?php _e( 'Need Help?', 'mashsb' ); ?></h2>
                <div class="feature-section two-col">
                    <div>
                        <h3><?php _e( 'Great Support', 'mashsb' ); ?></h3>
                        <p><?php _e( 'We do our best to provide the best support we can. If you encounter a problem or have a question, simply open a ticket using our <a href="https://www.mashshare.net/contact-developer/" target="blank">support form</a>.', 'mashsb' ); ?></p>
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
            <p class="about-text">
                <?php _e( 'Thank you for updating to the latest version! MashShare is installed and ready to grow your traffic from social networks!', 'mashsb' ); ?>
            </p>
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
        <div class="wrap about-wrap mashsb-about-wrap">
            <?php
            // load welcome message and content tabs
            $this->welcome_message();
            $this->tabs();
            ?>
            <div class="changelog">
                <h3><?php _e( 'Amazon Payments', 'mashsb' ); ?></h3>
                <div class="feature-section">
                    <div class="feature-section-media">
                        <img src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/24-checkout.png'; ?>"/>
                    </div>
                    <div class="feature-section-content">
                        <p><?php _e( 'With MashShare version 2.4, you can now accept payments through Amazon\'s Login and Pay with the new built-in payment gateway.', 'mashsb' ); ?></p>

                        <h4><?php _e( 'Secure Checkout', 'mashsb' ); ?></h4>
                        <p><?php _e( 'When using Amazon Payments, credit / debit card details are entered on Amazon\'s secure servers and never pass through your own server, making the entire process dramatically more secure and reliable.', 'mashsb' ); ?></p>

                        <h4><?php _e( 'Accept Credit and Debit Card Payments', 'mashsb' ); ?></h4>
                        <p><?php _e( 'Amazon Payments allows your customers to easily pay with their debit or credit cards. During checkout, customers will be provided an option to use a stored card or enter a new one.', 'mashsb' ); ?></p>

                        <h4><?php _e( 'Simple Customer Authentication', 'mashsb' ); ?></h4>
                        <p><?php _e( 'Customers can log into their Amazon account from your checkout screen and have all of their billing details retrieved automatically from Amazon. With just a few clicks, customers can effortlessly complete their purchase.', 'mashsb' ); ?></p>
                    </div>
                </div>
            </div>

            <div class="changelog">
                <h3><?php _e( 'Earnings / Sales By Category', 'mashsb' ); ?></h3>
                <div class="feature-section">
                    <div class="feature-section-media">
                        <img src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/24-category-earnings.png'; ?>"/>
                    </div>
                    <div class="feature-section-content">
                        <p><?php _e( 'MashShare version 2.4 introduces a new Report that displays earnings and sales for your product categories.', 'mashsb' ); ?></p>

                        <h4><?php _e( 'Earnings and Sales Overview', 'mashsb' ); ?></h4>
                        <p><?php _e( 'Quickly see how each of your categories has performed over the lifetime of your store. The total sales and earnings are displayed, as well as the average monthly sales and earnings for each category.', 'mashsb' ); ?></p>

                        <h4><?php _e( 'Category Sales / Earnings Mix', 'mashsb' ); ?></h4>
                        <p><?php _e( 'The report includes a visual break down of the sales / earnings mix for your categories. Quickly see which categories account for the highest (or lowest) percentage of your sales and earnings.', 'mashsb' ); ?></p>
                    </div>
                </div>
            </div>

            <div class="changelog">
                <h3><?php _e( 'Improved Data Export', 'mashsb' ); ?></h3>
                <div class="feature-section">
                    <div class="feature-section-media">
                        <img src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/screenshots/24-export.png'; ?>" class="mashsb-welcome-screenshots"/>
                    </div>
                    <div class="feature-section-content">
                        <h4><?php _e( 'Big Data Support', 'mashsb' ); ?></h4>
                        <p><?php _e( 'With the new export processing in MashShare 2.4, you can easily export massive amounts of data. Need to export 20,000 payment records? No problem.', 'mashsb' ); ?></p>

                        <h4><?php _e( 'Standardized Customer Export', 'mashsb' ); ?></h4>
                        <p><?php _e( 'The Customer export has been standardized so it now produces the same data during export for all export options. It can also easily handle 20,000 or even 50,000 customer records in a single export.', 'mashsb' ); ?></p>
                    </div>
                </div>
            </div>

            <div class="changelog">
                <h3><?php _e( 'Additional Updates', 'mashsb' ); ?></h3>
                <div class="feature-section three-col">
                    <div class="col">
                        <h4><?php _e( 'REST API Versioning', 'mashsb' ); ?></h4>
                        <p><?php _e( 'The REST API now supports a version parameter that allows you to specify which version of the API you wish to use.', 'mashsb' ); ?></p>
                    </div>
                    <div class="col">
                        <h4><?php _e( 'Better Cart Tax Display', 'mashsb' ); ?></h4>
                        <p><?php _e( 'Cart widgets now display estimated taxes for customers before reaching the checkout page.', 'mashsb' ); ?></p>
                    </div>
                    <div class="col">
                        <h4><?php _e( 'Customer > User Synchronization', 'mashsb' ); ?></h4>
                        <p><?php _e( 'Customer email addresses are now updated when the associated user account\'s email is changed.', 'mashsb' ); ?></p>
                    </div>
                    <div class="clear">
                        <div class="col">
                            <h4><?php _e( 'Better Test Mode Settings', 'mashsb' ); ?></h4>
                            <p><?php _e( 'Test Mode has been improved by moving the option to the Payment Gateways screen. Sales / earnings stats are now incremented in test mode.', 'mashsb' ); ?></p>
                        </div>
                        <div class="col">
                            <h4><?php _e( 'Exclude Taxes from Reports', 'mashsb' ); ?></h4>
                            <p><?php _e( 'Earnings and sales reports can now be shown exclusive of tax, allowing you to easily see how your store is performing after taxes.', 'mashsb' ); ?></p>
                        </div>
                        <div class="col">
                            <h4><?php _e( 'Default Gateway First', 'mashsb' ); ?></h4>
                            <p><?php _e( 'The gateway selected as the default option will always be displayed first on checkout.', 'mashsb' ); ?></p>
                        </div>
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
     * @since 2.5.6
     * @return void
     */
    public function credits_screen() {
        ?>
        <div class="wrap about-wrap mashsb-about-wrap">
            <?php
            // load welcome message and content tabs
            $this->welcome_message();
            $this->tabs();
            ?>
            <p class="about-description"><?php _e( 'Mashshare is created by a René Hermenau and developers all over the world who aim to provide the #1 ecosystem for growing social media traffic through WordPress.', 'mashsb' ); ?></p>

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
     * @since 2.5.6
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
            $contributor_list .= sprintf( '<a href="%s" title="%s">', esc_url( 'https://github.com/' . $contributor->login ), esc_html( sprintf( __( 'View %s', 'mashsb' ), $contributor->login ) )
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
     * @since 2.5.6
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
     * @since 2.5.6
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
        <div class="wrap about-wrap mashsb-about-wrap">
            <?php
            // load welcome message and content tabs
            $this->welcome_message();
            $this->tabs();
            ?>
            <div class="changelog">
                <h3><?php _e( 'Full Changelog', 'mashsb' ); ?></h3>

                <div class="feature-section">
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
        if( !get_transient( '_mashsb_activation_redirect' ) )
            return;
        
        // Delete the redirect transient
        delete_transient( '_mashsb_activation_redirect' );

        // Bail if activating from network, or bulk
        if( is_network_admin() || isset( $_GET['activate-multi'] ) )
            return;

        $upgrade = get_option( 'mashsb_version_upgraded_from' );
        //@since 2.0.3
        if( !$upgrade ) { // First time install
            wp_safe_redirect( admin_url( 'admin.php?page=mashsb-getting-started&redirect=1' ) );
            exit;
        } else { // Update
            wp_safe_redirect( admin_url( 'admin.php?page=mashsb-getting-started&redirect=1' ) );
            //wp_safe_redirect( admin_url( 'options-general.php?page=mashsb-settings&tab=visual#mashsb_settingslocation_header' ) );
            exit;
        }
    }

}

new MASHSB_Welcome();
