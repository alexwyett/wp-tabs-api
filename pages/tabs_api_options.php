<?php

/**
 * Options Settings Form
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
$variables = array(
    'wp_tabs_api_brochure_request_response' => array(
        'beforeRow' => '<tr><td colspan="2"><strong>Use the box below to enter text which will be 
            displayed to the customer when ordering a brochure.</strong></td></tr>',
        'label'     => 'Brochure Request Response Text',
        'type'      => 'textarea',
        'default'   => '',
        'args'      => 'cols="100"'
    ),
    'wp_tabs_api_ownerpack_request_response' => array(
        'beforeRow' => '<tr><td colspan="2"><strong>Use the box below to enter text which will be 
            displayed to the customer when ordering an owner pack.</strong></td></tr>',
        'label'     => 'Ownerpack Request Response Text',
        'type'      => 'textarea',
        'default'   => '',
        'args'      => 'cols="100"'
    ),
    'wp_tabs_api_booking_email_response' => array(
        'beforeRow' => '<tr><td colspan="2"><strong>Use the box below to enter text which will be 
            emailed to the customer when completing a booking.</strong></td></tr>',
        'label'     => 'Booking Request Response Text',
        'type'      => 'textarea',
        'default'   => '',
        'args'      => 'cols="100"'
    ),
    'wp_tabs_api_statuscode_20001' => array(
        'beforeRow' => '<tr><td colspan="2"><strong>The following are optional messages which are displayed if 
            the api could not find a price online.  Type a message in the corresponding right hand box
            to change the message.</strong></td></tr>',
        'label'     => 'The requested dates are in the past',
        'type'      => 'text',
        'args'      => 'maxlength="100" size="50"'
    ),
    'wp_tabs_api_statuscode_20002' => array(
        'label' => 'The property is not accepting bookings for the requested year',
        'type'  => 'text',
        'args'  => 'maxlength="200" size="50"'
    ),
    'wp_tabs_api_statuscode_20003' => array(
        'label' => 'The property is already booked for the requested dates',
        'type'  => 'text',
        'args'  => 'maxlength="200" size="50"'
    ),
    'wp_tabs_api_statuscode_20004' => array(
        'label' => 'The requested dates do not adhere to the property\'s short break rules',
        'type'  => 'text',
        'args'  => 'maxlength="200" size="50"'
    ),
    'wp_tabs_api_statuscode_20005' => array(
        'label' => 'The API was unable to find a price for this holiday (price was < £100).',
        'type'  => 'text',
        'args'  => 'maxlength="200" size="50"'
    ),
    'wp_tabs_api_statuscode_20006' => array(
        'label' => 'The API was unable to find a price for this holiday (missing pricetype record).',
        'type'  => 'text',
        'args'  => 'maxlength="200" size="50"'
    ),
    'wp_tabs_api_statuscode_20007' => array(
        'label' => 'The API was unable to find a price for this holiday (missing datechangedayperiod record).',
        'type'  => 'text',
        'args'  => 'maxlength="200" size="50"'
    ),
    'wp_tabs_api_statuscode_20008' => array(
        'label' => 'The API was unable to find a price for this holiday (missing pricingperiod record).',
        'type'  => 'text',
        'args'  => 'maxlength="200" size="50"'
    ),
    'wp_tabs_api_statuscode_20009' => array(
        'label' => 'The API was unable to find a price for this holiday (missing week record).',
        'type'  => 'text',
        'args'  => 'maxlength="200" size="50"'
    ),
    'wp_tabs_api_statuscode_20010' => array(
        'label' => 'The property does not accept pets',
        'type'  => 'text',
        'args'  => 'maxlength="200" size="50"'
    ),
    'wp_tabs_api_statuscode_20011' => array(
        'label' => 'The party exceeds the number of people the property can accommodate',
        'type'  => 'text',
        'args'  => 'maxlength="200" size="50"'
    ),
    'wp_tabs_api_statuscode_20012' => array(
        'label' => 'The party must consist of at least 1 adult',
        'type'  => 'text',
        'args'  => 'maxlength="200" size="50"'
    ),
    'wp_tabs_api_statuscode_20013' => array(
        'label' => 'The API was unable to find a price for this holiday (Missing price band in week record).',
        'type'  => 'text',
        'args'  => 'maxlength="200" size="50"'
    ),
    WPTABSAPIPLUGIN_OPT_EP_KEY => array(
        'label' => 'Optional Endpoints',
        'type'  => 'text',
        'args'  => 'maxlength="100" size="50"',
        'beforeRow' => '<tr><td colspan="2"><strong>These can be defined to request property data on
            additional templates.  The strings should be underscore separated and
            will be url sanitised for use.  You will also need to flush
            the permalink settings in order for the custom end points to work.
            To do this, re-save your permalink settings.</strong></td></tr>',
        'func'  => 'sanitize_title_with_dashes'
    ),
    'wp_tabs_api_search_prefix' => array(
        'beforeRow' => '<tr><td colspan="2"><strong>You can use the following to set a prefix onto 
            the query string parameters for property searches.  This is useful for avoiding clashes.</strong></td></tr>',
        'label'     => 'Search Query Prefix',
        'type'      => 'text',
        'default'   => 'wp_',
        'args'      => 'maxlength="100" size="50"'
    )
);

if (count($_POST) > 0) {
    if (checkArrayKeyExistsAndHasAValue($_POST, "action", "update")) {
        foreach (array_keys($variables) as $key) {
            if (checkKeyExists($_POST, $key)) {
                if (isset($variables[$key]['func'])) {
                    $func = $variables[$key]['func'];
                    if (function_exists($func)) {
                        $_POST[$key] = $func($_POST[$key]);
                    }
                }
                update_option($key, $_POST[$key]);
            }
        }
    }
}
$options = array();
foreach (array_keys($variables) as $key) {
    $options[$key] = get_option($key); 
}

?>

<form method="post" action="">  
    <input type="hidden" name="action" value="update" />  
    <h3>Tabs API Plugin Options</h3>
    <table class="form-table">    
    <?php
foreach ($variables as $key => $val) {
    echo isset($val['beforeRow']) ? $val['beforeRow'] : '';
    echo sprintf(
        '<tr>
            <th>
                <label for="%s">%s: </label>
            </th>
            <td>
                %s',
        $key,
        $val['label'],
        isset($val['before']) ? $val['before'] : ''
    );
    
    if ($val['type'] == 'textarea') {
        wp_editor(
            $options[$key], 
            $key, 
            array(
                "media_buttons" => true,
                'teeny' => false,
                "textarea_rows" => 10
            )
        );
    } else {
        echo getInputField(
            $key, 
            $options[$key], 
            $val['type'],
            $val['args'],
            array(),
            isset($val['default']) ? $val['default'] : ''
        );
    }
    
    echo '</td></tr>';
    echo isset($val['after']) ? $val['after'] : '';
}
    ?>
    </table>
    <p class="submit">
        <input type="submit" class="button-primary" value="Save Options" />
    </p>
</form>