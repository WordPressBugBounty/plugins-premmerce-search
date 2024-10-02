<?php

namespace Premmerce\Search\Frontend;

use  Premmerce\SDK\V2\FileManager\FileManager ;
use  Premmerce\Search\Model\Word ;
use  Premmerce\Search\SearchPlugin ;
use  Premmerce\Search\WordProcessor ;
use  Premmerce\Search\Integration\OceanWpIntegration ;
use  WP_Query ;
use  wpdb ;
class SearchHandler
{
    /**
     * @var Word
     */
    private  $word ;
    /**
     * @var WordProcessor
     */
    private  $processor ;
    /**
     * @var wpdb
     */
    private  $wpdb ;
    /**
     * @var
     */
    private  $searchWord ;
    /**
     * @var
     */
    private  $fileManager ;
    /**
     * @var array|null
     */
    private  $cachedLikeQueries = null ;
    /**
     * @var array
     */
    private  $whereToSearch = array() ;
    /**
     * @var array
     */
    private  $templateRewrite = array() ;
    /**
     * @var string
     */
    private  $postsInSkuSearch = '' ;
    /**
     * @var string
     */
    private  $postsInTaxonomySearch = '' ;
    /**
     * SearchHandler constructor.
     *
     * @param Word $word
     * @param WordProcessor $processor
     * @param FileManager $fileManager
     */
    public function __construct( Word $word, WordProcessor $processor, FileManager $fileManager )
    {
        global  $wpdb ;
        $this->wpdb = $wpdb;
        $this->word = $word;
        $this->processor = $processor;
        $this->fileManager = $fileManager;
        $this->whereToSearch = get_option( SearchPlugin::OPTIONS['whereToSearch'] );
        $this->templateRewrite = get_option( SearchPlugin::OPTIONS['templateRewrite'] );
        add_action( 'wp_footer', array( $this, 'renderAutocompleteItem' ) );
        add_action( 'init', array( $this, 'checkIntegration' ) );
        add_action( 'parse_query', function ( WP_Query $query ) {
            if ( !$this->searchWord ) {
                $this->searchWord = esc_sql( mb_strtolower( $query->get( 's' ) ) );
            }
        } );
        add_filter(
            'posts_search',
            array( $this, 'getSkuIds' ),
            10,
            2
        );
        add_filter(
            'posts_search',
            array( $this, 'getTaxonomyIds' ),
            10,
            2
        );
        add_filter(
            'posts_search',
            array( $this, 'extendSearch' ),
            10,
            2
        );
        add_filter(
            'posts_fields',
            array( $this, 'extendSearchFields' ),
            10,
            2
        );
        add_filter(
            'posts_search_orderby',
            array( $this, 'extendSearchOrder' ),
            10,
            2
        );
        if ( !empty($this->templateRewrite) ) {
            add_filter(
                'wc_get_template',
                array( $this, 'templateRewrite' ),
                10,
                5
            );
        }
    }
    
    /**
     * Prepare post ids for sku search
     *
     * @param $request
     * @param WP_Query $wpQuery
     *
     * @return string
     */
    public function getSkuIds( $request, WP_Query $wpQuery )
    {
        #/premmerce_clear
        return $request;
    }
    
    /**
     * Prepare post ids for taxonomy search
     *
     * @param $request
     * @param WP_Query $wpQuery
     *
     * @return string
     */
    public function getTaxonomyIds( $request, WP_Query $wpQuery )
    {
        #/premmerce_clear
        return $request;
    }
    
    /**
     * Render autocomplete item template in footer
     */
    public function renderAutocompleteItem()
    {
        $autocompleteFields = get_option( SearchPlugin::OPTIONS['autocompleteFields'] );
        $this->fileManager->includeTemplate( 'frontend/autocomplete-template.php', array(
            'data' => $autocompleteFields,
        ) );
    }
    
    /**
     * @param string $fields
     * @param WP_Query $wpQuery
     *
     * @return string
     */
    public function extendSearchFields( $fields, WP_Query $wpQuery )
    {
        
        if ( $wpQuery->is_search() ) {
            $likes = $this->getLikeQueries();
            $likeExcerpt = $this->getLikeExcerptPart();
            if ( count( $likes ) ) {
                return $fields . ', (' . implode( '+', $likes ) . $likeExcerpt . $this->postsInSkuSearch . $this->postsInTaxonomySearch . ') as relevance';
            }
        }
        
        return $fields;
    }
    
    /**
     * @param string $orderBy
     * @param WP_Query $wpQuery
     *
     * @return string
     */
    public function extendSearchOrder( $orderBy, WP_Query $wpQuery )
    {
        
        if ( $wpQuery->is_search() ) {
            $likes = $this->getLikeQueries();
            if ( count( $likes ) ) {
                return 'relevance DESC';
            }
        }
        
        return $orderBy;
    }
    
    /**
     * Extends default wordpress search
     *
     * @param string $request
     * @param WP_Query $wpQuery
     *
     * @return string
     */
    public function extendSearch( $request, WP_Query $wpQuery )
    {
        
        if ( $wpQuery->is_search() ) {
            $likes = $this->getLikeQueries();
            $likeExcerpt = $this->getLikeExcerptPart();
            $args = '(' . implode( '+', $likes ) . $likeExcerpt . $this->postsInSkuSearch . $this->postsInTaxonomySearch . ')';
            $request = sprintf( 'AND ((%s) >= 1 )', $args );
        }
        
        return $request;
    }
    
    /**
     * Create array of like queries for relevance
     *
     *
     * @return array|null
     */
    private function getLikeQueries()
    {
        
        if ( is_null( $this->cachedLikeQueries ) ) {
            $wordsFromSearch = $this->processor->splitString( $this->searchWord );
            $this->processor->setDictionary( $this->word->getWords() );
            $matchedWords = $this->processor->matchWords( $wordsFromSearch );
            //real search string
            $likes[] = '(' . $this->wpdb->prepare( "{$this->wpdb->posts}.post_title LIKE '%s'", '%' . $this->searchWord . '%' ) . ') * 2';
            //real search words
            foreach ( $wordsFromSearch as $singleSearchWord ) {
                $likes[] = '(' . $this->wpdb->prepare( "{$this->wpdb->posts}.post_title LIKE '%s'", '%' . $singleSearchWord . '%' ) . ')';
            }
            //found in dictionary
            foreach ( $matchedWords as $word ) {
                $likes[] = '(' . $this->wpdb->prepare( "{$this->wpdb->posts}.post_title LIKE '%s'", '%' . $word . '%' ) . ')';
            }
            $this->cachedLikeQueries = $likes;
        }
        
        return $this->cachedLikeQueries;
    }
    
    /**
     *
     * @return string
     *
     */
    private function getLikeExcerptPart()
    {
        $excerptLike = '';
        if ( isset( $this->whereToSearch['excerpt'] ) && $this->whereToSearch['excerpt'] ) {
            $excerptLike = '+(' . $this->wpdb->prepare( " {$this->wpdb->posts}.post_excerpt LIKE '%s'", '%' . $this->searchWord . '%' ) . ')';
        }
        return $excerptLike;
    }
    
    /**
     * Search by product and product variation sku.
     *
     * @return string
     */
    private function getIdsForSkuSearch()
    {
        $postsInQueryPart = '';
        $productsIds = $this->wpdb->get_col( $this->wpdb->prepare( "SELECT p.ID FROM {$this->wpdb->posts} p\n\t\t\t\t\t\tINNER JOIN {$this->wpdb->postmeta} pm ON (pm.post_id = p.ID AND pm.meta_key = '_sku' AND pm.meta_value LIKE %s )\n\t\t\t\t\t  \tWHERE post_type  = 'product'", '%' . $this->searchWord . '%' ) );
        $productVariationsIds = $this->wpdb->get_col( $this->wpdb->prepare( "SELECT DISTINCT p.post_parent\n\t\t\t\t\t  \tFROM {$this->wpdb->posts} p\n\t\t\t\t\t  \tINNER JOIN {$this->wpdb->postmeta} pm ON (pm.post_id = p.ID AND pm.meta_key = '_sku' AND pm.meta_value LIKE %s )\n\t\t\t\t\t  \tWHERE post_type = 'product_variation'", '%' . $this->searchWord . '%' ) );
        $foundIds = array_merge( $productsIds, $productVariationsIds );
        
        if ( $foundIds ) {
            $placeholders = array_fill( 0, count( $foundIds ), '%d' );
            $placeholders = implode( ',', $placeholders );
            $postsInQueryPart = $this->wpdb->prepare( " +( {$this->wpdb->posts}.ID IN ({$placeholders}) ) * 3", $foundIds );
        }
        
        return $postsInQueryPart;
    }
    
    /**
     * Add theme integration
     */
    public function checkIntegration()
    {
        $theme = wp_get_theme();
        if ( 'oceanwp' == $theme->get_template() ) {
            new OceanWpIntegration();
        }
    }
    
    /**
     * Search by product tags and categories.
     *
     * @return string
     */
    private function getIdsForTaxonomySearch( $taxonomies = array( 'product_cat', 'product_tag' ) )
    {
        $postsInQueryPart = '';
        array_walk( $taxonomies, array( $this, 'addToQueryFormatted' ) );
        $taxonomies = implode( ' OR ', $taxonomies );
        $taxonomyIds = $this->wpdb->get_col( $this->wpdb->prepare( "SELECT term_taxonomy_id AS id FROM {$this->wpdb->term_taxonomy}\n              INNER JOIN {$this->wpdb->terms} ON ({$this->wpdb->term_taxonomy}.term_taxonomy_id = {$this->wpdb->terms}.term_id AND ({$taxonomies}))\n              WHERE {$this->wpdb->terms}.name LIKE %s", '%' . $this->searchWord . '%' ) );
        
        if ( !empty($taxonomyIds) ) {
            $termIds = implode( ',', $taxonomyIds );
            $productsIds = $this->wpdb->get_col( "SELECT ID FROM {$this->wpdb->posts}\n           INNER JOIN {$this->wpdb->term_relationships} ON ({$this->wpdb->posts}.ID = {$this->wpdb->term_relationships}.object_id)\n           WHERE {$this->wpdb->term_relationships}.term_taxonomy_id IN ({$termIds}) AND post_type = 'product'" );
        }
        
        
        if ( !empty($productsIds) ) {
            $placeholders = array_fill( 0, count( $productsIds ), '%d' );
            $placeholders = implode( ',', $placeholders );
            $postsInQueryPart = $this->wpdb->prepare( " +( {$this->wpdb->posts}.ID IN ({$placeholders}) ) * 3", $productsIds );
        }
        
        return $postsInQueryPart;
    }
    
    /**
     * Format query string
     *
     * @param string $item
     * @return array
     */
    protected function addToQueryFormatted( &$item )
    {
        $item = "{$this->wpdb->term_taxonomy}.taxonomy = '{$item}'";
    }
    
    /**
     * Search template rewrite
     */
    public function templateRewrite(
        $template,
        $template_name,
        $args,
        $template_path,
        $default_path
    )
    {
        if ( $template_name == 'product-searchform.php' ) {
            $template = plugin_dir_path( __FILE__ ) . '../../views/frontend/product-searchform.php';
        }
        return $template;
    }

}