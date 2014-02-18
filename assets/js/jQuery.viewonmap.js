/*
 * jQuery view on map object.
 *
 * See the queryUrl parameter.  This is a url to a php file which will return a
 * json string in the following format:
 *
 * {
 *      "total": (int), // Total amount of nodes found
 *      "lng": (float), // Longitude of center
 *      "lat": (float), // Latitude of center
 *      "markers": [    // Array of markers
 *          {
 *              "lng":    (float), // Longitude of marker
 *              "lat":    (float), // Latitude of marker
 *              "count":  (int),   // Number of results found on the marker
 *              "marker": (string) // HTML of the infowindow
 *          }
 *      ]
 * }
 *
 * Map controls can be configured via css:
 *
 *      .dysMapControl {
 *          padding: 1px 4px;
 *          background: #FFF;
 *          border: 1px solid #666;
 *          margin: 5px 0px;
 *          font-size: 13px;
 *          box-shadow: 0px 2px 5px 0px #555;
 *      }
 *
 */
 (function(jQuery) {

    jQuery.viewOnMap = function(eleId, options) {
        // Default values
        var defaults = {
            distanceBetweenMarkers: 1000,
            queryUrl: '/getProperties.php',
            searchParams: {},
            mapOptions: {
                center:             new google.maps.LatLng(52.689, 1.44),
                zoom:               10,
                panControl:         true,
                zoomControl:        true,
                mapTypeControl:     true,
                scaleControl:       true,
                streetViewControl:  false,
                overviewMapControl: true,
                rotateControl:      true,
                mapTypeId:          google.maps.MapTypeId.ROADMAP
            },
            jsonFunction: null      // Set this if another json processing function
                                    // is needed
        }

        // Private variables
        var markersArray = [];
        var LatLngList = [];
        var points = [];
        var plugin = this;
        var activeListeners = [];
        var requesting = false;
        var markerPositions = [];

        // Public settings
        plugin.settings = {};
        plugin.loaded = false;

        // Constructor
        var init = function() {
            plugin.settings = jQuery.extend({}, defaults, options);
            plugin.eleId = eleId;
            
            // Attempt to load the map state from the URL
            var getparams = getUrlVars();
            if (getparams.length == 3 && typeof getparams['centerY'] != 'undefined') {
                
                // Get map params from url
                var zoom = parseInt(getparams['zoom']);
                var center = new google.maps.LatLng(
                    getparams['centerY'], 
                    getparams['centerX']
                );
                
                // Create new map
                plugin.createMap();
            
                // Set map zoom and center
                plugin.map.setCenter(center);
                plugin.map.setZoom(zoom);
                
            } else {
                // Create map!
                plugin.createMap();
            }
            
            // Set the zoom level
            plugin.lastZoom = plugin.getMap().getZoom();
            
            //http://stackoverflow.com/questions/2832636/google-maps-api-v3-getbounds-is-undefined
            google.maps.event.addListenerOnce(
                plugin.getMap(), 
                "bounds_changed", 
                function() {
                    plugin.firstLoad();
                    google.maps.event.addListener(
                        plugin.getMap(), 
                        "idle", 
                        plugin.firstLoad
                    );
                }
            );
        }


        /**
         * Map create function
         */
        plugin.createMap = function() {
            // Create new google map with provided options
            plugin.map = new google.maps.Map(
                document.getElementById(
                    plugin.eleId
                ),
                plugin.settings.mapOptions
            );
        }
        
        /**
         * First load function
         */
        plugin.firstLoad = function() {
            if (!plugin.loaded) {
                plugin.getMarkers();
                
                // add Event listeners
                addDragEndListener();
                addZoomChangedListener();
            }
        }
        
        /**
         * Clears all overlays from the map
         */
        plugin.clearOverlays = function() {
            if (markersArray) {
                for (i in markersArray) {
                    markersArray[i].setMap(null);
                }
            }
            markersArray = [];
            markerPositions = [];
        }
        
        /**
         * Redraw the map
         */
        plugin.refreshMap = function() {
            plugin.clearOverlays();
            plugin.getMarkers();
        }
        
        /**
         * Fit bounds to map
         */
        plugin.fitMapBounds = function() {
            //  Create a new viewpoint bound
            var bounds = new google.maps.LatLngBounds();
            
            console.log(LatLngList);

            //  Go through each...
            for (var i = 0, LtLgLen = LatLngList.length; i < LtLgLen; i++) {
                //  And increase the bounds to take this point
                bounds.extend(LatLngList[i]);
            }

            // Don't zoom in too far on only one marker
            if (bounds.getNorthEast().equals(bounds.getSouthWest())) {
               var extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat() + 0.01, bounds.getNorthEast().lng() + 0.01);
               var extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat() - 0.01, bounds.getNorthEast().lng() - 0.01);
               bounds.extend(extendPoint1);
               bounds.extend(extendPoint2);
            }
            
            // Fit these bounds to the map
            plugin.map.fitBounds(bounds);
        }


        /**
         * Add a marker onto the google map object given a coordinate
         *
         * @param float   lat
         * @param float   long
         * @param string  content
         * @param integer count
         */
        plugin.addMarker = function(lat, lng, content, count) {

            // Create a new market position
            var pos = new google.maps.LatLng(lat, lng);

            // Create marker object
            var marker = plugin.createMarker(pos, plugin.getMap(), count);
            
            // NB. We reuse a global infoWindow object, in order that only 1
            // infoWindow is visible at a time
            var infoWindow;
            infoWindow = plugin.createInfoWindow(content);
            
            // Add info window click event
            google.maps.event.addListener(marker, 'click', function() {
                infoWindow.open(plugin.map, marker);
                if(plugin.lastInfoWindow !== undefined) {
                    plugin.lastInfoWindow.close();
                }
                plugin.lastInfoWindow = infoWindow;
            });
            
            // Get map bounds
            var bounds = plugin.getMap().getBounds();
            if (!bounds.contains(marker.getPosition())) {
                marker.setVisible(false);
            }

            // Add marker to array
            markersArray.push(marker);
        }
        
        /**
         * Create marker
         * 
         * @param object  pos   LatLng object
         * @param object  map   Google map
         * @param integer count Count of properties
         *
         * @return object Return marker object
         */
        plugin.createMarker = function(pos, map, count) {
            return new google.maps.Marker({
                position: pos,
                map: map
            })
        }
        
        /**
         * Create a new info window
         *
         * @param string content
         *
         * @return object Returns the infowindow object
         */
        plugin.createInfoWindow = function(content) {
            return new google.maps.InfoWindow({
                content: content
            });
        }

        /**
         * Request markers from a predefined url
         */
        plugin.getMarkers = function() {
        
            // Set zoom to redraw cluster
            plugin.settings.searchParams.zoom = plugin.getMap().getZoom();
            
            if (!requesting) {
                requesting = true;
                
                plugin.startLoading();
                
                jQuery.getJSON(
                    plugin.settings.queryUrl, 
                    plugin.settings.searchParams,
                    function(json) {
                        if (!json || parseInt(json.total) == 0) {
                            plugin.errorLoading();
                        } else {
                            if (plugin.loaded === false) {
                                jQuery.each(json.markers, function(index, marker) {
                                    LatLngList.push(
                                        new google.maps.LatLng (
                                            marker.lat, 
                                            marker.lng
                                        )
                                    );
                                });
                                plugin.fitMapBounds();
                                plugin.loaded = true;
                            }
                            
                            jQuery.each(json.markers, function(index, marker) {
                                plugin.addMarker(
                                    marker.lat, 
                                    marker.lng, 
                                    marker.marker, 
                                    marker.count
                                );
                            });
                            requesting = false;
                            console.log(markersArray.length);
                        }
                        
                        plugin.endLoading();
                    }
                );
            }
        }
        
        /**
         * Loading function which can be prototyped
         */
        plugin.startLoading = function() {
            
        }
        
        /**
         * Finished Loading function which can be prototyped
         */
        plugin.endLoading = function() {
            
        }
        
        /**
         * Error Loading function which can be prototyped
         */
        plugin.errorLoading = function() {
            
        }
        
        /**
         * This function reloads the url to persist the map state
         */
        plugin.saveMapState = function() {
            // Get the base url of the page
            var baseurl = getBaseUrl();
            
            // Store the map position in the URL
            document.location = baseurl + '#?zoom=' + plugin.map.getZoom() + 
                '&centerX=' + plugin.map.getCenter().lng() + '&centerY=' + 
                plugin.map.getCenter().lat();
        }
        
        /**
         * Plugin map object accessor
         */
        plugin.getMap = function() {
            return plugin.map;
        }
        
        /**
         * Zoom changed listener
         */
        var addZoomChangedListener = function() {
            // Add zoom change event listener
            google.maps.event.addListener(plugin.map, 'zoom_changed', function() {
                plugin.saveMapState();
                plugin.refreshMap();
            });
        }
        
        /**
         * Drag end listener
         */
        var addDragEndListener = function() {
            // Add draggable event listener
            google.maps.event.addListener(plugin.map, 'dragend', function() {
                plugin.saveMapState();
                
                // Get map bounds
                var bounds = plugin.getMap().getBounds();
                // Loop through markers and make them visible
                if (markersArray) {
                    for (i in markersArray) {
                        markersArray[i].setVisible(false);
                        if (bounds.contains(markersArray[i].getPosition())) {
                            markersArray[i].setVisible(true);
                        }
                    }
                }
            });
        }
        
        /**
         * Add bounds changed listener
         */
        var addBoundsChangedListener = function() {
            google.maps.event.addListener(plugin.map, 'bounds_changed', function() {
                plugin.saveMapState();
                if (plugin.lastZoom != plugin.getMap().getZoom()) {
                    plugin.refreshMap();
                    plugin.lastZoom = plugin.getMap().getZoom();
                }
            });
        }
        
        /**
         * Get all of the get parameters from the url
         */
        var getUrlVars =  function() {
            var vars = [], hash;
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
            for(var i = 0; i < hashes.length; i++)
            {
              hash = hashes[i].split('=');
              vars.push(hash[0]);
              vars[hash[0]] = hash[1];
            }
            return vars;
        }
        
        /**
         * Get a single get parameter from a url
         * 
         * @param string name
         */
        var getUrlVar = function(name) {
            return getUrlVars()[name];
        }
    
        /**
         * Array contains function
         * 
         * @param Object obj comparison object
         */
        var _contains = function(a, obj) {
            if (a) {
                var i = a.length;
                while (i--) {
                    if (a[i] === obj) {
                        return true;
                    }
                }
            }
            return false;
        }
        
        /**
         * Get the base url of the page
         */
        var getBaseUrl = function() {
            return document.location.toString().substr(
                0, 
                document.location.toString().indexOf('#')
            );
        }

        // Call constructor
        init();
    }

})(jQuery);

/**
 * Future resources;
 *
 * code.google.com/p/google-maps-utility-library-v3/wiki/Libaries
 */