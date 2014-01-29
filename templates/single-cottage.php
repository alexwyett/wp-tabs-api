<?php
/**
 * The Template for displaying all single cottage posts.
 * 
 * THIS IS AN EXAMPLE FILE OF HOW THE TABS API PLUGIN CAN BE USED
 */
  
// Queue property css and javascript.  Again these are just
// example asset files but could be used as a basis for your
// theme.
WpTabsApiAdmin::enqueueCss(WPTABSAPIPLUGINNAME, 'property.css');

add_action(
    "wp_enqueue_scripts", 
    function () {
        WPTabsApiAdmin::enqueueJs(
            WPTABSAPIPLUGINNAME . 'jQuery.availabilityCalendar.js',
            'jQuery.availabilityCalendar.js'
        );
        WPTabsApiAdmin::enqueueJs(
            WPTABSAPIPLUGINNAME . 'property.js',
            'property.js'
        );

        wp_deregister_script('jquery');
        wp_register_script(
            'jquery', 
            '//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js',
            false,
            null
        );
        wp_enqueue_script('jquery');
    }, 
    11
);
    
// We'll need these links to build our availability calendar form
$bookingUrl = WpTabsApi__getEndPointPermalink($post->ID, 'booking/create');

// Wordpress header.  All of the wordpress functions can be used here just like
// any other wordpress template.
get_header();

// Wordpress loop which includes the property object from the tabs api
require_once 'cottage-loop.php';
?>                 
                    
<!-- Property Calendar -->
<div class="property-calendar">
    <form class="availability-form clear" id="enquiry-form" data-enquiry-url="<?php echo site_url('wp-admin/admin-ajax.php'); ?>" action="<?php echo $bookingUrl; ?>" method="post">
        <!-- Hidden variables -->
        <input name="action" value="enquiry" type="hidden">
        <input id="fromDate" name="fromDate" type="hidden">
        <input id="toDate" name="toDate" type="hidden">
        <input name="propRef" type="hidden" value="<?php echo $property->getPropref(); ?>">
        <input name="brandCode" type="hidden" value="<?php echo $property->getBrandcode(); ?>">
        <!-- /Hidden variables -->

        <fieldset>
            <div class="row clear">
                <label for="nights">Number of Nights:</label>
                <select id="nights" name="nights">
<?php 
foreach (range(2, 28) as $r) {
    echo sprintf(
        '<option value="%s"%s>%s nights</option>',
        $r,
        (($r == 7) ? ' selected' : ''),
        $r
    );
}
?>
                </select>
            </div>
            <div class="row clear">
                <label for="adults">Adults:</label>
                <select id="adults" name="adults">
<?php 
foreach (range(1, $property->getAccommodates()) as $r) {
    echo sprintf(
        '<option value="%s">%s</option>',
        $r,
        $r
    );
}
?>
                </select>
            </div>
            <div class="row clear">
                <label for="children">Children: <span>(3 to 17yrs)</span></label>
                <select id="children" name="children">
                    <option value="0">0</option>
<?php 
foreach (range(1, $property->getAccommodates()) as $r) {
    echo sprintf(
        '<option value="%s">%s</option>',
        $r,
        $r
    );
}
?>
                </select>
            </div>
            <div class="row clear">
                <label for="infants">Infants: <span>(Under 2yrs)</span></label>
                <select id="infants" name="infants">
                    <option value="0">0</option>
<?php 
foreach (range(1, $property->getAccommodates()) as $r) {
    echo sprintf(
        '<option value="%s">%s</option>',
        $r,
        $r
    );
}
?>
                </select>
            </div>
<?php
if ($property->hasPets()) {
    ?>
        <div class="row clear">
            <label for="pets">Pets:</label>
            <select id="pets" name="pets">
                <option value="0">0</option>
                <option value="1">1</option>
            </select>
        </div>
    <?php
}
?>
        </fieldset>
        <fieldset class="booking-section">
            <div class="inner">
                <div class="booking-summary" id="booking-summary">
                    <p>Please select a date from the calendar below to start your booking.</p>
                </div>
                <div class="booking-cta">
                    <button class="submit" id="booknow" disabled="disabled">Book Now</button>
                </div>
            </div>
        </fieldset>
        <fieldset class="booking-section">
            <div class="inner">
                <!-- Extra Breakdown -->
                <div id="bookingExtras" class="bookingExtras"></div>
            </div>
        </fieldset>
    </form>
    <div id="calendarContainer" class="clear">
            
<?php
$pointer = 0;
foreach (array(date('Y'), (date('Y')+1)) as $year) {
    for ($month = 1; $month <= 12; $month++) {
        $monthTime = mktime(0, 0, 0, $month, 1, $year);
        if ($monthTime >= mktime(0, 0, 0, date('m'), 1, date('Y'))) {

            if ($pointer % 4 == 0) {
                echo '<div class="clear"></div>';
            }
            echo $property->getCalendarWidget(
                $monthTime,
                array(
                    'start_day' => strtolower(
                        $property->getChangeOverDay()
                    ),
                    'attributes' => 'class="wp-tabs-api-calendar"',
                    'sevenRows' => true
                )
            );

            $pointer++;
        }
    }
}
?>
            
    </div>
</div>
<!-- /Property Calendar -->

<?php get_footer(); ?>