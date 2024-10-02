<?php

namespace Premmerce\Search\Admin;

use  Premmerce\Search\Model\Word ;
use  Premmerce\Search\SearchPlugin ;
use  Premmerce\SDK\V2\FileManager\FileManager ;
use  Premmerce\Search\WordProcessor ;
/**
 * Class Admin
 *
 * @package Premmerce\Search\Admin
 */
class Admin
{
    /**
     * @var FileManager
     */
    private  $fileManager ;
    /**
     * @var string
     */
    private  $settingsPage ;
    /**
     * @var Word
     */
    private  $word ;
    /**
     * @var WordProcessor
     */
    private  $wordProcessor ;
    /**
     * Admin constructor.
     *
     * Register menu items and handlers
     *
     * @param FileManager $fileManager
     * @param Word $word
     * @param WordProcessor $processor
     */
    public function __construct( FileManager $fileManager, Word $word, WordProcessor $processor )
    {
        $this->fileManager = $fileManager;
        $this->word = $word;
        $this->wordProcessor = $processor;
        $this->settingsPage = SearchPlugin::DOMAIN . '-admin';
        add_action( 'admin_init', array( $this, 'registerSettings' ) );
        add_action( 'admin_menu', array( $this, 'addMenuPage' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAssets' ) );
        add_filter( 'admin_footer_text', array( $this, 'removeFooterAdmin' ) );
    }
    
    /**
     * Add submenu to premmerce menu page
     */
    public function addMenuPage()
    {
        global  $admin_page_hooks ;
        $premmerceMenuExists = isset( $admin_page_hooks['premmerce'] );
        $svg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="20" height="16" style="fill:#82878c" viewBox="0 0 20 16"><g id="Rectangle_7"> <path d="M17.8,4l-0.5,1C15.8,7.3,14.4,8,14,8c0,0,0,0,0,0H8h0V4.3C8,4.1,8.1,4,8.3,4H17.8 M4,0H1C0.4,0,0,0.4,0,1c0,0.6,0.4,1,1,1 h1.7C2.9,2,3,2.1,3,2.3V12c0,0.6,0.4,1,1,1c0.6,0,1-0.4,1-1V1C5,0.4,4.6,0,4,0L4,0z M18,2H7.3C6.6,2,6,2.6,6,3.3V12 c0,0.6,0.4,1,1,1c0.6,0,1-0.4,1-1v-1.7C8,10.1,8.1,10,8.3,10H14c1.1,0,3.2-1.1,5-4l0.7-1.4C20,4,20,3.2,19.5,2.6 C19.1,2.2,18.6,2,18,2L18,2z M14,11h-4c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4c0.6,0,1-0.4,1-1C15,11.4,14.6,11,14,11L14,11z M14,14 c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1c0.6,0,1-0.4,1-1C15,14.4,14.6,14,14,14L14,14z M4,14c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1 c0.6,0,1-0.4,1-1C5,14.4,4.6,14,4,14L4,14z"/></g></svg>';
        $svg = 'data:image/svg+xml;base64,' . base64_encode( $svg );
        if ( !$premmerceMenuExists ) {
            add_menu_page(
                'Premmerce',
                'Premmerce',
                'manage_options',
                'premmerce',
                '',
                $svg
            );
        }
        add_submenu_page(
            'premmerce',
            esc_html__( 'Search settings', 'premmerce-search' ),
            esc_html__( 'Search settings', 'premmerce-search' ),
            'manage_options',
            $this->settingsPage,
            array( $this, 'options' )
        );
        
        if ( !$premmerceMenuExists ) {
            global  $submenu ;
            unset( $submenu['premmerce'][0] );
        }
    
    }
    
    /**
     * Options page
     */
    public function options()
    {
        $data = $_POST;
        if ( isset( $data[SearchPlugin::DOMAIN . '-update-indexes'] ) ) {
            $this->update();
        }
        $current = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'settings' );
        $tabs['settings'] = esc_html__( 'Settings', 'premmerce-search' );
        $tabs['affiliate'] = esc_html__( 'Affiliate', 'premmerce-url-manager' );
        #premmerce_clear
        
        if ( function_exists( 'premmerce_ps_fs' ) ) {
            if ( premmerce_ps_fs()->is_registered() ) {
                $tabs['account'] = esc_html__( 'Account', 'premmerce-search' );
            }
            $tabs['contact'] = esc_html__( 'Contact Us', 'premmerce-search' );
        }
        
        #/premmerce_clear
        $this->fileManager->includeTemplate( 'admin/main.php', array(
            'current'  => $current,
            'tabs'     => $tabs,
            'pageSlug' => $this->settingsPage,
        ) );
    }
    
    /**
     * Register plugin settings
     */
    public function registerSettings()
    {
        add_settings_section(
            'premmerce-search-settings',
            '',
            '',
            $this->settingsPage
        );
        register_setting( 'premmerce_search_options', 'premmerce_search_field_selector' );
        add_settings_field(
            'premmerce_search_field_selector',
            esc_html__( 'Search field selector', 'premmerce-search' ),
            array( $this, 'inputCallback' ),
            $this->settingsPage,
            'premmerce-search-settings',
            array(
            'name'        => 'premmerce_search_field_selector',
            'value'       => get_option( 'premmerce_search_field_selector' ),
            'description' => esc_html__( 'CSS Selector of custom search field', 'premmerce-search' ),
        )
        );
        register_setting( 'premmerce_search_options', 'premmerce_search_force_product_search' );
        add_settings_field(
            'premmerce_search_force_product_search',
            esc_html__( 'Force product search', 'premmerce-search' ),
            array( $this, 'checkboxCallback' ),
            $this->settingsPage,
            'premmerce-search-settings',
            array(
            'name'        => 'premmerce_search_force_product_search',
            'value'       => get_option( 'premmerce_search_force_product_search' ),
            'description' => esc_html__( 'Search for products only', 'premmerce-search' ),
        )
        );
        // Shortcode
        add_settings_field(
            'premmerce_search_shortcode',
            esc_html__( 'Shortcode for display form', 'premmerce-search' ),
            function () {
            $this->fileManager->includeTemplate( 'admin/fields/shortcode.php' );
        },
            $this->settingsPage,
            'premmerce-search-settings'
        );
    }
    
    public function checkboxCallback( $data )
    {
        $this->fileManager->includeTemplate( 'admin/fields/checkbox.php', $data );
    }
    
    public function outOfStockVisibilityCallback()
    {
        $outOfStockVisibility = get_option( SearchPlugin::OPTIONS['outOfStockVisibility'] );
        $this->fileManager->includeTemplate( 'admin/fields/out-of-stock-visibility.php', array(
            'data' => $outOfStockVisibility,
        ) );
    }
    
    public function inputCallback( $data )
    {
        $this->fileManager->includeTemplate( 'admin/fields/input.php', $data );
    }
    
    public function templateRewriteCallback()
    {
        $templateRewrite = get_option( SearchPlugin::OPTIONS['templateRewrite'] );
        $this->fileManager->includeTemplate( 'admin/fields/product-searchform.php', array(
            'data' => $templateRewrite,
        ) );
    }
    
    /**
     * Register admin styles
     */
    public function enqueueAssets()
    {
        if ( stristr( get_current_screen()->id, $this->settingsPage ) ) {
            wp_enqueue_style( 'premmerce-search-admin', $this->fileManager->locateAsset( 'admin/css/premmerce-search-admin.css' ) );
        }
    }
    
    /**
     * Set settings on plugin activate. Do not change existing options.
     */
    public function setSettings()
    {
        //Set default settings
        $defaultSettings = array(
            SearchPlugin::OPTIONS['minToSearch']        => 3,
            SearchPlugin::OPTIONS['resultNum']          => 6,
            SearchPlugin::OPTIONS['whereToSearch']      => array(
            'sku'      => false,
            'excerpt'  => false,
            'tag'      => false,
            'category' => false,
        ),
            SearchPlugin::OPTIONS['autocompleteFields'] => array(
            'image' => true,
            'price' => true,
            'btn'   => false,
        ),
        );
        foreach ( $defaultSettings as $settingName => $settingValue ) {
            if ( !get_option( $settingName ) ) {
                update_option( $settingName, $settingValue );
            }
        }
    }
    
    /**
     * Sanitize Where to search checkboxes data
     *
     * @param array $rawInput
     *
     * @return array $cleanData
     */
    public function sanitizeWhereToSearch( $rawInput )
    {
        $checkboxes = array(
            'sku',
            'excerpt',
            'tag',
            'category'
        );
        $cleanData = array();
        foreach ( $checkboxes as $checkboxName ) {
            $cleanData[$checkboxName] = !empty($rawInput[$checkboxName]);
        }
        return $cleanData;
    }
    
    /**
     * Sanitize autocomplete fields to show
     *
     * @param array $rawInput
     *
     * @return array $cleanData
     */
    public function sanitizeAutocompleteFields( $rawInput )
    {
        $checkboxes = array( 'image', 'price', 'btn' );
        $cleanData = array();
        foreach ( $checkboxes as $checkboxName ) {
            $cleanData[$checkboxName] = !empty($rawInput[$checkboxName]);
        }
        return $cleanData;
    }
    
    /**
     * Sanitize Where to search checkboxes data
     *
     * @param array $rawInput
     *
     * @return array $cleanData
     */
    public function sanitizeOutOfStockVisibility( $rawInput )
    {
        $checkboxes = array( 'outOfStock' );
        $cleanData = array();
        foreach ( $checkboxes as $checkboxName ) {
            $cleanData[$checkboxName] = !empty($rawInput[$checkboxName]);
        }
        return $cleanData;
    }
    
    /**
     * Sanitize custom user CSS
     *
     * @param array $input
     *
     * @return array $cleanData
     */
    public function sanitizeCustomCss( $input )
    {
        global  $allowedposttags ;
        $cleanData = wp_kses( (string) $input, $allowedposttags );
        return $cleanData;
    }
    
    /**
     * Update indexes handler
     */
    public function update()
    {
        $words = $this->wordProcessor->prepareIndexes( $this->word->selectProductWords() );
        $this->word->updateIndexes( $words );
    }
    
    /**
     * Admin footer modification
     *
     * @param $text - default Wordpress footer thankyou text
     */
    public function removeFooterAdmin( $text )
    {
        $screen = get_current_screen();
        $premmercePages = array( 'premmerce_page_premmerce-search-admin' );
        
        if ( in_array( $screen->id, $premmercePages ) ) {
            $link = 'https://wordpress.org/support/plugin/premmerce-search/reviews/?filter=5';
            $target = 'target="_blank"';
            $text = '<span id="footer-thankyou">';
            $text .= sprintf( __( 'Please rate our Premmerce Product Search for WooCommerce on <a href="%1$s" %2$s>WordPress.org</a><br/>Thank you from the Premmerce team!', 'premmerce-filter' ), $link, $target );
            $text .= '</span>';
        } else {
            $text = '<span id="footer-thankyou">' . $text . '</span>';
        }
        
        return $text;
    }

}