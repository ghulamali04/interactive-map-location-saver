<html>
<?php

include 'model/get_locations.php';
include 'model/save_location.php';
include 'db.php';
include 'connection.php';
include 'function/ip_address.php';
//get ip address
$ip_address = get_client_ip();
?>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo $url; ?>assets/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>

<body>

    <img src="<?php echo $url ?>assets/ajax-loader.gif" class="d-none gifimg" height="100" width="" style="position: fixed;left: 47%;top:40%;z-index: 1000">
    <div class="overlay d-none" style="position: fixed;width: 100%;height: 100%;background: rgba(0,0,0,0.3);top: 0;z-index: 500"></div>
    <input id="pac-input" class="pac-controls form-control rounded-0" type="text" placeholder="Find Location" />
    <div id="map"></div>


    <!--scripts-->
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=en&key=YOUR_KEY&libraries=places"></script>
    <script>
        var infowindow;
        var map;
        var red_icon = 'http://maps.google.com/mapfiles/ms/icons/red-dot.png';
        var purple_icon = 'http://maps.google.com/mapfiles/ms/icons/purple-dot.png';
        var locations = <?php get_all_locations($con, $ip_address); ?>;
        var size = Object.keys(locations).length;

        var myOptions = {
            zoom: 3,
            center: new google.maps.LatLng(0, 0),
            mapTypeId: 'roadmap'
        };

        map = new google.maps.Map(document.getElementById('map'), myOptions);
        // Create the search box and link it to the UI element.
        const input = document.getElementById("pac-input");
        const searchBox = new google.maps.places.SearchBox(input);

        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
        // Bias the SearchBox results towards current map's viewport.
        map.addListener("bounds_changed", () => {
            searchBox.setBounds(map.getBounds());
        });

        /**
         * Global marker object that holds all markers.
         * @type {Object.<string, google.maps.LatLng>}
         */

        var markers = {};

 searchBox.addListener("places_changed", () => {
    const places = searchBox.getPlaces();

    if (places.length == 0) {
      return;
    }

    // Clear out the old markers.
   
    // For each place, get the icon, name and location.
    const bounds = new google.maps.LatLngBounds();

    places.forEach((place) => {
      if (!place.geometry || !place.geometry.location) {
        console.log("Returned place contains no geometry");
        return;
      }

      let icon = {
        url: place.icon,
        size: new google.maps.Size(71, 71),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(17, 34),
        scaledSize: new google.maps.Size(25, 25),
      };

    
      if (place.geometry.viewport) {
        // Only geocodes have viewport.
        bounds.union(place.geometry.viewport);
      } else {
        bounds.extend(place.geometry.location);
      }
    });
    map.fitBounds(bounds);
  });
        var getMarkerUniqueId = function(lat, lng) {
            return lat + '_' + lng;
        };

        /**
         * Creates an instance of google.maps.LatLng by given lat and lng values and returns it.
         * This function can be useful for getting new coordinates quickly.
         * @param {!number} lat Latitude.
         * @param {!number} lng Longitude.
         * @return {google.maps.LatLng} An instance of google.maps.LatLng object
         */
        var getLatLng = function(lat, lng) {
            return new google.maps.LatLng(lat, lng);
        };

        var marker;
        /**
         * Binds click event to given map and invokes a callback that appends a new marker to clicked location.
         */
        var addMarker = google.maps.event.addListener(map, 'click', function(e) {


            var lat = e.latLng.lat(); // lat of clicked point
            var lng = e.latLng.lng(); // lng of clicked point


            if (!$.isEmptyObject(markers)) {
               
                marker.setPosition(e.latLng);
            } else {
                var markerId = getMarkerUniqueId(lat, lng); // an that will be used to cache this marker in markers object.
                marker = new google.maps.Marker({
                    position: getLatLng(lat, lng),
                    map: map,
                    animation: google.maps.Animation.DROP,
                    id: 'marker_' + markerId,
                    html: "    <div class='card adjust-card border-0' id='info_" + markerId + "'>\n" +
                        " <div class='form-row py-1'> " +
                        "<div class='col-12 '>" +
                        "<label for='manual_description'>Leave a Message</label>" +
                        "<textarea class='form-control rounded-0' id='manual_description' ></textarea>" +
                        "</div>" +
                        " </div>" +
                        " <div class='form-row py-1'> " +
                        "<div class='col-12 '>" +
                        "<label for='visitor_photo'>Upload Your Photo</label>" +
                        "<input type='file' class='form-control rounded-0' id='visitor_photo' name='visitor_photo' >" +
                        "</div>" +
                        " </div>" +
                        " <div class='form-row py-1 text-center justify-content-center'>" +
                        "<div class='col-12 '> <input type='button' class='btn rounded-0 px-4 btn-clr text-white' data-val='1' id='save' value='Save' onclick='saveData(" + lat + "," + lng + ")'/></div>" +
                        " </div>" +
                        "    </div> "
                });
                markers[markerId] = marker;
                bindMarkerEvents(marker);
                bindMarkerinfo(marker);
            }


        });

        var bindMarkerinfo = function(marker) {
            google.maps.event.addListener(marker, "click", function(point) {
                var markerId = getMarkerUniqueId(point.latLng.lat(), point.latLng.lng()); // get marker id by using clicked point's coordinate
                var marker = markers[markerId];
                infowindow = new google.maps.InfoWindow();
                infowindow.setContent(marker.html);
                infowindow.open(map, marker);

            });
        };

        var bindMarkerEvents = function(marker) {
            google.maps.event.addListener(marker, "rightclick", function(point) {
                var markerId = getMarkerUniqueId(point.latLng.lat(), point.latLng.lng()); // get marker id by using clicked point's coordinate
                var marker = markers[markerId];
                removeMarker(marker, markerId);
            });
        };


        var removeMarker = function(marker, markerId) {
            marker.setMap(null);
            delete markers[markerId];
        };

        /**
         * loop through (Mysql) dynamic locations to add markers to map.
         */
        var i;
        var confirmed = 0;

        for (i = 0; i < locations.length; i++) {
            var markerId = getMarkerUniqueId(locations[i][2], locations[i][3]);
            var html_content = "";

            if (locations[i][1] == '<?php echo $ip_address; ?>') {
                html_content = "    <div class='card adjust-card border-0' id='info_" + markerId + "'>\n" +
                    " <div class='container'> <img class='img-fluid d-block adjust-card-img' src='<?php $url; ?>media/" + locations[i][6] + "'  style='width: 16rem;height: 10rem;'></div>" +
                    " <div class='form-row py-1'> " +
                    "<div class='col-12 '>" +
                    "<label for='manual_description'>Leave a Message</label>" +
                    "<textarea class='form-control rounded-0' id='manual_description' >" + locations[i][4] + "</textarea>" +
                    "</div>" +
                    " </div>" +
                    " <div class='form-row py-1'> " +
                    "<div class='col-12 '>" +
                    "<label for='visitor_photo'>Upload Your Photo</label>" +
                    "<input type='file' class='form-control rounded-0' id='visitor_photo' name='visitor_photo' >" +
                    "</div>" +
                    " </div>" +
                    " <div class='form-row py-1 text-center justify-content-center'>" +
                    "<div class='col-12 '> <input type='button' class='btn rounded-0 px-4 btn-clr text-white' data-val='0' id='save' value='Update' onclick='saveData(" + locations[i][2] + "," + locations[i][3] + ")'/></div>" +
                    " </div>" +
                    "    </div> "

            } else {
                html_content = "<div class='card adjust-card'>\n" +
                    "<img class='img-fluid adjust-card-img d-block' src='<?php $url; ?>media/" + locations[i][6] + "' alt='not found''>" +
                    "<div class='card-body'>" +
                    "<h6 class='card-title'>Visitor's Message</h6>" +
                    "<p class='card-text'>" + locations[i][4] + "</p>" +
                    "</div>" +
                    "</div>"
            }
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(locations[i][2], locations[i][3]),
                map: map,
                icon: red_icon,
                html: html_content
            });

            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                    infowindow = new google.maps.InfoWindow();
                    confirmed = locations[i][4] === '1' ? 'checked' : 0;
                    $("#confirmed").prop(confirmed, locations[i][4]);
                    $("#id").val(locations[i][0]);
                    $("#description").val(locations[i][3]);
                    $("#form").show();
                    //console.log(marker)
                    infowindow.setContent(marker.html);
                    infowindow.open(map, marker);
                }
            })(marker, i));

            if (locations[i][1] == '<?php echo $ip_address; ?>') {
                markers[markerId] = (marker);
            }
            
        }
        
        /**
         * SAVE save marker from map.
         * @param lat  A latitude of marker.
         * @param lng A longitude of marker.
         */
        function saveData(lat, lng) {
            if (lat != marker.getPosition().lat() && lng != marker.getPosition().lng()) {
                new_lat = marker.getPosition().lat();
                new_lng = marker.getPosition().lng();
            } else {
                new_lat = lat;
                new_lng = lng;
            }
            var url = '<?php echo $url; ?>model/save_location.php'
            var description = document.getElementById('manual_description').value;
            var file_data = $("#visitor_photo").prop('files')[0];
            var valid = check(description, file_data);
            if (valid == true) {


                var form_data = new FormData();
                form_data.append('description', description);
                form_data.append('lat', new_lat);
                form_data.append('lng', new_lng);
                if (file_data) {
                    form_data.append('visitor_photo', file_data);
                    var fileType = file_data.type;

                    var match = ['image/jpeg', 'image/png', 'image/jpg'];
                    if (!((fileType == match[0]) || (fileType == match[1]) || (fileType == match[2]))) {
                        alert('Sorry, only JPG, JPEG, & PNG files are allowed to upload.');
                        $("#visitor_photo").val('');
                        return false;
                    }
                }


                $.ajax({
                    type: 'POST',
                    url: url,
                    contentType: false,
                    cache: false,
                    processData: false,
                    data: form_data,
                    beforeSend() {
                        $('.gifimg').removeClass('d-none');
                        $('.overlay').removeClass('d-none');
                    },
                    success: (res) => {
                        //console.log(res)
                        var markerId = getMarkerUniqueId(lat, lng); // get marker id by using clicked point's coordinate
                        var manual_marker = markers[markerId]; // find marker
                        manual_marker.setIcon(red_icon);
                        infowindow.close();
                        infowindow.setContent("<div class='container' > <span style=' color: #020202; font-size: 25px;'>saved!</span> </div>");
                        infowindow.open(map, manual_marker);
                        //start (append data to html)...........................................
                        var locations = JSON.parse(res);
                        var i;
                        var confirmed = 0;

                        function setMapOnAll() {
                            for (const prop in markers) {

                                markers[prop].setMap(null);
                            }
                            markers = {};
                        }
                        setMapOnAll();
                        for (i = 0; i < locations.length; i++) {
                            var markerId = getMarkerUniqueId(locations[i][2], locations[i][3]);
                            var html_content = "";
                            if (locations[i][1] == '<?php echo $ip_address; ?>') {
                                html_content = "    <div class='card adjust-card border-0' id='info_" + markerId + "'>\n" +
                                    " <div class='container'> <img class='img-fluid d-block adjust-card-img' src='<?php $url; ?>media/" + locations[i][6] + "'  style='width: 16rem;height: 20rem;'></div>" +
                                    " <div class='form-row py-1'> " +
                                    "<div class='col-12 '>" +
                                    "<label for='manual_description'>Leave a Message</label>" +
                                    "<textarea class='form-control rounded-0' id='manual_description' >" + locations[i][4] + "</textarea>" +
                                    "</div>" +
                                    " </div>" +
                                    " <div class='form-row py-1'> " +
                                    "<div class='col-12 '>" +
                                    "<label for='visitor_photo'>Upload Your Photo</label>" +
                                    "<input type='file' class='form-control rounded-0' id='visitor_photo' name='visitor_photo' >" +
                                    "</div>" +
                                    " </div>" +
                                    " <div class='form-row py-1 text-center justify-content-center'>" +
                                    "<div class='col-12 '> <input type='button' class='btn rounded-0 px-4 btn-clr text-white' data-val='0' id='save' value='Update' onclick='saveData(" + locations[i][2] + "," + locations[i][3] + ")'/></div>" +
                                    " </div>" +
                                    "    </div> "
                            } else {
                                html_content = "<div class='card adjust-card'>\n" +
                                    "<img class='img-fluid adjust-card-img d-block' src='<?php $url; ?>media/" + locations[i][6] + "' alt='not found''>" +
                                    "<div class='card-body'>" +
                                    "<h6 class='card-title'>Visitor's Message</h6>" +
                                    "<p class='card-text'>" + locations[i][4] + "</p>" +
                                    "</div>" +
                                    "</div>"
                            }
                            marker = new google.maps.Marker({
                                position: new google.maps.LatLng(locations[i][2], locations[i][3]),
                                map: map,
                                icon: red_icon,
                                html: html_content
                            });

                            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                                return function() {
                                    infowindow = new google.maps.InfoWindow();
                                    confirmed = locations[i][4] === '1' ? 'checked' : 0;
                                    $("#confirmed").prop(confirmed, locations[i][4]);
                                    $("#id").val(locations[i][0]);
                                    $("#description").val(locations[i][3]);
                                    $("#form").show();
                                    infowindow.setContent(marker.html);
                                    infowindow.open(map, marker);
                                }
                            })(marker, i));

                            if (locations[i][1] == '<?php echo $ip_address; ?>') {

                                markers[markerId] = (marker);
                            }
                        }
                        //end...............................................
                        $('.gifimg').addClass('d-none');
                        $('.overlay').addClass('d-none');
                    },
                    error: (res) => {
                        console.log(res)
                    }
                })
            }

        }

        function check(desc, img) {
            if ($("#save").attr('data-val') == '1') {
                if (!(desc && img)) {
                    alert("Write Description & Upload your picture!");
                    return false;
                } else {
                    return true;
                }
            } else if ($("#save").attr('data-val') == '0') {
                if (!desc) {
                    alert("Description required!");
                    return false;
                } else {
                    return true;
                }
            }
        }
    </script>

</body>

</html>