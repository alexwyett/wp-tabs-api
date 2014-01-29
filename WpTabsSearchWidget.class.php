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
        
        $form = new \AW\Forms\Form(
            array(
                'method' => 'post', 
                'action' => admin_url('admin-ajax.php')
            ),
            $search->getInitialParams()
        );
        
        // Set form template
        $form->setTemplate('<form{implodeAttributes}>{renderFieldSets}</form>');
        
        $fieldSet = new \AW\Forms\FormFields\Fieldset();
        $fieldSet->setLegend('Search for Cottages');
        
        // Set uri hidden field
        $fieldSet->setField(
            new \AW\Forms\FormFields\HiddenField(
                'redirectUrl', 
                array('value' => $uri)
            )
        );
        
        // Set uri hidden field
        $fieldSet->setField(
            new \AW\Forms\FormFields\HiddenField(
                'action', 
                array('value' => 'cottagesearch')
            )
        );
        
        // Create new textfield for cottage name search
        $field = new \AW\Forms\FormFields\TextField(
            $search->getSearchPrefix() . 'name'
        );
        $field->setWrapper(new \AW\Forms\FormFields\Wrapper());
        $field->createLabel('Name');
        $fieldSet->setField($field);
        
        // Create new area select box
        $field = new \AW\Forms\FormFields\SelectField(
            $search->getSearchPrefix() . 'area',
            array_merge(
                array('Any Area' => ''),
                $this->getTabsApi()->getAreasInverse()
            )
        );
        $field->setWrapper(new \AW\Forms\FormFields\Wrapper());
        $field->createLabel('Area');
        $fieldSet->setField($field);
        
        // Create new location select box
        $field = new \AW\Forms\FormFields\SelectField(
            $search->getSearchPrefix() . 'location',
            array_merge(
                array('Any Location' => ''),
                $this->getTabsApi()->getLocationsArray()
            )
        );
        $field->setWrapper(new \AW\Forms\FormFields\Wrapper());
        $field->createLabel('Location');
        $fieldSet->setField($field);
        
        // Create new textfield for arrival date
        $field = new \AW\Forms\FormFields\TextField(
            $search->getSearchPrefix() . 'fromDate',
            array('class' => 'dtp')
        );
        $field->setWrapper(new \AW\Forms\FormFields\Wrapper());
        $field->createLabel('From');
        $fieldSet->setField($field);
        
        // Create new nights select box
        $field = new \AW\Forms\FormFields\SelectField(
            $search->getSearchPrefix() . 'nights',
            array(
                'Any' => '',
                '2 nights' => 2,
                '3 nights' => 3,
                '4 nights' => 4,
                '5 nights' => 5,
                '6 nights' => 6,
                '7 nights' => 7,
                '8 nights' => 8,
                '9 nights' => 9,
                '10 nights' => 10,
                '11 nights' => 11,
                '12 nights' => 12,
                '13 nights' => 13,
                '14 nights' => 14,
                '14 nights' => 14,
                '15 nights' => 15,
                '16 nights' => 16,
                '17 nights' => 17,
                '18 nights' => 18,
                '19 nights' => 19,
                '20 nights' => 20,
                '21 nights' => 21,
                '22 nights' => 22,
                '23 nights' => 23,
                '24 nights' => 24,
                '25 nights' => 25,
                '26 nights' => 26,
                '27 nights' => 27,
                '28 nights' => 28
            )
        );
        $field->setWrapper(new \AW\Forms\FormFields\Wrapper());
        $field->createLabel('Nights');
        $fieldSet->setField($field);
        
        // Create new accommodates select box
        $field = new \AW\Forms\FormFields\SelectField(
            $search->getSearchPrefix() . 'accommodates',
            array(
                'Any' => '',
                2  => 2,
                3  => 3,
                4  => 4,
                5  => 5,
                6  => 6,
                7  => 7,
                8  => 8,
                9  => 9,
                "10+" => ">10"
            )
        );
        $field->setWrapper(new \AW\Forms\FormFields\Wrapper());
        $field->createLabel('Sleeping');
        $fieldSet->setField($field);
        
        // Create new pets checkbox box
        $field = new \AW\Forms\FormFields\CheckBoxField(
            $search->getSearchPrefix() . 'pets',
            array_key_exists(
                $search->getSearchPrefix() . 'pets',
                $form->getFormValues()
            ),
            array(
                'id' => $search->getSearchPrefix() . 'pets',
                'value' => 'true'
            )
        );
        $field->setWrapper(new \AW\Forms\FormFields\Wrapper());
        $field->createLabel('Pets?');
        $fieldSet->setField($field);
        
        // Create new order by field
        $field = new \AW\Forms\FormFields\SelectField(
            $search->getSearchPrefix() . 'orderBy',
            array(
                'Any' => '',
                'Price low to high' => 'price_asc',
                'Price high to low' => 'price_desc',
                'Sleeps low to high' => 'accom_asc',
                'Sleeps high to low' => 'accom_desc',
                'Bedrooms low to high' => 'bedrooms_asc',
                'Bedrooms high to low' => 'bedrooms_desc',
            )
        );
        $field->setWrapper(new \AW\Forms\FormFields\Wrapper());
        $field->createLabel('Order');
        $fieldSet->setField($field);
        
        // Add fieldset to form
        $form->setFieldset($fieldSet);
        
        // Add new Submit button and field
        $fieldSet = new \AW\Forms\FormFields\Fieldset(array('class' => 'actions'));
        $fieldSet->setTemplate('<div{implodeAttributes}>{renderFields}</div>');
        $submit = new \AW\Forms\FormFields\SubmitField(array('value' => 'Search'));
        $fieldSet->setField($submit);
        $form->setFieldset($fieldSet);
        
        echo $form;
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