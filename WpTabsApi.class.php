<?php

/**
 * WP Tabs Api Class
 *
 * PHP Version 5.3
 * 
 * @category  WPTabsAPI
 * @package   Wordpress
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2013 Alex Wyett
 * @license   http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link      http://www.carltonsoftware.co.uk
 */

/**
 * Require the admin class
 */
require_once 'WpTabsApiAdmin.class.php';

/**
 * Require the api wrapper class
 */
require_once 'WpTabsApiWrapper.class.php';

/**
 * Require the api search widget class
 */
require_once 'WpTabsSearchWidget.class.php';


/**
 * Plugin Admin Class
 *
 * PHP Version 5.3
 * 
 * @category  WPTabsAPI
 * @package   Wordpress
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2013 Alex Wyett
 * @license   http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link      http://www.carltonsoftware.co.uk
 */
class WpTabsApi
{
    /**
     * Static cottage post type (for caching)
     *
     * @var mixed
     */
    public static $cottagePostType = false;
    
    /**
     * Tabs api wrapper
     *
     * @var WpTabsApiWrapper
     */
    protected $tabsApi;
    
    /**
     * End points array
     * 
     * @var array
     */
    private $_endPoints = array(
        'booking',
        'shortlist'
    );
    
    /**
     * Cottage slug format
     * 
     * @var string 
     */
    protected $cottageSlugFormat = '{getName}-{getPropref}';


    /**
     * Constructor
     * 
     * @param string $file Originating file
     * 
     * @return void
     */
    function __construct($file)
    {
        // Start session if not already started
        @session_start();
        
        // Specific WP actions...
        register_activation_hook($file, array(&$this, 'activate'));
        register_uninstall_hook($file, array(&$this, 'uninstall'));
        
        // Set globals
        $this->_defineConstants();
        
        // Set Tabs API Object
        $this->_setTabsApiConnection();

        // Register post types (including templates)
        add_action('init', array(&$this, 'registerCottagePostType'));
        
        // Add menu
        add_action('admin_menu', array('WPTabsApiAdmin', 'adminMenu'));
        
        // Admin scripts/styles
        add_action(
            'admin_enqueue_scripts', 
            array(
                'WpTabsApiAdmin', 
                'printAdminStyles'
            )
        );
        add_action(
            'admin_enqueue_scripts', 
            array(
                'WpTabsApiAdmin', 
                'printAdminScripts'
            )
        );
        
        // Add meta boxes
        add_action('admin_init', array('WpTabsApiAdmin', 'getMetaBoxes'));
        
        // Meta Box save function
        add_action('save_post', array('WpTabsApiAdmin', 'setMetaBoxes'), 10, 2);
        
        // Add Search Widget
        add_action(
            'widgets_init', 
            create_function('', 'return register_widget("WpTabsSearchWidget");')
        );
            
        // Add booking enquiry ajax action
        add_action('wp_ajax_enquiry', array(&$this, 'preProcessEnquiry'));  
        add_action('wp_ajax_nopriv_enquiry', array(&$this, 'preProcessEnquiry'));
            
        // Add cottage search ajax action
        add_action('wp_ajax_cottage_search', array(&$this, 'getAjaxCottageSearch'));  
        add_action('wp_ajax_nopriv_cottage_search', array(&$this, 'getAjaxCottageSearch'));
            
        // Add shortlist ajax action
        add_action('wp_ajax_shortlist', array(&$this, 'preProcessShortlist'));  
        add_action('wp_ajax_nopriv_shortlist', array(&$this, 'preProcessShortlist'));
            
        // Add shortlist ajax action
        add_action('wp_ajax_cottagesearch', array(&$this, 'preProcessCottageSearch'));  
        add_action('wp_ajax_nopriv_cottagesearch', array(&$this, 'preProcessCottageSearch'));
        
        // Add brochure request shortcode
        add_shortcode('brochure_request', array($this, 'renderBrochureForm'));
        
        // Add owner pack request shortcode
        add_shortcode('ownerpack_request', array($this, 'renderOwnerPackForm'));
    }
    
    /**
     * Install function
     * 
     * @return void
     */
    public function activate()
    {
        
    }
    
    /**
     * Uninstall function
     * 
     * @return void
     */
    public function uninstall()
    {
        
    }
    
    /**
     * Get the tabs api object
     *
     * @return \WpTabsApiWrapper
     */
    public function getTabsApi()
    {
        return $this->tabsApi;
    }
    
    /**
     * Get the tabs api object
     *
     * @return boolean
     */
    public function isTabsApiConnected()
    {
        return is_object($this->getTabsApi());
    }
    
    
    /**
     * Get the cottage post type
     *
     * @param boolean $useCache Force to not use cache if false
     *
     * @return mixed
     */
    public function getCottagePostType($useCache = true)
    {
        if ($useCache && is_string(self::$cottagePostType)) {
            return self::$cottagePostType;
        }
        
        $cottagePostType = get_option(WPTABSAPIPLUGIN_COTTAGE_POST_TYPE_KEY);
        
        if (is_string($cottagePostType) && strlen($cottagePostType) > 0) {
            
            // Update cached variable
            self::$cottagePostType = $cottagePostType;
            
            return $cottagePostType;
        } else {
            return false;
        }
    }
    
    /**
     * Set the cottage post type
     *
     * @param string $cpt The provided string for the cottage post type
     *
     * @return void
     */
    public function setCottagePostType($cpt)
    {
        update_option(
            WPTABSAPIPLUGIN_COTTAGE_POST_TYPE_KEY,
            sanitize_title($cpt)
        );
    }
    
    /**
     * Register cottage post type.
     * 
     * @return void
     */
    public function registerCottagePostType()
    {
        $cottagePostType = $this->getCottagePostType(false);
        if ($cottagePostType) {
            
            // Get the total cottages
            define(
                'WPTABS_COTTAGECOUNT',
                $this->_getIndexedCottageCount($cottagePostType)
            );
            
            // Register cottage post type
            register_post_type(
                $cottagePostType, 
                array(
                    'public' => true,
                    'has_archive' => $cottagePostType,
                    'publicly_queryable' => true,
                    'query_var' => true,
                    'show_in_menu' => false, 
                    'show_in_nav_menus' => true, 
                    'show_in_admin_bar' => false,
                    'labels' => array(
                        'name' => 'Cottage',
                        'singular_name' => 'Cottage'
                    ),
                    'rewrite' => array(
                        'slug' => $cottagePostType,
                        'with_front' => true
                    )
                )
            );
            
            // Request optional endpoints for custom templates
            $eps = $this->_getOptionalEndpoints();
            $this->_endPoints = array_merge($this->_endPoints, $eps);
            
            foreach ($this->_endPoints as $ept) {
                add_rewrite_endpoint($ept, EP_PERMALINK);
            }
            
            add_filter(
                'template_redirect', 
                array(
                    &$this, 
                    'addTemplateRedirect'
                )
            );
            
            // Add in booking, enquiry and payment endpoints
            add_filter('request', array(&$this, 'addEndPoints'));
            
            // Add in templates
            add_filter('template_include', array(&$this, 'includeTemplate'), 1);
            
            // Add the_content() filter.  This will add in a property object
            // to each $posts variable in the_loop()
            add_filter('the_content', array(&$this, 'addCottageContent'));          
        }
    }

    /**
     * Add the enpoints to the query variable so WordPress
     * won't mangle it.
     * 
     * @param array $vars Current query url
     * 
     * @return array
     */
    public function addEndPoints($vars)
    {
        array_merge($vars, $this->_endPoints);
        return $vars;
    }

    /**
     * From http://codex.wordpress.org/Template_Hierarchy
     * 
     * This function is for determining which template to use when a
     * custom end point is found.
     *
     * Adds a custom template to the query queue.
     * 
     * @return void
     */
    public function addTemplateRedirect()
    {
        if (stristr($_SERVER['REDIRECT_URL'], 'imagecache')) {
            extract($this->_getImageCacheUrlVars($_SERVER['REDIRECT_URL']));
            $this->_cacheImageRequest(
                $propRef, 
                $filename,
                $type,
                $width,
                $height
            );
        }
        
        // If this isnt a single cottage post, return.
        if (!is_singular()) {
            return;
        }
        
        $endPointFound = false;
        global $wp_query;
        
        foreach ($this->_endPoints as $ep) {
            if (in_array($ep, array_keys($wp_query->query))) {
                $endPointFound = $ep;
                
                // Template var - this is the matches[3] in the rewrite rules.
                // We will test if a template exists or is blank to be valid, 
                // i.e. /cottage/{cottage-slug}/booking/update is ok but not 
                // /cottage/{cottage-slug}/booking/bla bla
                if ($wp_query->query[$ep] != '') {
                    $endPointFound .= '-' . $wp_query->query[$ep];
                }
            }
        }
        
        // No endpoint found, return.
        if (!$endPointFound) {
            return;
        }
        
        // Look for local template
        $template = locate_template(
            array(
                'single-cottage-'.$endPointFound.'.php'
            )
        );
        
        // If Empty String, use default template
        if ($template == '') {
            $template = WPTABSAPIPLUGIN_DIR . 'templates' . DS
                . 'single-cottage-'.$endPointFound.'.php';
        }
        
        // Get post, could be useful...
        $post = get_queried_object();
        
        // Get pre-processing function
        $func = 'preProcess' . ucfirst(preg_replace("/[^\w]+/", "", $endPointFound));
    
        // Do some pre-processing.  This will extract useful variables
        // which can be used in the templates.  Look for a global pre-processing
        // function first and extra its contents, if thats not found, use the
        // class defaults.
        if (!function_exists($func)) {
            if (method_exists($this, $func)) {
                extract($this->$func($post));
            }
        } else {
            extract($func($post));
        }
        
        // Include template file
        if (is_file($template)) {
            
            // Include template and exit
            include_once $template;
            exit();
        } else {
            // Returning will go back to the cottage page.  This shouldn't
            // ever occur anyway
            $this->do404();
        }
    }
    
    /**
     * Add in plugin template files.  Note, this function will recurse after 
     * reindexing the cottages if the index cottage count does not equal the 
     * total cottages found.
     *
     * @param string  $template_path Current wordpress template file
     * @param boolean $recurse       Allow the function to rescan property data
     *
     * @global $property Declares the global property object so it can be used
     * within template files.
     *
     * @return string
     */
    public function includeTemplate($template_path, $recurse = true)
    {
        global $post;
        
        // Check what type of page it is, single cottage, do this, else do the
        // next bit
        if (get_post_type() == $this->getCottagePostType()
            && is_single()
        ) {
            
            // Cottage pre-processing function.  This will extract
            // $property as a global object so it can be used in the
            // template file
            extract($this->preProcessCottage($post));
            
            // checks if the file exists in the theme first
            $templateFile = 'single-' . $this->getCottagePostType();
            $themeFile = locate_template(array($templateFile . '.php'));
            
            if ($themeFile) {
                $template_path = $themeFile;
            } else {
                $templateFile = WPTABSAPIPLUGIN_DIR . DS . 'templates' . DS
                      . 'single-cottage.php';
                if (is_file($templateFile)) {
                    $template_path = $templateFile;
                }
            }
            
            
        } else {
            // Use the search helper if its a normal wordpress page and 
            // if the search option has been enabled
            if (is_page()) {
                if (get_post_meta(
                    $post->ID, 
                    WPTABSAPIPLUGIN_SEARCHPAGE_OPT_EP_KEY, true
                ) == '1') {
                    
                    // Get the search helper
                    $search = $this->getSearch(
                        $_GET,
                        get_permalink($post->ID), 
                        $this->getSearchCriteria($post->ID)
                    );
                    
                    // If a valid search, save the search in the session
                    if ($search->getSearch()) {                        
                        // Check totals are correct
                        if ($recurse) {
                            if (WPTABS_COTTAGECOUNT != $this->getTabsApi()->getNumberOfProperties()) {
                                $this->updateCottageIndexes();
                                return $this->includeTemplate($template_path, false);
                            }
                        }
                    }
                    
                    // Add search object to global post object so it can be
                    // used in the template
                    $post->tabs_api_search = $search;
                }
            }
        }
        return $template_path;
    }
    
    /**
     * Get all of the cottage indexes in wordpress
     * 
     * @return integer
     */
    public function updateCottageIndexes()
    {
        $updateCount = 0;
        if ($this->getCottagePostType(false) && $this->isTabsApiConnected()) {
            //$this->_removeAllCottageIndexes();
            $properties = $this->getTabsApi()->getAllProperties();
            if ($properties) {
            
                // Stop cat/term counting
                wp_defer_term_counting(true);
                
                foreach ($properties as $property) {
                    if (!$this->_getPageByName(
                         $this->_getCottageSlug($property), 
                            $this->getCottagePostType()
                        )
                    ) {
                        if ($this->_addCottageIndex(
                            $property->getId(), 
                            $property->getName(),
                            $this->_getCottageSlug($property)
                        )) {
                            $updateCount++;
                        }
                    }
                }
                
                // Start cat/term counting again
                wp_defer_term_counting(false);
            }
        }
        
        // Flush permalinks
        flush_rewrite_rules(false);
        
        // Update index time
        update_option(WPTABSAPIPLUGIN_INDEXTIME, date('Y-m-d h:i:s'));
        
        return $updateCount;
    }
    
    /**
     * Get all of the cottage indexed in wordpress
     * 
     * @return array
     */
    public function getAllIndexedCottages()
    {
        $cpt = $this->getCottagePostType(false);
        if ($cpt) {
            return new Wp_Query(
                array(
                    'post_type' => $cpt, 
                    'posts_per_page' => -1
                )
            );
        }
        return false;
    }
    
    /**
     * Adds a property into the global $post
     * 
     * @param string $content Existing content
     * 
     * @return string 
     */
    public function addCottageContent($content)
    {
        global $post;
        if ($post->post_type != $this->getCottagePostType()) {
        } else {
            $post->property = $this->getTabsApi()->getPropertyFromId(
                $post->post_excerpt
            );
        }        
        return $content;
    }
    
    /**
     * Get permalink hook
     * 
     * @param \tabs\api\property\Property $cottage   Api Property object
     * @param string                      $permalink Permalink
     * 
     * @return string
     */
    public function getCottagePermalink($cottage = null, $permalink = '/')
    {
        return WpTabsApi__getEndPointPermalink(
            $this->_getPageByName(
                $this->_getCottageSlug($cottage), 
                $this->getCottagePostType()
            ), 
            $permalink
        );
    }
    
    /**
     * Return cottage slug format
     * 
     * @return string
     */
    public function getCottageSlugFormat()
    {
        return $this->cottageSlugFormat;
    }
    
    /**
     * Sends a default 404 header and tests for a template 404 page too
     * 
     * @param string $message An optional message you want to pass to the
     * 404 page.
     * 
     * @global type $wp_query 
     * 
     * @return void
     */
    public function do404($message = '')
    {
        global $wp_query;
        header("HTTP/1.0 404 Not Found");
        $wp_query->set_404();
        if (is_file(TEMPLATEPATH . DS . '404.php')) {
            include TEMPLATEPATH . DS . '404.php';
        }
        exit();
    }
    
    /**
     * Create a search helper object and perform a search
     * 
     * @param array  $searchParams   Array of filters
     * @param string $baseUrl        Base url of the search, for pagination
     * @param array  $searchCriteria Array of filters for landing pages
     * 
     * @return SearchHelperLite 
     */
    public function getSearch(
        $searchParams = array(), 
        $baseUrl = '', 
        $searchCriteria = array()
    ) {
        $searchId = '';
        if (isset($_SESSION['tabs_api_search_id'])) {
            $searchId = $_SESSION['tabs_api_search_id'];
        }
        
        // Filter out invalid search filters
        $filters = $this->getTabsApi()->getSearchFilters();
        $searchParams = array_intersect_key(
            array_filter($searchParams), 
            array_flip($filters)
        );

        // Get the search helper
        $search = $this->getTabsApi()->getSearchHelper(
            $searchParams,
            $searchCriteria,
            $baseUrl
        );

        // Set search prefix
        $search->setSearchPrefix(
            WPTABSAPIPLUGINSEARCHPREFIX
        );

        // Perform the search and set Search ID
        $search->search($searchId);

        // If a valid search, save the search in the session
        if ($search->getSearch()) {
            $_SESSION['tabs_api_search_id'] = $search->getSearchId();

            // Loop through properties and add shortlist marker
            $shortlist = $this->getTabsApi()->getShortlist();

            foreach ($search->getProperties() as $property) {
                if (in_array($property->getPropref(), $shortlist)) {
                    $property->setShortlist(true);
                    $search->getSearch()->setProperty($property);
                }
            }
        }
        
        return $search;
    }
    
    /**
     * Perform a cottage search and return json data (for map functionality)
     * 
     * @return void 
     */
    public function getAjaxCottageSearch()
    {
        $search = $this->getSearch($_POST); 
        
        // Register hook for the cottage ajax preprocessing
        do_action('wpTabsApiAjaxCottageSearch', $search);
    }
            
    
    /**
     * Get the search criteria of the landing page
     * 
     * @param integer $postId Post ID
     * 
     * @return array
     */
    public function getSearchCriteria($postId)
    {
        $filters = array();
        foreach (WPTabsApiAdmin::getFilterSettings() as $filter) {
            $filterVal = '';
            foreach ($filter['controls'] as $control) {
                $filterVal .= (string) get_post_meta(
                    $postId, 
                    'tabs_api_' . $control['name'], 
                    true
                );
            }
            if ($filterVal != '') {
                $filters[WPTABSAPIPLUGINSEARCHPREFIX . $filter['filter']] = $filterVal;
            }
        }
        
        // Check for shortlist
        if (get_post_meta(
            $postId, 
            WPTABSAPIPLUGIN_SEARCHPAGE_FILTER_SHORTLIST, true) == '1'
        ) {
            $shortlist = $this->getTabsApi()->getShortlist();
            if (count($shortlist) > 0) {
                $filters['reference'] = implode(',', $shortlist);
            } else {
                $filters['reference'] = 'XXXXXX';
            }
        }
        return $filters;
    }
    
    /**
     * Render a brochure request form and output it
     * 
     * @param array $attributes Shortcode attributes
     * 
     * @return string
     */
    public function renderBrochureForm($attributes)
    {
        extract(
            shortcode_atts(
                array(
                    'target' => ''
                ), 
                $attributes
            )
        );
        
        $brochureForm = \aw\formfields\forms\BrochureForm::factory(
            array(
                'method' => 'post'
            ), 
            $_POST,
            array_merge(
                array('Select' => ''),
                $this->getTabsApi()->getCountriesInverse()
            ),
            array_merge(
                array('Select' => ''),
                $this->_getSourcecodesOptgroup()
            )
        );
            
        // Register hook for the cottage preprocessing
        do_action('wpTabsApiBrochurePreprocess', $brochureForm);
        
        if (count($_POST) > 0) {
            $brochureForm->validate();
            if ($brochureForm->isValid()) {
                try {
                    // Create customer
                    $customer = $this->getTabsApi()->createNewCustomerFromPostArray(
                        $_POST
                    );
                    $customer->setBrandCode(get_option('tabs_api_brandcode'));
                    $customer->requestBrochure();
                    
                    // Register hook for the brochure request post processing
                    do_action('wpTabsApiBrochurePostProcess', $customer);
                
                    if ($target == '') {
                        return get_option(
                            'wp_tabs_api_brochure_request_response',
                            '<p>Thank you for ordering one of our brochures.  
                                We\'ll put one in the post to your shortly.</p>'
                        );
                    } else {
                        redirect($target);
                    }
                } catch(Exception $e) {
                    $this->do404($e->getMessage());
                }
            }
        }
        
        return (string) $brochureForm;
    }
    
    /**
     * Render an owner pack request form and output it
     * 
     * @param array $attributes Shortcode attributes
     * 
     * @return string
     */
    public function renderOwnerpackForm($attributes)
    {
        extract(
            shortcode_atts(
                array(
                    'target' => ''
                ), 
                $attributes
            )
        );
        
        $ownerForm = \aw\formfields\forms\OwnerpackForm::factory(
            array(
                'method' => 'post'
            ), 
            $_POST,
            array_merge(
                array('Select' => ''),
                $this->getTabsApi()->getCountriesInverse()
            ),
            array_merge(
                array('Select' => ''),
                $this->getTabsApi()->getSourceCodesInverse()
            )
        );
            
        // Register hook for the cottage preprocessing
        do_action('wpTabsApiOwnerpackPreprocess', $ownerForm);
        
        if (count($_POST) > 0) {
            $ownerForm->validate();
            if ($ownerForm->isValid()) {
                try {
                    // Create owner
                    $owner = $this->getTabsApi()->createNewOwnerFromPostArray(
                        $_POST
                    );
                    $owner->setEnquiryBrandCode(get_option('tabs_api_brandcode'));
                    
                    // Register hook for the owner pack request post processing
                    do_action('wpTabsApiOwnerPackPostProcess', $owner);
                    
                    $owner->requestOwnerPack(
                        assignArrayValue($_POST, 'where', ''),
                        assignArrayValue($_POST, 'about', ''), 
                        (isset($_POST['currentlyLetting']) ? ($_POST['currentlyLetting'] == '1') : false)
                    );
                
                    if ($target == '') {
                        return get_option(
                            'wp_tabs_api_ownerpack_request_response',
                            '<p>Thank you for ordering one of our owner packs.  
                                We\'ll put one in the post to your shortly.</p>'
                        );
                    } else {
                        redirect($target);
                    }
                } catch(Exception $e) {
                    $this->do404($e->getMessage());
                }
            }
        }
        
        return (string) $ownerForm;
    }
    
    // ---------------------- Pre-Processing functions --------------------- //
    
    
    /**
     * Cottage Pre-processing function
     *
     * @param array $post Current post object
     *
     * @return array
     */
    public function preProcessCottage($post)
    {        
        global $property;
        try {
            $property = $this->getTabsApi()->getPropertyFromId($post->post_excerpt);
            if ($property) {
                
                // Register hook for the cottage preprocessing
                do_action('wpTabsApiCottagePreprocess', $property);
                
                return array(
                    'property' => $property
                );
                
            } else {
                $this->do404('No property found');
            }
        } catch (\tabs\api\client\ApiException $e) {
            $this->do404($e->getMessage());
        }
    }
    
    
    /**
     * Enquiry Pre-processing function.  This function gets called when the
     * wp-admin/admin-ajax.php file is called with an action hook of enquiry.
     *
     * @return array
     */
    public function preProcessEnquiry()
    {
        // Default response
        $response = array(
            "status" => "error", 
            "code" => "-1", 
            "message" => "No price found"
        );

        // Check supplied dates
        if (isset($_GET['fromDate'])
            && isset($_GET['toDate'])
            && isset($_GET['adults'])
            && isset($_GET['propRef'])
        ) {
            $people = intval($_GET['adults']);
            if (isset($_GET['children'])) {
                $people += intval($_GET['children']);
            }

            $fromdate = strtotime($_GET['fromDate']);
            $todate = strtotime($_GET['toDate']);

            $pets = 0;
            if (isset($_GET['pets'])) {
                $pets = intval($_GET['pets']);
            }

            try {
                // Do enquiry 
                $enquiry = $this->getTabsApi()->getEnquiry(
                    $_GET['propRef'] . '_' . get_option('tabs_api_brandcode', 'XX'), 
                    $fromdate, 
                    $todate, 
                    $people, 
                    $pets
                );

                $bkfe = $enquiry->getExtraDetail("BKFE");

                if ($bkfe) {
                    $bkfe = $bkfe->getTotalPrice();
                } else {
                    $bkfe = 0;
                }

                if ($enquiry->getBasicPrice() > 0) {
                    $response = array(
                        "status" => "ok", 
                        "bookingFee" => $bkfe,
                        "from" => date('d-m-Y', $fromdate),
                        "to" => date('d-m-Y', $todate),
                        "message" => sprintf(
                            '&pound;%s',
                            number_format($enquiry->getFullPrice(), 2)
                        )
                    );
                    $response = array_merge($response, $enquiry->toArray());
                }
            } catch(Exception $e) {
                $message = get_option('wp_tabs_api_statuscode_' . $e->getCode());
                if (!$message || strlen(trim($message)) == 0) {
                    $message = $e->getMessage();
                }
                $response = array(
                    "status" => "error", 
                    "code" => $e->getCode(), 
                    "message" => $message
                );
            }
        }
        
        // Register hook for the cottage enquiry preprocessing
        do_action('wpTabsApiEnquiryPreprocess', $enquiry, $response);

        // Output json
        generateResponse($response);
    }
    
    
    /**
     * Booking Create Pre-processing function
     *
     * @param array $post Current post object
     *
     * @return array
     */
    public function preProcessBookingcreate($post)
    {
        // Check supplied dates
        if (isset($_POST['fromDate'])
            && isset($_POST['toDate'])
            && isset($_POST['adults'])
        ) {
            $adults   = assignArrayValue($_POST, 'adults', 1);
            $children = assignArrayValue($_POST, 'children', 0);
            $infants  = assignArrayValue($_POST, 'infants', 0);
            $pets     = assignArrayValue($_POST, 'pets', 0);
            $fromdate = strtotime($_POST['fromDate']);
            $todate = strtotime($_POST['toDate']);
            
            try {
                $booking = $this->getTabsApi()->createNewBooking(
                    $post->post_excerpt,
                    $fromdate,
                    $todate,
                    $adults,
                    $children,
                    $infants,
                    $pets
                );
                
                if ($booking) {
                    $_SESSION['wp_tabs_api_booking_id'] = $booking->getBookingId();
                    redirect(
                        WpTabsApi__getEndPointPermalink(
                            $post->ID, 
                            'booking'
                        )
                    );
                }
            } catch(ApiException $e) {
                $this->do404($e->getApiMessage());
            }
        }

        return array();
    }

    /**
     * Booking Pre-processing function
     *
     * @param array $post Current post object
     *
     * @return array
     */
    public function preProcessBooking($post)
    {
        if (!checkKeyExists($_SESSION, 'wp_tabs_api_booking_id')) {
            // Catch any direct links
            $this->do404();
        } else {
            try {
                // Get booking Object
                $booking = $this->getTabsApi()->createBookingFromId(
                    $_SESSION['wp_tabs_api_booking_id']
                );

                // Check that booking hasn't already been completed
                if ($booking->isConfirmed()) {
                    redirect(
                        WpTabsApi__getEndPointPermalink(
                            $post->ID, 
                            'booking/complete'
                        )
                    );
                }

                $property = $this->getTabsApi()->getPropertyFromId(
                    $post->post_excerpt
                );

                // Create new booking form object
                $bookingForm = \aw\formfields\forms\BookingForm::factory(
                    array(
                        'method' => 'post',
                    ),
                    $_POST,
                    array_merge(
                        array('Select' => ''),
                        $this->getTabsApi()->getCountriesInverse()
                    ),
                    array_merge(
                        array('Select' => ''),
                        $this->getTabsApi()->getSourceCodesInverse()
                    ),
                    array(), // TODO: Extras
                    $booking->getAdults(),
                    $booking->getChildren(),
                    $booking->getInfants()
                );

                // Register hook for the cottage booking preprocessing
                do_action(
                    'wpTabsApiBookingPreprocess',
                    array(
                        'booking' => $booking,
                        'property' => $property,
                        'bookingForm' => $bookingForm
                    )
                );

                // Look for validation
                if (count($_POST) > 0) {
                    $bookingForm->validate();
                    if ($bookingForm->isValid()) {
                        try {
                            // Create customer and save to booking
                            $customer = $this->getTabsApi()
                                ->createNewCustomerFromPostArray($_POST);
                            $booking->setCustomer($customer);

                            // Create new party members (but clear first)
                            $booking->clearPartyMembers();
                            for ($i = 1; $i <= $booking->getAdults(); $i++) {
                                $member = $this->getTabsApi()
                                    ->createNewPartyMemberFromPostArray(
                                        $_POST,
                                        $i,
                                        'adult'
                                    );
                                    
                                if ($member) {
                                    $booking->setPartyMember($member);
                                }
                            }
                            for ($i = 1; $i <= $booking->getChildren(); $i++) {
                                $member = $this->getTabsApi()
                                    ->createNewPartyMemberFromPostArray(
                                        $_POST,
                                        $i,
                                        'child'
                                    );
                                if ($member) {
                                    $booking->setPartyMember($member);
                                }
                            }
                            for ($i = 1; $i <= $booking->getChildren(); $i++) {
                                $member = $this->getTabsApi()
                                    ->createNewPartyMemberFromPostArray(
                                        $_POST,
                                        $i,
                                        'infant'
                                    );
                                if ($member) {
                                    $booking->setPartyMember($member);
                                }
                            }

                            // Save party details
                            $booking->setPartyDetails();

                            if (array_key_exists('payment', $_POST) 
                                && $_POST['payment'] == '2'
                            ) {
                                redirect(
                                    WpTabsApi__getEndPointPermalink(
                                        $post->ID, 
                                        'booking/payment'
                                    )
                                );
                            } else {
                                
                                // Confirm booking if website mode is set to live
                                if (get_option('tabs_api_website_mode', 'Test') == 'Live') {
                                    $booking->confirmBooking();
                                } else {
                                    $booking->setConfirmation(true);
                                }
                                
                                if ($booking->isConfirmed()) {

                                    // Register hook for the cottage booking 
                                    // postprocessing
                                    do_action(
                                        'wpTabsApiBookingPostProcess', 
                                        $booking, 
                                        $property
                                    );

                                    // Redirect to booking complete page
                                    redirect(
                                        WpTabsApi__getEndPointPermalink(
                                            $post->ID, 
                                            'booking/complete'
                                        )
                                    );
                                }
                            }

                            // Any other response from confirmBooking with throw
                            // an exception
                        } catch(Exception $e) {
                            // Redirect to booking page with flash message
                            saveFlashMessage('warning', $e->getMessage());
                            redirect(
                                WpTabsApi__getEndPointPermalink(
                                    $post->ID, 
                                    'booking'
                                )
                            );
                        }
                    }
                }

                return array(
                    'property' => $property,
                    'booking' => $booking,
                    'bookingForm' => $bookingForm
                );

            } catch(ApiException $e) {
                // Catch any direct links
                $this->do404($e->getMessage());
            }
        }
    }

    /**
     * Booking Update Pre-processing function
     *
     * @param array $post Current post object
     *
     * @return array
     */
    public function preProcessBookingupdate($post)
    {
        if (!checkKeyExists($_SESSION, 'wp_tabs_api_booking_id')) {
            // Catch any direct links
            $this->do404();
        } else {
            try {
                // Get booking Object
                $booking = $this->getTabsApi()->createBookingFromId(
                    $_SESSION['wp_tabs_api_booking_id']
                );

                // Check that booking hasn't already been completed
                if ($booking->isConfirmed()) {
                    redirect(
                        WpTabsApi__getEndPointPermalink(
                            $post->ID, 
                            'booking/complete'
                        )
                    );
                }

                $property = $this->getTabsApi()->getPropertyFromId(
                    $post->post_excerpt
                );
                
                // Look for validation
                if (count($_POST) > 0) {
                    // TODO
                }
                
                redirect(
                    WpTabsApi__getEndPointPermalink(
                        $post->ID, 
                        'booking'
                    )
                );
            } catch(ApiException $e) {
                // Catch any direct links
                $this->do404($e->getMessage());
            }
        }
    }

    /**
     * Process the booking complete endpoint
     *
     * @param array $post Current post object
     *
     * @return array
     */
    public function preProcessBookingpayment($post)
    {
        if (!checkKeyExists($_SESSION, 'wp_tabs_api_booking_id')) {
            // Catch any direct links
            $this->do404('No booking session found');
        } else {
            try {
                // Get booking Object
                $booking = $this->getTabsApi()->createBookingFromId(
                    $_SESSION['wp_tabs_api_booking_id']
                );
                
                if ($booking->isConfirmed()) {

                    // Redirect to booking complete page
                    redirect(
                        WpTabsApi__getEndPointPermalink(
                            $post->ID, 
                            'booking/complete'
                        )
                    );
                }

                $sagePay = $this->getTabsApi()->getSagePayHelper(
                    get_option('tabs_api_sagepay_vendor'),
                    get_option('tabs_api_sagepay_mode'),
                    plugins_url(
                        WPTABSAPIPLUGINSLUG . DS . 
                        'sagepaycallback.php?bookingId=' . $booking->getBookingId()
                    )
                );

                // Optional, set credit card fee
                $ccFee = get_option('tabs_api_credit_card_charge', 0);
                if (is_numeric($ccFee) && $ccFee > 0) {
                    $sagePay->setCcCharge(number_format($ccFee, 2));
                }

                // Your next step is to create a transaction
                $response = $sagePay->buyDeferred(
                    $booking->getDepositAmount(),
                    sprintf(
                        'Web Booking for Property %s',
                        $booking->getPropertyRef()
                    ),
                    $booking->getCustomer(),
                    'WEB' . $booking->getPropertyRef . date('dmyhis')
                );

                switch ($response['Status']) {
                case 'OK':
                    break;
                default:
                    throw new Exception($response['StatusDetail']);
                    break;
                }

                return array(
                    'booking' => $booking,
                    'sagePay' => $response
                );
            } catch(Exception $e) {
                // Catch any direct links
                $this->do404($e->getMessage());
            }
        }
    }

    /**
     * Process the booking complete endpoint
     *
     * @return array
     */
    public function preProcessBookingcomplete()
    {
        if (!checkKeyExists($_SESSION, 'wp_tabs_api_booking_id')) {
            // Catch any direct links
            $this->do404('No booking session found');
        } else {
            try {
                // Get booking Object
                $booking = $this->getTabsApi()->createBookingFromId(
                    $_SESSION['wp_tabs_api_booking_id']
                );

                // Register hook for the cottage booking complete preprocessing
                do_action(
                    'wpTabsApiBookingCompletePreprocess', 
                    $booking
                );

                return array(
                    'booking' => $booking
                );
            } catch(ApiException $e) {
                // Catch any direct links
                $this->do404($e->getMessage());
            }
        }
    }
    
    /**
     * Preprocess cottage search function
     * 
     * @return void
     */
    public function preProcessCottageSearch()
    {
        // Look for a redirect endpoint
        $endPoint = assignArrayValue($_POST, 'redirectUrl', false);
        if (!$endPoint) {
            $endPoint = $_SERVER['HTTP_REFERER'];
        } else {
            unset($_POST['redirectUrl']);
        }
        
        if (array_key_exists('action', $_POST)) {
            unset($_POST['action']);
        }
        
        // Remove any blank filters to keep things tidy
        $_POST = array_filter($_POST);
        
        // Split off fragment
        if (stristr($endPoint, '#')) {
            list($endPoint, $fragment) = explode('#', $endPoint);
        }
        
        // Split off query
        $query = '';
        if (stristr($endPoint, '?')) {
            list($endPoint, $query) = explode('?', $endPoint);
            if (strlen($query) > 0) {
                $query = '&' . $query;
            }
        }
        
        // Create query string
        $redirectStr = $endPoint;
        if (count($_POST) > 0) {
            $redirectStr .= '?' . http_build_query($_POST);
        }
        if (strlen($query) > 0) {
            if (stristr($redirectStr, '?')) {
                $redirectStr .= $query;
            } else {
                $redirectStr .= '?' . $query;
            }
        }
        
        redirect(
            $redirectStr
        );
    }
    
    /**
     * Shortlist function. 
     *
     * @param object $post Global post object if the endpoint is being used
     * 
     * @return void
     */
    public function preProcessShortlist($post = null)
    {
        $property = null;
        if ($post) {
            $property = $this->getTabsApi()->getPropertyFromId(
                $post->post_excerpt
            );
        } else {
            if (assignArrayValue($_POST, 'propRef', false)) {
                $property = $this->getTabsApi()->getPropertyFromId(
                    $_POST['propRef'] . '_' . get_option('tabs_api_brandcode')
                );
            }
        }
        
        if ($property) {
            if ($property->isOnShortlist()) {
                $this->getTabsApi()->removeFromShortlist($property->getPropRef());
            } else {
                $this->getTabsApi()->addToShortlist($property->getPropRef());
            }
            $shortlist = $this->getTabsApi()->getShortlist();
        }
        
        if (isAjax()) {
            generateResponse(
                array(
                    'count' => count($shortlist),
                    'properties' => $shortlist
                )
            );
        } else {
            saveFlashMessage('ok', 'Added to Shortlist');
            if (isset($_SERVER['HTTP_REFERER'])) {
                redirect($_SERVER['HTTP_REFERER'], 'refresh', 204);
            } else {
                $post = $this->_getPageByName(
                    $this->_getCottageSlug($property),
                    $this->getCottagePostType()
                );
                if ($post) {
                    redirect(get_permalink($post->ID), 'refresh', 204);
                }
            }
        }
    }

    // ------------------------- Private functions ------------------------- //

    /**
     * Define constants
     *
     * @return void
     */
    private function _defineConstants()
    {
        define('WPTABSAPIPLUGIN_DIR', plugin_dir_path(__FILE__));
        define('WPTABSAPIPLUGINNAME', 'Tabs API Plugin');
        define('WPTABSAPIPLUGINSLUG', 'wp-tabs-api');
        define(
            'WPTABSAPIPLUGINSEARCHPREFIX', 
            get_option('wp_tabs_api_search_prefix', 'wp_')
        );
        define('WPTABSAPIPLUGIN_DEFAULTPAGE', 'tabs_api_home_page');
        define(
            'WPTABSAPIPLUGIN_COTTAGE_POST_TYPE_KEY', 
            'tabs_api_cottage_post_type'
        );
        define(
            'WPTABSAPIPLUGIN_OPT_EP_KEY', 
            'tabs_api_optional_enpoints'
        );
        define(
            'WPTABSAPIPLUGIN_SEARCHPAGE_OPT_EP_KEY', 
            'tabs_api_search_page'
        );
        define(
            'WPTABSAPIPLUGIN_SEARCHPAGE_OPT_FILTER_KEY', 
            'tabs_api_search_page_filter'
        );
        define(
            'WPTABSAPIPLUGIN_SEARCHPAGE_FILTER_SHORTLIST', 
            'tabs_api_search_shortlist_enabled'
        );
        define(
            'WPTABSAPIPLUGIN_SEARCHPAGE_NOPROPS_FOUND', 
            'tabs_api_search_noprops_found_text'
        );
        define(
            'WPTABSAPIPLUGIN_INDEXTIME', 
            'tabs_api_indextime'
        );
        define('DS', DIRECTORY_SEPARATOR);
    }

    /**
     * Remove all of the cottage pages from wordpress
     *
     * @return void
     */
    private function _removeAllCottageIndexes()
    {
        $cpt = $this->getCottagePostType(false);
        if ($cpt) {
            global $wpdb;
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM $wpdb->posts WHERE post_type = '%s'",
                    $cpt
                )
            );
        }
    }

    /**
     * Get plugin optional endpoints
     *
     * @return array
     */
    private function _getOptionalEndpoints()
    {
        $eps = get_option(WPTABSAPIPLUGIN_OPT_EP_KEY, '');
        if (strlen($eps) > 0) {
            return explode('_', $eps);
        }
        return array();
    }

    /**
     * Add a cottage index
     *
     * @param string $cottageIndex Cottage Index
     * @param string $cottageName  Cottage Name
     * @param string $cottageSlug  Cottage Slug
     *
     * @return mixed Integer if ok
     */
    private function _addCottageIndex($cottageIndex, $cottageName, $cottageSlug)
    {
        global $current_user;
        $cpt = $this->getCottagePostType();
        if ($cpt) {
            return wp_insert_post(
                array(
                    'post_title'     => $cottageName,
                    'post_type'      => $cpt,
                    'post_name'      => $cottageSlug,
                    'post_excerpt'   => $cottageIndex,
                    'post_author'    => $current_user->data->ID,
                    'post_status'    => 'publish',
                    'post_parent'    => 0,
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                    'post_content'   => ''
                ),
                true
            );
        }
        return false;
    }

    /**
     * Check to see if a post type page exists or not
     *
     * @param string $page     Page name to check
     * @param string $postType Custom post type
     *
     * @return boolean
     */
    private function _checkPostTypePageExists($page, $postType)
    {
        return $this->_getPageByName($page, $postType);
    }

    /**
     * Check to see a page exists and return if found
     *
     * @param string $pageName Page name to check
     * @param string $postType Custom post type
     *
     * @return mixed
     */
    private function _getPageByName($pageName, $postType)
    {
        global $wpdb;
        $page = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM $wpdb->posts WHERE 
                    post_name = '%s' AND post_type = '%s'",
                $pageName,
                $postType
            )
        );
        return $page;
    }

    /**
     * Check all of the cottages indexed in the db
     *
     * @param string $postType Custom post type
     *
     * @return mixed
     */
    private function _getIndexedCottageCount($postType)
    {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT count(ID) FROM $wpdb->posts WHERE post_type = '%s'",
                $postType
            )
        );
    }

    /**
     * Set the tabs api object
     *
     * @return void
     */
    private function _setTabsApiConnection()
    {
        $apiUrl = get_option('tabs_api_url');
        $apiUser = get_option('tabs_api_user');
        $apiSecret = get_option('tabs_api_secret');
        if (is_string($apiUrl) && strlen($apiUrl) > 0) {
            $this->tabsApi = new WpTabsApiWrapper($apiUrl, $apiUser, $apiSecret);
        }
    }

    /**
     * Function used to overwrite all of the property data in a string
     *
     * @param Property $property Property object
     * @param string   $str      String to replace property data eg 
     * {getName}-{getPropref}
     *
     * @return string
     */
    private function _replacePropertyData($property, $str)
    {
        preg_match_all('#\{[^}]*\}#s', $str, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if (isset($match[0])) {
                $method = str_replace('}', '', str_replace('{', '', $match[0]));
                $str = str_replace(
                    '{'.$method.'}', 
                    $property->$method(), 
                    $str
                );
            }
        }
        return $str;
    }

    /**
     * Return a cottage slug string
     * 
     * @param \tabs\api\property\Property $cottage Api Cottage object
     * 
     * @return string
     */
    private function _getCottageSlug($cottage)
    {
        return sanitize_title(
            $this->_replacePropertyData(
                $cottage, 
                $this->getCottageSlugFormat()
            )
        );
    }
    
    /**
     * Image cache function
     * 
     * @param string  $propRef  Property ref
     * @param string  $filename Filename to cache
     * @param string  $type     Image type
     * @param integer $width    Image width
     * @param integer $height   Image height
     * 
     * @return void
     */
    private function _cacheImageRequest(
        $propRef, 
        $filename = null,
        $type = 'tocc',
        $width = 100,
        $height = 100
    ) {
        try {
            $property = $this->getTabsApi()->getPropertyFromId(
                $propRef . '_' . get_option('tabs_api_brandcode', 'XX')
            );
            if ($property) {
                if (!$filename && $property->getMainImage()) {
                    $filename = $property->getMainImage()->getFilename();
                }

                $filename = $this->_getFilenameFromPath($filename);
                if ($filename) {
                    $images = $property->getImages();
                    $imageData = null;
                    foreach ($images as $image) {
                        $img = $this->_getFilenameFromPath($image->getFilename());
                        if ($img == $filename) {
                            $imageData = file_get_contents(
                                $image->createImageSrc($type, $width, $height)
                            );
                            break;
                        }
                    }
                    if ($imageData) {
                        $image = imagecreatefromstring($imageData);
                        $path = plugin_dir_path(__FILE__) . 'imagecache' . DS;
                        $path = sprintf(
                            '%s%s%s%s%s%s%s%s%s%s',
                            $path,    DS,
                            $propRef, DS,
                            $type,    DS,
                            $width,   DS,
                            $height,  DS                            
                        );
                        if ($this->_createFolder($path)) {
                            imagejpeg($image, $path . $filename, 100);
                        } else {
                            
                        }
                        if ($image) {
                            header('Last-Modified: '. gmdate('D, d M Y H:i:s', time() - 86400 * 365) . ' GMT', true, 200);
                            header('Expires: '. gmdate('D, d M Y H:i:s',  time() + 86400 * 7) . ' GMT', true, 200);
                            header('Content-type: image/jpeg');
                            header("x-generated-by: image handler");
                            imagejpeg($image, null, 100);
                        }
                        return;
                    }
                }
            }
        } catch (\RuntimeException $e) {
            // TODO, not available image?
        }
    }

    /**
     * Function to check and create a create a folder
     * 
     * @param string $path Destination path
     * 
     * @return boolean if folder found or created
     */
    private function _createFolder($path)
    {
        if (!is_dir(rtrim($path, '/') . '/')) {
            return mkdir(rtrim($path, '/') . '/', 0777, true);
        }
        return true;
    }
    
    /**
     * Get the filename from a give path, strip query string params etc
     * 
     * @param string $fileName Filename or path
     * 
     * @return string|boolean 
     */
    private function _getFilenameFromPath($fileName)
    {
        if ($fileName) {
            $img = parse_url($fileName);
            if (isset($img['path'])) {
                return $img['path'];
            }
        }
        return false;
    }
    
    /**
     * Return the variables from the mage cach url
     * 
     * @param string $path Path of image
     * 
     * @return array
     */
    private function _getImageCacheUrlVars($path)
    {
        $propRef = '';
        $fileName = null;
        $type = 'tocc';
        $width = 100;
        $height = 100;
        
        $path = explode('imagecache', $path, 2);        
        $path = explode('/', trim($path[1], '/'));
        
        switch (count($path)) {
        case 1:
        case 2:
        case 3:
        case 4:
            // TODO: image coming soon
            break;
        case 5:
            $propRef = $path[0];
            $type = $path[1];
            $width = $path[2];
            $height = $path[3];
            $fileName = end($path);
            break;
        }
        
        return array(
            'propRef' => $propRef,
            'filename' => $fileName,
            'type' => $type,
            'width' => $width,
            'height' => $height
        );
    }
    
    /**
     * Return an array of \aw\formfields\fields\Optgroup objects populated
     * with source code data.
     *
     * @return array
     */
    private function _getSourcecodesOptgroup()
    {
        $sourceOpts = array();
        $sourceCats = array();
        $sources = $this->getTabsApi()->getSourceCodesFull();
        foreach ($sources as $source) {
            if ($source->getCategory() == '') {
                $source->setCategory('Other');
            }
            $sourceCats[$source->getCategory()][] = $source;
        }
        
        foreach ($sourceCats as $cat => $sources) {
            $options = array();
            foreach ($sources as $source) {
                $options[$source->getDescription()] = $source->getCode();
            }
            $sourceOpts[] = \aw\formfields\fields\Optgroup::factory($cat, $options);
        }
        
        return $sourceOpts;
    }
}
