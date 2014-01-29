<?php

/**
 * Plugin Settings Form
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
    "tabs_api_url" => array(
        "label" => "API Url",
        "type" => "text",
        "args" => 'maxlength="100" size="50"',
        "description" => "This is the url to the root of the tabs api you wish to connect to."
    ),
    "tabs_api_user" => array(
        "label" => "API Username",
        "type" => "text",
        "args" => 'maxlength="100" size="30"',
        "description" => "Tabs API Username, should be supplied by Carlton Software"
    ),
    "tabs_api_secret" => array(
        "label" => "API Secret Key",
        "type" => "text",
        "args" => 'maxlength="100" size="30"',
        "description" => "Tabs API Secret Key, should be supplied by Carlton Software"
    ),
    "tabs_api_brandcode" => array(
        "label" => "Tabs Brandcode",
        "type" => "text",
        "args" => 'maxlength="3" size="3"',
        "description" => "Two digit tabs brandcode"
    ),
    "tabs_api_website_mode" => array(
        "label" => "Website Mode",
        "type" => "select",
        "options" => array(
            'Test',
            'Live'
        ),
        "description" => "Website mode, if this is set to test, bookings will not be confirmed by the Tabs API."
    ),
    "tabs_api_sagepay_vendor" => array(
        "label" => "Sagepay Vendor Name",
        "type" => "text",
        "args" => 'maxlength="20" size="20"',
        "description" => "Sagepay vendor name for taking payments."
    ),
    "tabs_api_sagepay_mode" => array(
        "label" => "Sagepay Vendor Mode",
        "type" => "select",
        "options" => array(
            'Test',
            'Live'
        ),
        "description" => "Sagepay mode - which system for payments to be processed on. "
        . "If set to test, only <a href='http://www.sagepay.co.uk/support/12/36/test-card-details-for-your-test-transactions'>these</a> card "
        . "details will be able to be processed."
    ),
    "tabs_api_credit_card_charge" => array(
        "label" => "Sagepay Credit Card Percentage",
        "type" => "text",
        "description" => "Percentage of the transaction amount you wish for credit card transactions."
    )
);

if (count($_POST) > 0) {
    if (checkArrayKeyExistsAndHasAValue($_POST, "action", "update")) {
        foreach (array_keys($variables) as $key) {
            if (checkKeyExists($_POST, $key)) {
                if ($key == 'tabs_api_brandcode') {
                    $_POST[$key] = strtoupper($_POST[$key]);
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
    <h3>Tabs API Plugin Settings</h3>
    <table class="form-table">    
    <?php
foreach ($variables as $key => $val) {
    echo sprintf(
        '<tr>
            <th>
                <label for="%s">%s: </label>
            </th>
            <td>
                %s
                <div class="field-description">%s</div>
            </td>
        </tr>',
        $key,
        $val['label'],
        getInputField(
            $key, 
            $options[$key], 
            $val['type'],
            (isset($val['args']) ? $val['args'] : ''),
            (isset($val['options']) ? $val['options'] : array())
        ),
        (isset($val['description']) ? $val['description'] : '')
    );
}
    ?>
    </table>
    <p class="submit">
            <input type="submit" class="button-primary" value="Save Changes" />
    </p>
</form>