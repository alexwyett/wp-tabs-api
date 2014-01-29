<?php
/**
 * This endpoint is the main template for the booking complete page.
 * It just acts as a summary for the successful booking. 
 */

add_action('wp_footer', 'addJs');
function addJs() {
    echo '<script type="text/javascript">
        if (top.location!= self.location) {
            top.location = self.location.href
        }
    </script>';
}
  
// Queue property css and javascript.  Again these are just
// example asset files but could be used as a basis for your
// theme.
WpTabsApiAdmin::enqueueCss(WPTABSAPIPLUGINNAME, 'property.css');


// Wordpress header.  All of the wordpress functions can be used here just like
// any other wordpress template.
get_header();

// Wordpress loop which includes the property object from the tabs api
require_once 'cottage-loop.php';

?>

<p><?php echo $booking->getCustomer(); ?>! Thank you for your booking!</p>
<?php
    if ($booking->getAmountPaid() > 0) {
        echo sprintf(
            '<p>Thank you for your payment of &pound;%s.  We will process your booking shortly.</p>',
            number_format($booking->getAmountPaid(), 2)
        );
    }
?>
<p>The details are:</p>
<?php

// Include the booking summary template
require_once 'booking-summary.php';

?>
<p>Please use W<?php echo $booking->getWNumber(); ?> as your reference if you wish to contact us.</p>

<?php

get_footer();