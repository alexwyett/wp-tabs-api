<?php

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
class WPTabsApiAdmin
{
    /**
     * Pages array
     *
     * @var array
     */
    public static $pages = array(
        'tabs_api_property_update' => 'Property Sync',
        'tabs_api_options' => 'Plugin Options',
    );
    
    /**
     * Static filter settings 
     */
    static $filterSettings = array(
        array(
            'label' => 'Sleeping:',
            'filter' => 'accommodates',
            'controls' => array(
                array(
                    'name' => 'accommodation_operand',
                    'type' => 'select',
                    'values' => array('' => '=', '>', '<')
                ),
                array(
                    'name' => 'accommodation_filter',
                    'type' => 'select',
                    'values' => array(
                        '' => 'Any', 
                        '2', 
                        '3', 
                        '4', 
                        '5', 
                        '6', 
                        '7', 
                        '8', 
                        '9', 
                        '10'
                    )
                )
            )
        ),
        array(
            'label' => 'Bedrooms:',
            'filter' => 'bedrooms',
            'controls' => array(
                array(
                    'name' => 'bedrooms_operand',
                    'type' => 'select',
                    'values' => array('' => '=', '>', '<')
                ),
                array(
                    'name' => 'bedrooms_filter',
                    'type' => 'select',
                    'values' => array(
                        '' => 'Any', 
                        '2', 
                        '3', 
                        '4', 
                        '5'
                    )
                )
            )
        ),
        array(
            'label' => 'Pets?',
            'filter' => 'pets',
            'controls' => array(
                array(
                    'name' => 'pets_checkbox',
                    'type' => 'checkbox',
                    'values' => 'true'
                )
            )
        ),
        array(
            'label' => 'From:',
            'filter' => 'fromDate',
            'controls' => array(
                array(
                    'name' => 'fromDate',
                    'type' => 'dateSelect',
                    'values' => array()
                )
            )
        ),
        array(
            'label' => 'To:',
            'filter' => 'toDate',
            'controls' => array(
                array(
                    'name' => 'toDate',
                    'type' => 'dateSelect',
                    'values' => array()
                )
            )
        ),
        array(
            'label' => 'Duration:',
            'filter' => 'nights',
            'controls' => array(
                array(
                    'name' => 'nights',
                    'type' => 'select',
                    'values' => array(
                        '' => 'Any',
                        '7' => '1 Week',
                        '14' => '2 Weeks',
                        '21' => '3 Weeks',
                        '28' => '4 Weeks',
                        '2' => '2 Days',
                        '3' => '3 Days',
                        '4' => '4 Days',
                        '5' => '5 Days',
                        '6' => '6 Days',
                        '8' => '8 Days',
                        '9' => '9 Days',
                        '10' => '10 Days',
                        '11' => '11 Days',
                        '12' => '12 Days',
                        '13' => '13 Days',
                        '15' => '15 Days',
                        '16' => '16 Days',
                        '17' => '17 Days',
                        '18' => '18 Days',
                        '19' => '19 Days',
                        '21' => '21 Days',
                    )
                )
            )
        )
    );
    
    /**
     * Backend header
     * 
     * @return string
     */
    public function adminHeader()
    {
        return sprintf(
            "<div class='wrap'>
                <h2>Tabs API Wordpress Plugin</h2>
                %s",
            getFlashStatusMessage()
        );
    }

    /**
     * Backend footer
     *
     * @return string
     */
    public function adminFooter()
    {
        return "</div>";
    }

    /**
     * Admin menu generation
     *
     * @return void
     */
    public static function adminMenu()
    {
        $instance = new WPTabsApiAdmin();
        $parent   = WPTABSAPIPLUGIN_DEFAULTPAGE;
        $min_capability   = 'edit_pages';   // The wordpress capability, 
                                            // plugin required at league edit 
                                            // rights to function
                                            
        if (function_exists('add_menu_page')) {
            add_menu_page(
                WPTABSAPIPLUGINNAME . ' - Home',
                WPTABSAPIPLUGINNAME,
                $min_capability,
                $parent,
                array($instance, 'adminPage')
            );
        }

        if (function_exists('add_submenu_page')) {
            add_submenu_page(
                $parent,
                WPTABSAPIPLUGINNAME . ' - Home',
                'Settings',
                $min_capability, 
                $parent,
                array($instance, 'adminPage')
            );
            
            if (self::hasSettings()) {
                foreach (self::getPages() as $filename => $page) {
                    add_submenu_page(
                        $parent,
                        $page,
                        $page,
                        $min_capability, 
                        $filename,
                        array($instance, 'adminPage')
                    );
                }
            }
        }
    }
    
    /**
     * Get Pages
     *
     * @return array
     */
    public function getPages()
    {
        return self::$pages;
    }
    
    /**
     * Test if the api url has been entered or not
     *
     * @return boolean
     */
    public static function hasSettings()
    {
        $apiUrl = get_option('tabs_api_url');
        return (is_string($apiUrl) && strlen($apiUrl) > 0);
    }

    /**
     * Add the admin styles
     *
     * @return void
     */
    public static function printAdminStyles()
    {
        wp_register_style(
            'jquery-ui-css', 
            'http://ajax.googleapis.com/ajax/libs/jquery
                ui/1.8/themes/base/jquery-ui.css'
        );
        wp_enqueue_style('jquery-ui-css');
    }

    /**
     * Add the admin scripts
     *
     * @return void
     */
    public static function printAdminScripts()
    {
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker');
    }
    
    /**
     * Queue css files ready for output
     * 
     * @param string $queueName The Name of your queue item
     * @param string $script    CSS filename
     * 
     * @return void
     */
    public static function enqueueCss($queueName, $script)
    {
        wp_register_style(
            $queueName, 
            plugins_url(WPTABSAPIPLUGINSLUG . '/assets/css/' . $script)
        );
        wp_enqueue_style($queueName);
    }
    
    /**
     * Queue javascript files ready for output
     * 
     * @param string $queueName The Name of your queue item
     * @param string $script    Script filename
     * 
     * @return void
     */
    public static function enqueueJs($queueName, $script)
    {
        wp_register_script(
            $queueName, 
            plugins_url('/assets/js/' . $script, __FILE__), 
            array('jquery'), 
            false, 
            true
        );
        wp_enqueue_script($queueName);
    }

    /**
     * Backend pages handler
     *
     * @return string
     */
    public function adminPage()
    {
        // Current user wp global
        global $current_user;

        // JS must be enabled to use properly WPTabsApi...
        _e('<noscript>Javascript must be enabled.</noscript>', 'WPTabsApi');

        // Page Header
        echo self::adminHeader();
        
        echo getFlashStatusMessage();

        // Get the admin page
        $admin_page = trim($_GET['page']);
        if (in_array($admin_page, array_keys(self::getPages()))) {
            $curPath = admin_url('admin.php?page=' . $admin_page);
            include_once WPTABSAPIPLUGIN_DIR . 
                "pages" . DS . "{$admin_page}.php";
        } else {
            $curPath = admin_url('admin.php?page=tabs_api_home_page');
            include_once WPTABSAPIPLUGIN_DIR . 
                "pages" . DS . "tabs_api_home_page.php";
        }

        // Page Footer
        echo self::adminFooter();
    }
    
    /**
     * Add meta boxes to admin interface
     * 
     * @return void
     */
    public static function getMetaBoxes()
    {
        add_meta_box(
            'wp_tabs_api_make_search_page',
            WPTABSAPIPLUGINNAME,
            array(
                'WpTabsApiAdmin',
                'displaySearchPageOptions'
            ),
            'page', 'normal', 'high'
        );
    }
    
    /**
     * Set meta boxes to db
     * 
     * @param integer $pageId Wordpress Page ID
     * @param object  $page   Wordpress page object
     * 
     * @return void
     */
    public static function setMetaBoxes($pageId, $page)
    {
        // Check post type is a page
        if ($page->post_type == 'page') {
            $hardCbs = array(
                WPTABSAPIPLUGIN_SEARCHPAGE_FILTER_SHORTLIST,
                WPTABSAPIPLUGIN_SEARCHPAGE_OPT_EP_KEY
            );
            foreach ($hardCbs as $cbV) {
                $value = 0;
                // Store data in post meta table if present in post data
                if (isset($_POST[$cbV])) {
                    $value = 1;
                }
            
                update_post_meta(
                    $pageId, 
                    $cbV, 
                    $value
                );
            }
            
            $noProps = '';
            if (isset($_POST[WPTABSAPIPLUGIN_SEARCHPAGE_NOPROPS_FOUND])) {
                $noProps = $_POST[WPTABSAPIPLUGIN_SEARCHPAGE_NOPROPS_FOUND];
            }
            
            update_post_meta(
                $pageId, 
                WPTABSAPIPLUGIN_SEARCHPAGE_NOPROPS_FOUND, 
                $noProps
            );
            
            foreach (self::getFilterSettings() as $filter) {
                foreach ($filter['controls'] as $control) {
                    $val = '';
                    // Store data in post meta table if present in post data
                    if (isset($_POST['tabs_api_' . $control['name']])) {
                        $val = $_POST['tabs_api_' . $control['name']];
                    }

                    update_post_meta(
                        $pageId, 
                        'tabs_api_' . $control['name'], 
                        $val
                    );
                }
            }
        }
    }
    
    /**
     * Add the meta boxes to the current page
     * 
     * @param object $page Current admin page
     * 
     * @return void
     */
    public static function displaySearchPageOptions($page)
    {
        // Retrieve current page meta information 
        $searchPageEnabled = get_post_meta(
            $page->ID, 
            WPTABSAPIPLUGIN_SEARCHPAGE_OPT_EP_KEY, 
            true
        );
        
        // Retrieve shortlist option
        $shortlistEnabled = get_post_meta(
            $page->ID, 
            WPTABSAPIPLUGIN_SEARCHPAGE_FILTER_SHORTLIST, 
            true
        );
        
        // No properties found text
        $noPropsText = get_post_meta(
            $page->ID, 
            WPTABSAPIPLUGIN_SEARCHPAGE_NOPROPS_FOUND, 
            true
        );
        
        ?>
            <table>
                <tr>
                    <td style="width: 200px;">Enable Cottage listings?</td>
                    <td><?php 
                            echo getInputField(
                                WPTABSAPIPLUGIN_SEARCHPAGE_OPT_EP_KEY,
                                $searchPageEnabled,
                                'checkbox'
                            );
                        ?>
                    </td>
                </tr>
        <?php
        if ($searchPageEnabled == '1') {
            ?>
            <tr>
                <td colspan="2" style="padding-top: 20px; font-weight: bold;">
                    Use the following option if you only which to display
                    shortlisted properties.
                </td>
            </tr>
            <tr>
                <td>
                    Only Show Shortlisted Properties?
                </td>
                <td>
                    <?php
                        echo getInputField(
                            WPTABSAPIPLUGIN_SEARCHPAGE_FILTER_SHORTLIST,
                            $shortlistEnabled,
                            'checkbox'
                        );
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 20px; font-weight: bold;">
                    The following text can be added to display a message to the
                    visitor when no properties have been found in their search.
                </td>
            </tr>
            <tr>
                <td valign="top" style="padding-top: 30px;">
                    No properties found text
                </td>
                <td>
                    <?php
                        wp_editor(
                            $noPropsText, 
                            WPTABSAPIPLUGIN_SEARCHPAGE_NOPROPS_FOUND, 
                            array(
                                "media_buttons" => true,
                                'teeny' => false,
                                "textarea_rows" => 10
                            )
                        );
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 20px; font-weight: bold;">
                    The following controls can be set to fix certain
                    property filters.
                </td>
            </tr>
            <?php
            foreach (self::getFilterSettings() as $filter) {
                $controls = '';
                foreach ($filter['controls'] as $control) {
                    $filterVal = get_post_meta(
                        $page->ID, 
                        'tabs_api_' . $control['name'], 
                        true
                    );
                    
                    if ($control['type'] == 'dateSelect') {
                        $control['values'] = self::_getDatesArray();
                        $control['type'] = 'select';
                    }
                    
                    $controls .= getInputField(
                        'tabs_api_' . $control['name'],
                        $filterVal,
                        $control['type'],
                        '',
                        $control['values']
                    );
                }

                echo sprintf(
                    '<tr>
                        <td>%s</td>
                        <td>%s</td>
                    </tr>',
                    $filter['label'],
                    $controls
                );
            }
        }
        ?>
            </table>
        <?php
    }
    
    /**
     * Get an array of settings for the meta boxes
     * 
     * @return array
     */
    public static function getFilterSettings()
    {
        global $wpTabsApi;
        $settings = self::$filterSettings;
        
        if (isset($wpTabsApi)) {
            // Add tabs areas
            array_push(
                $settings,
                array(
                    'label' => 'Area:',
                    'filter' => 'area',
                    'controls' => array(
                        array(
                            'name' => 'area_filter',
                            'type' => 'select',
                            'values' => array_merge(
                                array('' => 'Any'),
                                $wpTabsApi->getTabsApi()->getAreas()
                            )
                        )
                    )
                )
            );
            
            // Add tabs Locations
            array_push(
                $settings,
                array(
                    'label' => 'Location:',
                    'filter' => 'location',
                    'controls' => array(
                        array(
                            'name' => 'location_filter',
                            'type' => 'select',
                            'values' => array_merge(
                                array('' => 'Any'),
                                $wpTabsApi->getTabsApi()->getLocations()
                            )
                        )
                    )
                )
            );
        }
        
        return $settings;
    }
    
    /**
     * Get an array of dates
     * 
     * @return array
     */
    private static function _getDatesArray()
    {
        $dates = array('' => 'Any');
        for ($i = mktime(0, 0, 0, 1, 1, date('Y'));
            $i <= mktime(0, 0, 0, 12, 31, date('Y') + 1);
            $i = $i + 86400
        ) {
            $dates[date('d-m-Y', $i)] = date('d F Y', $i);
        }
        return $dates;
    }
}