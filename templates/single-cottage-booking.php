<?php

/**
 * This endpoint is the main template for the booking page.  All updates will be
 * posted to the update-booking endpoint. 
 */
  
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

<p>About your booking:</p>
<?php

// Include the booking summary template
require_once 'booking-summary.php';

?>
<p>Fill in the form below to reserve your holiday.</p>


<div class="booking-form">
    <?php echo getFlashStatusMessage(); ?>
    <?php echo $bookingForm; ?>
</div>

<?php

get_footer();