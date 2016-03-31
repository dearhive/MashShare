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
        add_dashboard_page(
                __( 'MashShare Changelog', 'mashsb' ), __( 'MashShare Changelog', 'mashsb' ), $this->minimum_capability, 'mashsb-changelog', array($this, 'changelog_screen')
        );

        // Getting Started Page
        add_submenu_page(
                'mashsb-settings', __( 'Quickstart', 'mashsb' ), __( 'Quickstart', 'mashsb' ), $this->minimum_capability, 'mashsb-getting-started', array($this, 'getting_started_screen')
        );

        // Credits Page
        add_dashboard_page(
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
        ?>
        <div class="wrap about-wrap mashsb-about-wrap">
            <?php
            // load welcome message and content tabs
            $this->welcome_message();
            $this->tabs();
            ?>
            <p class="about-description"><?php _e( 'Use the tips below to get started using MashShare. You will be up and running in no time!', 'mashsb' ); ?></p>

            <div class="changelog">
                <h3><?php _e( 'Creating Your First Download Product', 'mashsb' ); ?></h3>
                <div class="feature-section">
                    <div class="feature-section-media">
                        <img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/edit-download.png'; ?>" class="mashsb-welcome-screenshots"/>
                    </div>
                    <div class="feature-section-content">
                        <h4><a href="<?php echo admin_url( 'post-new.php?post_type=download' ) ?>"><?php printf( __( '%s &rarr; Add New', 'mashsb' ), edd_get_label_plural() ); ?></a></h4>
                        <p><?php printf( __( 'The %s menu is your access point for all aspects of your MashShare product creation and setup. To create your first product, simply click Add New and then fill out the product details.', 'mashsb' ), edd_get_label_plural() ); ?></p>


                        <h4><?php _e( 'Download Files', 'mashsb' ); ?></h4>
                        <p><?php _e( 'Uploading the downloadable files is simple. Click <em>Upload File</em> in the Download Files section and choose your download file. To add more than one file, simply click the <em>Add New</em> button.', 'mashsb' ); ?></p>
                    </div>
                </div>
            </div>

            <div class="changelog">
                <h3><?php _e( 'Display a Product Grid', 'mashsb' ); ?></h3>
                <div class="feature-section">
                    <div class="feature-section-media">
                        <img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/grid.png'; ?>"/>
                    </div>
                    <div class="feature-section-content">
                        <h4><?php _e( 'Flexible Product Grids', 'mashsb' ); ?></h4>
                        <p><?php _e( 'The [downloads] shortcode will display a product grid that works with any theme, no matter the size. It is even responsive!', 'mashsb' ); ?></p>

                        <h4><?php _e( 'Change the Number of Columns', 'mashsb' ); ?></h4>
                        <p><?php _e( 'You can easily change the number of columns by adding the columns="x" parameter:', 'mashsb' ); ?></p>
                        <p><pre>[downloads columns="4"]</pre></p>

                        <h4><?php _e( 'Additional Display Options', 'mashsb' ); ?></h4>
                        <p><?php printf( __( 'The product grids can be customized in any way you wish and there is <a href="%s">extensive documentation</a> to assist you.', 'mashsb' ), 'http://docs.easydigitaldownloads.com/' ); ?></p>
                    </div>
                </div>
            </div>

            <div class="changelog">
                <h3><?php _e( 'Purchase Buttons Anywhere', 'mashsb' ); ?></h3>
                <div class="feature-section">
                    <div class="feature-section-media">
                        <img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/purchase-link.png'; ?>"/>
                    </div>
                    <div class="feature-section-content">
                        <h4><?php _e( 'The <em>[purchase_link]</em> Shortcode', 'mashsb' ); ?></h4>
                        <p><?php _e( 'With easily accessible shortcodes to display purchase buttons, you can add a Buy Now or Add to Cart button for any product anywhere on your site in seconds.', 'mashsb' ); ?></p>

                        <h4><?php _e( 'Buy Now Buttons', 'mashsb' ); ?></h4>
                        <p><?php _e( 'Purchase buttons can behave as either Add to Cart or Buy Now buttons. With Buy Now buttons customers are taken straight to PayPal, giving them the most frictionless purchasing experience possible.', 'mashsb' ); ?></p>
                    </div>
                </div>
            </div>

            <div class="changelog">
                <h3><?php _e( 'Need Help?', 'mashsb' ); ?></h3>
                <div class="feature-section two-col">
                    <div class="col">
                        <h4><?php _e( 'Phenomenal Support', 'mashsb' ); ?></h4>
                        <p><?php _e( 'We do our best to provide the best support we can. If you encounter a problem or have a question, simply open a ticket using our <a href="https://easydigitaldownloads.com/support">support form</a>.', 'mashsb' ); ?></p>
                    </div>
                    <div class="col">
                        <h4><?php _e( 'Need Even Faster Support?', 'mashsb' ); ?></h4>
                        <p><?php _e( 'Our <a href="https://easydigitaldownloads.com/support/pricing/">Priority Support</a> system is there for customers that need faster and/or more in-depth assistance.', 'mashsb' ); ?></p>
                    </div>
                </div>
            </div>

            <div class="changelog">
                <h3><?php _e( 'Stay Up to Date', 'mashsb' ); ?></h3>
                <div class="feature-section two-col">
                    <div class="col">
                        <h4><?php _e( 'Get Notified of Extension Releases', 'mashsb' ); ?></h4>
                        <p><?php _e( 'New extensions that make MashShare even more powerful are released nearly every single week. Subscribe to the newsletter to stay up to date with our latest releases. <a href="http://eepurl.com/kaerz" target="_blank">Sign up now</a> to ensure you do not miss a release!', 'mashsb' ); ?></p>
                    </div>
                    <div class="col">
                        <h4><?php _e( 'Get Alerted About New Tutorials', 'mashsb' ); ?></h4>
                        <p><?php _e( '<a href="http://eepurl.com/kaerz" target="_blank">Sign up now</a> to hear about the latest tutorial releases that explain how to take MashShare further.', 'mashsb' ); ?></p>
                    </div>
                </div>
            </div>

            <div class="changelog">
                <h3><?php _e( 'Extensions for Everything', 'mashsb' ); ?></h3>
                <div class="feature-section two-col">
                    <div class="col">
                        <h4><?php _e( 'Over 250 Extensions', 'mashsb' ); ?></h4>
                        <p><?php _e( 'Add-on plugins are available that greatly extend the default functionality of MashShare. There are extensions for payment processors, such as Stripe and PayPal, extensions for newsletter integrations, and many, many more.', 'mashsb' ); ?></p>
                    </div>
                    <div class="col">
                        <h4><?php _e( 'Visit the Extension Store', 'mashsb' ); ?></h4>
                        <p><?php _e( '<a href="https://easydigitaldownloads.com/downloads" target="_blank">The Extensions store</a> has a list of all available extensions, including convenient category filters so you can find exactly what you are looking for.', 'mashsb' ); ?></p>
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
                <?php printf( __( 'Thank you for updating to the latest version! MashShare %s is ready to grow your traffic from social networks!', 'mashsb' ), $display_version ); ?>
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
                        <img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/24-checkout.png'; ?>"/>
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
                        <img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/24-category-earnings.png'; ?>"/>
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
                        <img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/24-export.png'; ?>" class="mashsb-welcome-screenshots"/>
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
            <p class="about-description"><?php _e( 'Mashshare is created by a team of developers who aim to provide the #1 ecosystem for growing social media traffic through WordPress.', 'mashsb' ); ?></p>

            <?php echo $this->contributors(); ?>
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
            $contributor_list .= sprintf( '<a href="%s" title="%s">', esc_url( 'https://github.com/' . $contributor->login ), esc_html( sprintf( __( 'View %s', 'easy-digital-downloads' ), $contributor->login ) )
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
        $contributors = get_transient( 'edd_contributors' );

        if( false !== $contributors )
            return $contributors;

        $response = wp_remote_get( 'https://api.github.com/repos/mashshare/mashshare/contributors?per_page=999', array('sslverify' => false) );

        if( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) )
            return array();

        $contributors = json_decode( wp_remote_retrieve_body( $response ) );

        if( !is_array( $contributors ) )
            return array();

        set_transient( 'edd_contributors', $contributors, 3600 );

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
            wp_safe_redirect( admin_url( 'options-general.php?page=mashsb-settings&tab=visual#mashsb_settingslocation_header' ) );
            //wp_safe_redirect( admin_url( 'admin.php?page=mashsb-getting-started' ) );
            exit;
        } else { // Update
            wp_safe_redirect( admin_url( 'admin.php?page=mashsb-getting-started' ) );
            exit;
        }
    }

}

new MASHSB_Welcome();
