<?php

/**
 * This endpoint is the main template for the payment page.  
 */

// Wordpress header.  All of the wordpress functions can be used here just like
// any other wordpress template.
get_header();

// Wordpress loop which includes the property object from the tabs api
require_once 'cottage-loop.php';

?>

<p>Please fill in the payment form below to complete your booking.  The details are:</p>

<?php

// Include the booking summary template
require_once 'booking-summary.php';

echo sprintf(
    '<iframe src="%s" width="%s" height="600" id="sagePayFrame"></iframe>',
    $sagePay['NextURL'],
    '100%'
);

get_footer();