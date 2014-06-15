<?php

/**
 * Plugin Name: WP Tabs API Plugin
 * Plugin URI: 
 * Description: A plugin built to create and manage a tabs api instance from within wordpress.
 * Version: 0.1
 * Author: Alex Wyett
 * Author URI: www.wyett.co.uk
 *
 * PHP Version 5.3
 * 
 * @category  WPTabsAPI
 * @package   Wordpress
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2013 Alex Wyett
 * @license   http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link      http://www.carltonsoftware.co.uk
 * 
 * 
 * This plugin has the following hooks available:
 * 
 * 1: add_action('wpTabsApiCottagePreprocess', 'yourCottageFunctionName');
 *      
 *  - This hook allows you to preprocess the cottage information prior to the
 *    page load.  The first argument of the function will be the tabs api
 *    property object. I.e.
 * 
 *      function yourCottageFunctionName($property) {}
 * 
 * 2: add_action('wpTabsApiEnquiryPreprocess', 'yourEnquiryFunctionName');
 * 
 *  - This hook allows you to capture the enquiry object before json output.
 *    It has two arguments.  The enquiry object and the json response array
 *    before encoding. I.e.
 * 
 *      function yourEnquiryFunctionName($enquiry, $response) {}
 * 
 * 3: add_action('wpTabsApiBookingPreprocess', 'yourBookingFunctionName');
 * 
 *  - This hook allows you to capture the booking object before the page renders.
 *    It has three objects in the array.  The $booking, $property and 
 *    $bookingForm objects. I.e.
 * 
 *      function yourBookingFunctionName($array) {}
 * 
 * 4: add_action('wpTabsApiBookingPostProcess', 'yourBookingFunctionName');
 * 
 *  - This hook allows you to capture the booking object after it is completed.
 *    Parameters, the booking and property objects.
 *      I.e.
 * 
 *          function yourBookingFunctionName($booking, $property) {}
 * 
 * 5: add_action('wpTabsApiBookingCompletePreprocess', 'yourBookingCompleteFunctionName');
 * 
 *  - This hook allows you to capture the booking object before the page renders
 *    and after the booking has been complete. It has one argument, the booking
 *    object. I.e.
 * 
 *      function yourBookingCompleteFunctionName($booking) {}
 * 
 * 6: add_action('wpTabsApiBrochurePostProcess', 'yourBrochureRequestFunctionName');
 * 
 *  - This hook allows you to capture the customer object after a brochure request
 *    has been submitted but before the redirect has occurred.  This will enable
 *    emails to be sent to the customer and add any tracking that
 *    may be required.
 * 
 *      function yourBookingCompleteFunctionName($customer) {}
 * 
 * 7: add_action('wpTabsApiOwnerPackPostProcess', 'yourOwnerPackRequestFunctionName');
 * 
 *  - This hook allows you to capture the owner object after an ownerpack request
 *    has been submitted but before the redirect has occurred.  This will enable
 *    emails to be sent to the customer and add any tracking that
 *    may be required.
 * 
 *      function yourBookingCompleteFunctionName($owner) {}
 * 
 * 8: add_action('wpTabsApiAjaxCottageSearch', 'yourAjaxSearchFunction');
 * 
 *  - This action is called if you submit a request to the admin-ajax script.
 *    The function should have one parameter which will be the returned search
 *    object.
 * 
 *      function yourAjaxSearchFunction($search) {}
 * 
 * 9: add_action('wpTabsApiBrochurePreprocess', 'yourBrochureModifyFunction');
 * 
 *  - This action enables the brochure form to be modified prior to it being
 *    rendered.
 * 
 *      function yourBrochureModifyFunction(&$brochureForm) {}
 * 
 * 10: add_action('wpTabsApiOwnerpackPreprocess', 'yourOwnerpackModifyFunction');
 * 
 *  - This action enables the ownerpack form to be modified prior to it being
 *    rendered.
 * 
 *      function yourOwnerpackModifyFunction(&$ownerPack) {}
 * 
 */

global $wpdb;
global $wpTabsApi;

/**
 * Include helper functions to global use
 */
require 'helpers/common.php';

/**
 * Include formFields bootstrap loader
 */
require_once 'libraries/aw-form-fields/autoload.php';

/**
 * Include the tabs api 
 */
require_once 'libraries/tabs-api-client/autoload.php';

/**
 * Require Tabs Api Class 
 */
require_once 'WpTabsApi.class.php';

$wpTabsApi = new WpTabsApi(__FILE__);


// Global functions
if (!function_exists('WpTabsApi__getSearchPageContent')) {
    /**
     * Search page content.  Allows easy integration of search page output
     * into a pre-existing theme.
     * 
     * @return string 
     */
    function WpTabsApi__getSearchPageContent()
    {
        global $wpTabsApi;
        global $post;
        $content = '';
        if (!empty($post) && isset($post->tabs_api_search)) {
            $search = $post->tabs_api_search;

            if ($search) {
                if ($search->getProperties()) {
                    $content .= sprintf(
                        '<p class="searchInfo">Showing %s %s</p>', 
                        $search->getSearchInfo(),
                        $search->getLabel()
                    );

                    $content .= $search->getPaginationLinks();
                    foreach ($search->getProperties() as $property) {
                        ob_start();
                        // Use get_stylesheet_directory() in case childtheme is being used.
                        if (is_file(get_stylesheet_directory() . DS . 'single-cottage-row.php')) {
                            include get_stylesheet_directory() . DS . 'single-cottage-row.php';
                        } else {
                            include 'templates/single-cottage-row.php';
                        }
                        $content .= ob_get_clean();
                    }
                } else {
                    // No properties found text
                    $noPropsText = get_post_meta(
                        $post->ID, 
                        WPTABSAPIPLUGIN_SEARCHPAGE_NOPROPS_FOUND, 
                        true
                    );
                    
                    if ($noPropsText && $noPropsText != '') {
                        $content .= $noPropsText;
                    } else {
                        $content .= 'No properties found';
                    }
                }
            } else {
                $content .= 'No properties found';
            }
        }
        
        return $content;
    }
}


if (!function_exists('WpTabsApi__getPropertySummary')) {
    /**
     * Search page content.  Allows easy integration of search page output
     * into a pre-existing theme.
     * 
     * @return string 
     */
    function WpTabsApi__getPropertySummary()
    {
        global $wpTabsApi;
        global $post;
        $content = '';
        if (!empty($post) && isset($post->post_excerpt)) {
            $propRef = $post->post_excerpt;
            $property = $wpTabsApi->getTabsApi()->getPropertyFromId($propRef);
            if ($property) {
                ob_start();
                // Use get_stylesheet_directory() in case childtheme is being used.
                if (is_file(get_stylesheet_directory() . DS . 'single-cottage-summary.php')) {
                    include get_stylesheet_directory() . DS . 'single-cottage-summary.php';
                } else {
                    include 'templates/single-cottage-summary.php';
                }
                $content .= ob_get_clean();
            }
        }
        
        return $content;
    }
}


if (!function_exists('WpTabsApi__getEndPointPermalink')) {
    /**
     * Return a permalink of a post with an endpoint attached
     * 
     * @return string 
     */
    function WpTabsApi__getEndPointPermalink($postId, $permalink = '')
    {
        $permalink = trim($permalink, '/');
        $rw = new WP_Rewrite();
        if ($rw->using_permalinks()) {
            if ($permalink == '') {
                return get_permalink($postId);
            } else {
                return get_permalink($postId) . $permalink . '/';
            }
        } else {
            if (stristr($permalink, '/')) {
                $permalink = explode('/', $permalink, 2);
                $permalink = $permalink[0] . '=' . $permalink[1];
            }
            return get_permalink($postId) . '&' . $permalink;
        }
    }
}


if (!function_exists('WpTabsApi__getImageCache')) {
    /**
     * Return an image cache path
     * 
     * @return string 
     */
    function WpTabsApi__getImageCache(
        $propRef, 
        $filename, 
        $type = 'tocc', 
        $width = 100, 
        $height = 100
    ) {
        $img = parse_url($filename);
        if (isset($img['path'])) {
            $filename = $img['path'];
        }
        return sprintf(
            plugins_url(WPTABSAPIPLUGINSLUG . '/imagecache/%s/%s/%s/%s/%s'),
            $propRef,
            $type,
            $width,
            $height,
            $filename
        );
    }
}