<?php

/**
 * WP Tabs Api Search Widget Class
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
 * Require the api wrapper class
 */
require_once 'WpTabsApiWrapper.class.php';

/**
 * WP Tabs Api Search Widget Class
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
class WpTabsSearchWidget extends WP_Widget
{
    /**
     * Tabs api wrapper
     *
     * @var WpTabsApiWrapper
     */
    protected $tabsApi;
    
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct(__CLASS__, 'Tabs Quick Search Widget');
    }
    
    /**
     * Form override
     * 
     * @param array $instance Widget instance
     * 
     * @return void
     */
    public function form($instance)
    {
        // Check values
        if ($instance) {
            $searchPageUri = esc_attr($instance['wp_tabs_search_page']);
        } else {
            $searchPageUri = '';
        }
        ?>
        <p>
        <label for="<?php echo $this->get_field_id('wp_tabs_search_page'); ?>">
            <?php _e('Search Page URI', 'wp_widget_plugin'); ?>
        </label>
        <?php
        
        $pages = get_pages(array('post_type' => 'page'));
        $pageArray = array(get_home_url() => 'Home Page');
        foreach ($pages as $page) {
            $pageArray[" " . $page->ID] = $page->post_title;
        }
        echo getInputField(
            $this->get_field_name('wp_tabs_search_page'), 
            $searchPageUri, 
            'select',
            'class="widefat"',
            $pageArray
        );
        
        ?>
        </p>
        <?php
    }
    
    /**
     * update widget
     * 
     * @param array $new_instance Wordpress widget instance
     * @param array $old_instance Wordpress widget instance
     * 
     * @return array
     */ 
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['wp_tabs_search_page'] = strip_tags(
            $new_instance['wp_tabs_search_page']
        );
        return $instance;
    }
    
    /**
     * Widget helper function.  Handles the output of the widget
     * 
     * @param array $args     Widget settings
     * @param array $instance ???
     * 
     * @return void
     */
    public function widget($args, $instance)
    {
        global $post;
        $search = false;
        if (!empty($post) && isset($post->tabs_api_search)) {
            $search = $post->tabs_api_search;
        }
        
        // Setup the api connection
        $this->_setTabsApiConnection();
        
        // If there isn't a global search object available, create a new one
        if (!$search) {
            $search = $this->getTabsApi()->getSearchHelper(
                $_GET, 
                array(), 
                ''
            );

            // Set search prefix
            $search->setSearchPrefix(
                WPTABSAPIPLUGINSEARCHPREFIX
            );
            $search->search();
        }

        // Get the url for the form submission
        $uri = empty($instance['wp_tabs_search_page']) ? '' : $instance['wp_tabs_search_page'];
        if (!stristr('http:', $uri)) {
            $uri = get_permalink(trim($uri));
        }
        
        // New search form
        $form = $this->getTabsApi()->getQuicksearchForm(
            array(
                'method' => 'post', 
                'action' => admin_url('admin-ajax.php')
            ),
            $search->getInitialParams()
        );
        
        // Set uri hidden field
        $form->addChild(
            new \aw\formfields\fields\HiddenInput(
                'redirectUrl', 
                array('value' => $uri)
            )
        );
        
        // Set uri hidden field
        $form->addChild(
            new \aw\formfields\fields\HiddenInput(
                'action', 
                array('value' => 'cottagesearch')
            )
        );
        
        echo $form->mapValues();
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
     * Get the tabs api object
     *
     * @return WpTabsApiWrapper
     */
    public function getTabsApi()
    {
        return $this->tabsApi;
    }
}