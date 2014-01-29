<?php

/**
 * Helper Functions File
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
 * Function to generate a json response
 *
 * @param array $responseArray The array to encode as json
 *
 * @return void
 */
if (!function_exists('generateResponse')) {
    function generateResponse($responseArray) 
    {
        if (function_exists('json_encode')) {
            die(json_encode($responseArray));
        } else {
            die("json_encode function does not exist");
        }   
    }
}


/**
 * Function to get a included file stream
 *
 * @param string $filename    The path to the file
 * @param mixed  $objectArray Any object you want to pass by reference
 *
 * @return mixed
 */
if (!function_exists('getIncludeContents')) {
    function getIncludeContents($filename, $objectArray = array())
    {
        if (is_file($filename)) {
            ob_start();
            include $filename;
            return ob_get_clean();
        }
        return false;
    }
}

/**
* Function used to check a global variable and return a default if not
* 
* @param string $global  the string of the global you want to check
* @param string $default the string of the default you want to use
* 
* @return string
*/
if (!function_exists('checkGlobal')) {
    function checkGlobal($global, $default)
    {
        $constants = get_defined_constants(true);
        $userconstants = $constants['user'];    
        if (checkArrayKeyExistsAndHasAValue($constants, $global, "")) {
            return $userconstants[$global];
        } else {
            return $default;
        }
    }
}

/**
 * Function used to test the validity of an array
 * 
 * @param array $array the array to check
 * 
 * @return boolean
 */
if (!function_exists('checkArray')) {
    function checkArray($array) 
    {
        if (is_array($array)) {
            if (count($array) > 0) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Check to see if a request is an ajax request or not
 *
 * @return boolean
 */
if (!function_exists('isAjax')) {
    function isAjax() 
    {
        return (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        );
    }
}

/**
 * Function used to assign a variable a valur if it exists in an array 
 * else, assign failed value
 * 
 * @param array  $array            the array to validate
 * @param string $key              the key to check exisitence
 * @param string $failed_key_value the value to use if check has failed
 * 
 * @return boolean
 */
if (!function_exists('assignArrayValue')) {
    function assignArrayValue($array, $key, $failed_key_value)
    {
        if (checkKeyExists($array, $key)) {
            return $array[$key];
        } else {
            return $failed_key_value;
        }
    }
}

/**
 * Function used to test to see a key is in an array and has a specific value
 * 
 * @param array  $array     the array to validate
 * @param string $key       the key to check exisitence
 * @param string $key_value the comparison value
 * 
 * @return boolean
 */
if (!function_exists('checkArrayKeyExistsAndHasAValue')) {
    function checkArrayKeyExistsAndHasAValue($array, $key, $key_value)
    {
        if (checkKeyExists($array, $key)) {
            return ($array[$key] == $key_value);
        }
        
        return false;
    }
}

/**
 * Function used to test to see a key is in an array and is greater then a 
 * certain string length
 * 
 * @param array  $array      the array to validate
 * @param string $key        the key to check exisitence
 * @param string $min_length the min length comparison value
 * @param string $max_length the max length comparison value
 * 
 * @return boolean
 */
if (!function_exists('checkArrayKeyExistsAndIsGreaterThanLength')) {
    function checkArrayKeyExistsAndIsGreaterThanLength(
        $array, 
        $key, 
        $min_length = 0, 
        $max_length = 0
    ) {
        if (checkKeyExists($array, $key)) {
            if (strlen($array[$key]) > $min_length) {
                if ($max_length > 0) {
                    return (strlen($array[$key]) < $max_length);
                } else {
                    return true;
                }
            } else {
                return false;
            }
        }
        
        return false;
    }
}


/**
 * Function used to test to see a key is in an array
 * 
 * @param array  $array the array to validate
 * @param string $key   the key to check existence
 * 
 * @return boolean
 */
if (!function_exists('checkKeyExists')) {
    function checkKeyExists($array, $key)
    {
        return isset($array[$key]);
    }
}

/**
 * Function used to save a flash message
 *
 * @param string $status  The status/class you was temporarily saved
 * @param string $message The message you was temporarily saved
 * 
 * @return void
 */
if (!function_exists('saveFlashMessage')) {
    function saveFlashMessage($status, $message)
    {
        @session_start();
        $_SESSION['flash_status'] = $status;
        $_SESSION['flash'] = $message;
    }
}

/**
 * Function used to save a flash message
 *
 * @return string
 */
if (!function_exists('getFlashMessage')) {
    function getFlashMessage()
    {
        @session_start();
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        } else {
            return "";
        }
    }
}

/**
 * Function used to save a flash message
 *
 * @return string
 */
if (!function_exists('getFlashStatus')) {
    function getFlashStatus()
    {
        @session_start();
        if (isset($_SESSION['flash_status'])) {
            $flash_status = $_SESSION['flash_status'];
            unset($_SESSION['flash_status']);
            return $flash_status;
        } else {
            return "";
        }
    }
}

/**
 * Function used to save a flash status message
 *
 * @return string
 */
if (!function_exists('getFlashStatusMessage')) {
    function getFlashStatusMessage()
    {
        $flash_status = getFlashStatus();
        $flash_message = getFlashMessage();
        
        if ($flash_status != "" && $flash_message != "") {
            return sprintf(
                '<div id="message" class="updated fade %s"><p>%s</p></div>',
                $flash_status,
                $flash_message
            );
        } else {
            return "";
        }
    }
}


/**
 * Header Redirect
 *
 * Header redirect in two flavors
 * For very fine grained control over headers, you could use the Output
 * Library's set_header() function.
 *
 * @param string  $uri      the URL
 * @param string  $method   the method: location or redirect
 * @param integer $httpCode http response code if using location redirect
 * 
 * @return string
 */
if (!function_exists('redirect')) {
    function redirect($uri = '', $method = 'refresh', $httpCode = 302)
    {
        if (!headers_sent()) {
            switch($method) {
            case 'refresh':
                header("Refresh:0;url=".$uri);
                break;
            default:
                header("Location: ". $uri, true, $httpCode);
                break;
            }
        } else {
            echo '<script type="text/javascript">';
            echo 'window.location.href="'.$uri.'";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="0;url='.$uri.'" />';
            echo '</noscript>';
        }
        exit;
    }
}

/**
 * Return a html settings control for the form
 * 
 * @param string $name           Input name of setting
 * @param string $settingValue   Value
 * @param string $type           Type
 * @param string $elementArgs    Additional Arguments for the input element
 * @param array  $optionalValues Optional parameters
 * @param string $defaultValue  Default Value
 * 
 * @return string 
 */
if (!function_exists('getInputField')) {
    function getInputField(
        $name, 
        $settingValue, 
        $type, 
        $elementArgs = '',
        $optionalValues = array(),
        $defaultValue = ''
    ) {        
        if ($settingValue == '') {
            $settingValue = $defaultValue;
        } else {
            $settingValue = str_replace("\'", "'", $settingValue);
        }

        switch ($type) {

        case "select": // Comma separated value list
            $select = sprintf(
                '<select id="%s" name="%s" %s>',
                $name,
                $name,
                $elementArgs
            );
            foreach ($optionalValues as $key => $value) {
                // If the key is numeric, use value for both index and value
                if (!is_string($key)) {
                    $key = $value;
                }
                $selected = ($key == $settingValue) ? "selected" : "";
                $select .= sprintf(
                    '<option value="%s" %s>%s</option>',
                    $key,
                    $selected,
                    $value
                );
            }            

            $select .= "</select>";
            return $select;
            break;

        case "select_db":
            global $wpdb;
            $select = '';
            if (is_object($wpdb)) {
                $results = $wpdb->get_results($optionalValues);
                if ($results) {
                    $select = sprintf(
                        '<select id="%s" name="%s">',
                        $name,
                        $name
                    );
                    foreach ($results as $result) {
                        $selected = '';
                        if ($result->option_value == $settingValue) {
                            $selected = 'selected';
                        }
                        $select .= sprintf(
                            '<option value="%s" %s>%s</option>',
                            $result->option_value,
                            $selected,
                            $result->display_value
                        );
                    }
                    $select .= "</select>";
                }
            }
            return $select;
            break;

        case "tinyint":  // Checkbox
        case "boolean":  // Checkbox
        case "checkbox": // Checkbox
            $checked = (
                $settingValue == "1" || 
                $settingValue == "true") ? " checked" : "";
            
            $cbVal = (is_string($optionalValues) ? $optionalValues : 1);
            
            return sprintf(
                '<input id="%s" name="%s" value="%s" type="checkbox" 
                    class="checkbox wp_metabox_checkbox" %s>',
                $name,
                $name,
                $cbVal,
                $checked                
            );
            break;

        case "hidden":
            return sprintf(
                '<input id="%s" name="%s" value="%s" type="hidden" 
                    class="wp_metabox_hidden">',
                $name,
                $name,
                $defaultValue                
            );
            break;

        case "date":
            return sprintf(
                '<input id="%s" name="%s" value="%s" type="date" 
                    class="wp_metabox_date">',
                $name,
                $name,
                date("d M Y", strtotime($settingValue))
            );
            break;
        
        case "longtext": // Textarea
        case "textarea": // Textarea
            return sprintf(
                '<textarea id="%s" name="%s" %s>%s</textarea>',
                $name,
                $name,
                $elementArgs,
                $settingValue                
            );
            break;
        
        case "wysiwyg": // Wysiwyg Textarea
            ob_start();
            if ($optionalValues != "") {
                wp_editor(
                    $settingValue, 
                    $name, 
                    array(
                        "media_buttons" => false,
                        'tinymce' => array(
                            'theme_advanced_buttons1' => $optionalValues,
                            'theme_advanced_buttons2' => ''
                        ),
                        "textarea_rows" => 10
                    )
                );
            } else {
                wp_editor(
                    $settingValue, 
                    $name, 
                    array(
                        "media_buttons" => false,
                        'teeny' => true,
                        "textarea_rows" => 10
                    )
                );
            }
            return ob_get_clean();
            break;

        default:
            return sprintf(
                '<input id="%s" value="%s" name="%s" type="text" %s>',
                $name,
                $settingValue,
                $name,
                $elementArgs
            );
            break;
        }
    }
}