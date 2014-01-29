/**
 * Enquiry bool - stops concurrent requests
 */
var enquiring = false;

/**
* Standard getPrice callback function
* 
* @param startDay Starting date of the holiday period
* @param endDay   Finish date of the holiday period
* 
* @return void
*/
var getPrice = function(startDay, endDay) {
    if (!enquiring) {
        var startStr = startDay.toString();
        var endStr = endDay.toString();
        jQuery('#booking-summary').removeClass('ok').html('Finding price...');
        jQuery("#fromDate").val(startStr);
        jQuery("#toDate").val(endStr);
        jQuery('#bookingExtras').html('');
        jQuery('#booknow').attr("disabled", "disabled");
        $form = jQuery('#enquiry-form');
        enquiring = true;
        jQuery.getJSON($form.data('enquiry-url'), $form.serialize(), function(json) {
            jQuery('#booking-summary').addClass(json.status).html(json.message);
            jQuery('#enquiryFromDate').val(json.from);
            jQuery('#enquiryToDate').val(json.to);
            if (json.status == 'ok') {
                jQuery('#booknow').removeAttr("disabled");
                if (countJson(json.extras) > 0) {
                    jQuery('#bookingExtras').html('<p>Including:</p>');
                    jQuery.each(json.extras, function(extraCode, extra) {
                        jQuery('#bookingExtras').append('<p>' + extra.description + ': &pound;' + extra.total.toFixed(2) + '</p>');
                    });
                }
            }
            enquiring = false;
        });
    }
}


/**
* Json Count function
* @param obj json object
*/
function countJson(obj) {
    var prop;
    var count = 0;
    for (prop in obj) {
        count++;
    }
    return count;
}


jQuery(document).ready(function() {
    // Create availability calendar functions
    var cal = jQuery('#calendarContainer').availabilityCalendar({
        clickCallBack: getPrice
    });
    
    // Attach highlight function to change event
    jQuery('#nights, #adults, #children, #infants, #pets').change(function() {
        cal.data('plugin_availabilityCalendar')._highlightCalendar(
            jQuery('#' + jQuery('#fromDate').val())
        );
    });
});