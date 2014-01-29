<?php

/**
 * Sagepay call back file
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
 */

define('WP_USE_THEMES', false);
require_once '../../../wp-blog-header.php';
header("HTTP/1.0 200 OK");

global $wpdb;

/**
 * Include helper functions to global use
 */
require 'helpers/common.php';

/**
 * Require Tabs Api Class 
 */
require_once 'WpTabsApi.class.php';

$wpTabsApi = new WpTabsApi('wp-tabs-api-plugin.php');

if (assignArrayValue($_GET, 'bookingId', false)) {
    // Get booking Object
    $booking = $wpTabsApi->getTabsApi()->createBookingFromId(
        $_GET['bookingId']
    );
    
    if ($booking) {
        $property = $wpTabsApi->getTabsApi()->getPropertyFromId(
            $booking->getPropertyRef() . '_' . get_option('tabs_api_brandcode')
        );
        
        $payment = $booking->processSagepayResponse($_POST);
        
        if ($payment->getStatus() == 'OK') {
            // Confirm booking
            $booking->confirmBooking();

            // Register hook for the cottage booking 
            // postprocessing
            do_action(
                'wpTabsApiBookingPostProcess', 
                $booking, 
                $property
            );
        }
        
        die(
            $payment->sagePayPaymentAcknowledgement(
                $wpTabsApi->getCottagePermalink($property) . 'booking/complete',
                $wpTabsApi->getCottagePermalink($property) . 'booking/error'
            )
        );
    }
}

?>
Status=ERROR
StatusDetail=There was a problem processing your payment
RedirectURL=<?php echo home_url('/404'); ?>