<?php

/**
 * WP Tabs Api Wrapper Class
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
 * Require the quicksearch form object
 */
require_once 'libraries/Quicksearch.php';

/**
 * WP Tabs Api Wrapper Class.  Conditionally includes tabs api client files
 * and returns the correct objects. 
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
class WpTabsApiWrapper
{
    /**
     * Constructor
     *
     * @param string $url    Api Url
     * @param string $key    Api User Key
     * @param string $secret Api Secret Key
     *
     * @return void
     */
    public function __construct($url, $key = '', $secret = '')
    {
        // Start session if not already started
        @session_start();
        
        // Create a new api connection
        \tabs\api\client\ApiClient::factory($url, $key, $secret);
    }
    
    /**
     * Return all properties in api
     *
     * @return array
     */
    public function getAllProperties()
    {
        // Retrieve property data from api
        $propertySearch = \tabs\api\property\PropertySearch::fetchAll(
            '', 
            '', 
            null,
            array('id', 'propertyRef', 'slug', 'name')
        );
    
        return $propertySearch->getProperties();
    }
    
    /**
     * Get all of the property slugs in the api
     * 
     * @param string $propertyId The property 'propref_brandcode'
     * 
     * @return \tabs\api\property\Property
     */
    public function getPropertyFromId($propertyId)
    {
        $propRef = rtrim(
            $propertyId, '_' . strtoupper(get_option('tabs_api_brandcode'))
        );
        $property = \tabs\api\property\Property::getProperty(
            $propRef, 
            strtoupper(get_option('tabs_api_brandcode'))
        );
            
        // Check if the property is shortlisted
        if (in_array($property->getPropref(), $this->getShortlist())) {
            $property->setShortlist(true);
        }
        
        return $property;
    }
    
    /**
     * Return a new search helper object
     * 
     * @param array  $searchParams      Search params
     * @param array  $landingPageParams Hardcoded search params
     * @param string $baseUrl           Base url of search
     * 
     * @return \tabs\api\property\SearchHelper
     */
    public function getSearchHelper(
        $searchParams = array(),
        $landingPageParams = array(),
        $baseUrl = ''
    ) {
        return new \tabs\api\property\SearchHelper(
            $searchParams, 
            $landingPageParams, 
            $baseUrl
        );
    }
    
    /**
     * Get tabs areas
     * 
     * @return array 
     */
    public function getAreas()
    {
        return \tabs\api\utility\Utility::getAreas();
    }
    
    /**
     * Get the areas for the search form
     * 
     * @return array
     */
    public function getAreasInverse()
    {
        return array_flip($this->getAreas());
    }
    
    /**
     * Get tabs locations
     * 
     * @return array 
     */
    public function getLocations()
    {
        return  \tabs\api\utility\Utility::getLocations();
    }
    
    /**
     * Get the locations for the search form
     * 
     * @return array
     */
    public function getLocationsInverse()
    {
        return array_flip($this->getLocations());
    }
    
    /**
     * Get the locations array with areacode as a class
     * 
     * @return array 
     */
    public function getLocationsArray()
    {
        $areas =  \tabs\api\utility\Utility::getAreasAndLocations();
        $locations = array();
        
        foreach ($areas as $area) {
            foreach ($area->getLocations() as $location) {
                $locations[$location->getName()] = array(
                    'value' => $location->getCode(),
                    'class' => $area->getCode()
                );
            }
        }       
        
        return $locations;
    }
    
    /**
     * Get number of properties
     * 
     * @return array 
     */
    public function getNumberOfProperties()
    {
        return  \tabs\api\utility\Utility::getNumberOfProperties();
    }
    
    /**
     * Get tabs countries
     * 
     * @return array 
     */
    public function getCountries()
    {
        return \tabs\api\utility\Utility::getCountriesBasic();
    }
    
    /**
     * Get tabs countries
     * 
     * @return array 
     */
    public function getCountriesInverse()
    {
        return array_flip($this->getCountries());
    }
    
    /**
     * Get tabs sourcecodes
     * 
     * @return array 
     */
    public function getSourceCodes()
    {
        return \tabs\api\utility\Utility::getSourceCodesBasic();
    }
    
    /**
     * Get tabs sourcecode objects
     * 
     * @return array 
     */
    public function getSourceCodesFull()
    {
        return \tabs\api\utility\Utility::getSourceCodes();
    }
    
    /**
     * Get tabs countries
     * 
     * @return array 
     */
    public function getSourceCodesInverse()
    {
        return array_flip($this->getSourceCodes());
    }
    
    /**
     * Return a new enquiry object
     * 
     * @param string    $propertyId The property 'propref_brandcode'
     * @param timestamp $fromdate   Start of booking
     * @param timestamp $todate     End of booking
     * @param integer   $people     Number of people
     * @param integer   $pets       Number of pets
     * 
     * @return  \tabs\api\booking\Enquiry
     */
    public function getEnquiry(
        $propertyId,
        $fromdate,
        $todate,
        $people,
        $pets = 0
    ) {
        $property = $this->getPropertyFromId($propertyId);
        return \tabs\api\booking\Enquiry::create(
            $property->getPropref(), 
            get_option('tabs_api_brandcode', 'XX'), //$property->getBrandcode(), 
            $fromdate,
            $todate,
            $people,
            0,
            0,
            $pets
        );
    }
    
    /**
     * Return a new booking object
     * 
     * @param string    $propertyId The property 'propref_brandcode'
     * @param timestamp $fromdate   Start of booking
     * @param timestamp $todate     End of booking
     * @param integer   $adults     Number of people
     * @param integer   $children   Number of people
     * @param integer   $infants    Number of people
     * @param integer   $pets       Number of pets
     * 
     * @return \tabs\api\booking\Booking
     */
    public function createNewBooking(
        $propertyId,
        $fromdate,
        $todate,
        $adults,
        $children = 0,
        $infants = 0,
        $pets = 0
    ) {
        $property = $this->getPropertyFromId($propertyId);
        return \tabs\api\booking\Booking::create(
            $property->getPropref(), 
            get_option('tabs_api_brandcode', 'XX'),
            $fromdate, 
            $todate, 
            (int) $adults, 
            (int) $children, 
            (int) $infants, 
            (int) $pets
        );
    }
    
    /**
     * Return an existing booking object
     * 
     * @param string $bookingId The booking id
     * 
     * @return \Booking
     */
    public function createBookingFromId($bookingId)
    {
        return \tabs\api\booking\Booking::createBookingFromId($bookingId);
    }
    
    /**
     * Create a new customer from a provided array
     * 
     * @param array $array Key/Val array of data (such as post array)
     * 
     * @return Customer
     */
    public function createNewCustomerFromPostArray($array)
    {
        $customer = \tabs\api\core\Customer::factory('', '');
        $this->_setPersonProperties($customer, $array);
        return $customer;
    }
    
    /**
     * Create a new owner from a provided array
     * 
     * @param array $array Key/Val array of data (such as post array)
     * 
     * @return Owner
     */
    public function createNewOwnerFromPostArray($array)
    {
        $owner = \tabs\api\core\Owner::factory('', '');
        $this->_setPersonProperties($owner, $array);
        return $owner;
    }
    
    /**
     * Return an allow list of search filters
     * 
     * @return array
     */
    public function getSearchFilters()
    {
        $filters = \tabs\api\utility\Utility::getApiInformation()->getSearchFilters();
        foreach ($filters as $filter) {
            $filters[] = WPTABSAPIPLUGINSEARCHPREFIX . $filter;
        }
        
        // Add in defaults, page, pageSize, orderBy
        $filters[] = WPTABSAPIPLUGINSEARCHPREFIX . 'page';
        $filters[] = WPTABSAPIPLUGINSEARCHPREFIX . 'pageSize';
        $filters[] = WPTABSAPIPLUGINSEARCHPREFIX . 'orderBy';
        
        return $filters;
    }
    
    /**
     * Create a new arty member from a provided array
     * 
     * @param array   $array  Key/Val array of data (such as post array)
     * @param integer $number Party member number
     * @param string  $type   Type
     * 
     * @return PartyDetail
     */
    public function createNewPartyMemberFromPostArray(
        $array, 
        $number = 1, 
        $type = 'adult'
    ) {
        if (isset($array[$type . '_' . $number . '_firstname'])
            && isset($array[$type . '_' . $number . '_surname'])
            && isset($array[$type . '_' . $number . '_age'])
        ) {
            $partyMember = \tabs\api\booking\PartyDetail::createPartyMember(
                $array[$type . '_' . $number . '_firstname'], 
                $array[$type . '_' . $number . '_surname'], 
                $array[$type . '_' . $number . '_age'], 
                $array[$type . '_' . $number . '_title'], 
                $type
            );
        
            return $partyMember;
        } else {
            return false;
        }
    }
    
    /**
     * Return a new sagepay helper
     * 
     * @param string $vendor          Sagepay vendor name
     * @param string $mode            Sagepay Mode
     * @param string $notificationURL Callback url
     * 
     * @return \SagepayServer 
     */
    public function getSagePayHelper(
        $vendor, 
        $mode = 'Test', 
        $notificationURL = ''
    ) {
        return new \tabs\api\utility\SagepayServer(
            $vendor, 
            $mode, 
            $notificationURL
        );
    }
    
    /**
     * Add a property reference to the shortlist
     * 
     * @param string $propRef Property reference
     * 
     * @return void
     */
    public function addToShortlist($propRef)
    {
        $shortlist = $this->getShortlist();
        if (!in_array($propRef, $shortlist)) {
            array_push($shortlist, $propRef);
        }
        $this->saveShortlist($shortlist);
    }
    
    /**
     * Remove a property ref from the shortlist
     * 
     * @param string $propRef Property Reference
     * 
     * @return void
     */
    public function removeFromShortlist($propRef)
    {
        $shortlist = $this->getShortlist();
        if (in_array($propRef, $shortlist)) {
            $shortlist = array_flip($shortlist);
            unset($shortlist[$propRef]);
            $shortlist = array_flip($shortlist);
        }
        $this->saveShortlist($shortlist);
    }
    
    /**
     * Retrieve the shortlist
     * 
     * @return array Array of proprefs
     */
    public function getShortlist()
    {
        if ($_SESSION && isset($_SESSION['wp_tabs_api_shortlist'])) {
            $shortlist = json_decode($_SESSION['wp_tabs_api_shortlist']);
            if (is_array($shortlist)) {
                return $shortlist;
            }
        }
        
        return array();
    }
    
    /**
     * Save the shortlist to the session
     * 
     * @param array $shortlist Shortlist of properties
     * 
     * @return void
     */
    public function saveShortlist($shortlist)
    {
        $_SESSION['wp_tabs_api_shortlist'] = json_encode($shortlist);
    }
    
    /**
     * Set a person object
     * 
     * @param \tabs\api\core\Person $obj   Api person instance
     * @param array                 $array Array of variables
     * 
     * @return void
     */
    private function _setPersonProperties(&$obj, $array)
    {
        // Set the customer main fields
        foreach ($array as $key => $val) {
            
            // Some basic transforms needed for the new forms
            switch($key) {
            case 'initial':
                $key = 'firstName';
                break;
            case 'telephone':
                $key = 'daytimePhone';
                break;
            case 'mobile':
                $key = 'mobilePhone';
                break;
            case 'emailoptin':
                $key = 'emailOptIn';
                break;
            }
            
            $func = 'set' . ucfirst($key);
            if ($val != '') {
                // Set the email optin boolean
                if ($key == 'emailOptIn') {
                    $val = true;
                }
                // Set the customer data
                if (property_exists($obj, $key)) {
                    $obj->$func($val);
                }
                // Set Customer address fields
                if (property_exists($obj->getAddress(), $key)) {
                    $obj->getAddress()->$func($val);
                }
            }
        }
    }
    
    /**
     * Return a quick search form
     * 
     * @param array $attributes Form attributes
     * @param array $formValues Form Values
     *
     * @return \aw\formfields\form\Quicksearch
     */
    public function getQuicksearchForm(
        $attributes = array(),
        $formValues = array()
    ) {
        $search = $this->getSearchHelper();

        // Set search prefix
        $search->setSearchPrefix(
            WPTABSAPIPLUGINSEARCHPREFIX
        );
        
        // New search form
        $form = new \aw\formfields\forms\Quicksearch($attributes, $formValues);
        
        // Set element prefix
        $form->setPrefix(WPTABSAPIPLUGINSEARCHPREFIX);
        
        // Create new area select box
        $form->setAreaSelect(
            $form->createBasicSelect(
                'Area', 
                array_merge(
                    array('Any Area' => ''),
                    $this->getAreasInverse()
                ),
                $search->getSearchPrefix() . 'area', 
                'area'
            )
        );
        
        // Create new area location box
        $form->setLocationSelect(
            $form->createBasicSelect(
                'Location', 
                array_merge(
                    array('Any Location' => ''),
                    $this->getLocationsArray()
                ),
                $search->getSearchPrefix() . 'location', 
                'location'
            )
        );
            
        // Register hook for the form preprocessing
        do_action('wpTabsApiWidgetFormModify', $form);
        
        return $form->build();
    }
}