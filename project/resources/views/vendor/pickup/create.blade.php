@extends('layouts.vendor')
@section('content')
    <div class="gs-vendor-outlet">
        <!-- breadcrumb start  -->
        <div class="gs-vendor-breadcrumb has-mb">
                <div class=" d-flex align-items-center gap-4">
                    <a href="{{route("vendor-pickup-point-index")}}"class="back-btn">
                        <i class="fa-solid fa-arrow-left-long"></i>
                    </a>
                    <h4>@lang('Create Pickup Point')</h4>
                </div>

            <ul class="breadcrumb-menu">
                <li>
                    <a href="{{ route('vendor.dashboard') }}" class="text-capitalize">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="#4C3533" class="home-icon-vendor-panel-breadcrumb">
                            <path
                                d="M9 21V13.6C9 13.0399 9 12.7599 9.109 12.546C9.20487 12.3578 9.35785 12.2049 9.54601 12.109C9.75993 12 10.04 12 10.6 12H13.4C13.9601 12 14.2401 12 14.454 12.109C14.6422 12.2049 14.7951 12.3578 14.891 12.546C15 12.7599 15 13.0399 15 13.6V21M2 9.5L11.04 2.72C11.3843 2.46181 11.5564 2.33271 11.7454 2.28294C11.9123 2.23902 12.0877 2.23902 12.2546 2.28295C12.4436 2.33271 12.6157 2.46181 12.96 2.72L22 9.5M4 8V17.8C4 18.9201 4 19.4802 4.21799 19.908C4.40974 20.2843 4.7157 20.5903 5.09202 20.782C5.51985 21 6.0799 21 7.2 21H16.8C17.9201 21 18.4802 21 18.908 20.782C19.2843 20.5903 19.5903 20.2843 19.782 19.908C20 19.4802 20 18.9201 20 17.8V8L13.92 3.44C13.2315 2.92361 12.8872 2.66542 12.5091 2.56589C12.1754 2.47804 11.8246 2.47804 11.4909 2.56589C11.1128 2.66542 10.7685 2.92361 10.08 3.44L4 8Z"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                </li>
                <li>
                    <a href="{{route("vendor.dashboard")}}" class="text-capitalize">
                        @lang('Dashboard')
                    </a>
                </li>
                <li>
                    <a href="javascript:;" class="text-capitalize"> @lang('Create Pickup Point') </a>
                </li>
            </ul>
        </div>
        <!-- breadcrumb end -->

        <!-- Edit Profile area start  -->
        <div class="vendor-edit-profile-section-wrapper">
            <div class="gs-edit-profile-section">

                <form class="edit-profile-area" action="{{ route('vendor-pickup-point-create') }}" method="POST">
                    @csrf
                    <div class="row">

                        <!-- Search Box for Map -->
                        <div class="form-group">
                            <label for="search-box">@lang('Search Location')</label>
                            <div class="input-group">
                                <input type="text" id="search-box" class="form-control" placeholder="@lang('Search for a location...')">
                                <div class="input-group-append">
                                    <button type="button" id="search-btn" class="btn btn-outline-secondary">
                                        <i class="fa fa-search"></i>
                                    </button>
                                    <button type="button" id="current-location-btn" class="btn btn-outline-primary ml-2">
                                        <i class="fa fa-location-arrow"></i> @lang('Current Location')
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Map Container -->
                        <div class="form-group">
                            <label>@lang('Select Location on Map')</label>
                            <div id="map" style="height: 400px; width: 100%; border: 1px solid #ddd; border-radius: 5px;"></div>
                            <small class="text-muted">@lang('Click on the map to select your pickup location')</small>
                        </div>

                        <!-- Hidden inputs to store coordinates -->
                        <input type="hidden" id="latitude" name="latitude" value="">
                        <input type="hidden" id="longitude" name="longitude" value="">

                        <!-- Location Display -->
                        <div class="form-group">
                            <label for="location">@lang('Selected Location')</label>
                            <input type="text" id="location" class="form-control" placeholder="@lang('Click on map to select location')" 
                                   name="location" readonly>
                            @error('location')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-12 col-sm-12">
                            <button class="template-btn btn-forms" type="submit" id="submit-btn" disabled>
                                @lang('Save')
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Edit Profile area end  -->
    </div>

    <!-- Google Maps API -->
    <script async defer 
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places&callback=initMap">
    </script>

    <script>
        let map;
        let marker;
        let geocoder;
        let autocomplete;

        function initMap() {
            // Default location (you can change this to your preferred default)
            const defaultLocation = { lat: 23.8103, lng: 90.4125 }; // Dhaka, Bangladesh

            // Initialize map
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 13,
                center: defaultLocation,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
            });

            // Initialize geocoder
            geocoder = new google.maps.Geocoder();

            // Initialize autocomplete for search box
            autocomplete = new google.maps.places.Autocomplete(
                document.getElementById('search-box')
            );

            // Try to get user's current location
            getCurrentLocation();

            // Add click listener to map
            map.addListener('click', (event) => {
                setMarker(event.latLng);
                getAddressFromCoordinates(event.latLng);
            });

            // Add listener for autocomplete
            autocomplete.addListener('place_changed', () => {
                const place = autocomplete.getPlace();
                if (place.geometry) {
                    map.setCenter(place.geometry.location);
                    map.setZoom(15);
                    setMarker(place.geometry.location);
                    document.getElementById('location').value = place.formatted_address;
                    updateCoordinates(place.geometry.location);
                    enableSubmitButton();
                }
            });

            // Search button click event
            document.getElementById('search-btn').addEventListener('click', () => {
                const searchText = document.getElementById('search-box').value;
                if (searchText) {
                    geocoder.geocode({ address: searchText }, (results, status) => {
                        if (status === 'OK') {
                            map.setCenter(results[0].geometry.location);
                            map.setZoom(15);
                            setMarker(results[0].geometry.location);
                            document.getElementById('location').value = results[0].formatted_address;
                            updateCoordinates(results[0].geometry.location);
                            enableSubmitButton();
                        } else {
                            alert('@lang("Location not found. Please try a different search term.")');
                        }
                    });
                }
            });

            // Current location button click event
            document.getElementById('current-location-btn').addEventListener('click', () => {
                getCurrentLocation();
            });

            // Enter key press for search box
            document.getElementById('search-box').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('search-btn').click();
                }
            });
        }

        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const userLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        map.setCenter(userLocation);
                        map.setZoom(15);
                        setMarker(new google.maps.LatLng(userLocation.lat, userLocation.lng));
                        getAddressFromCoordinates(new google.maps.LatLng(userLocation.lat, userLocation.lng));
                    },
                    (error) => {
                        console.log('Geolocation error: ', error);
                        // If geolocation fails, keep default location
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 600000
                    }
                );
            }
        }

        function setMarker(location) {
            if (marker) {
                marker.setMap(null);
            }
            marker = new google.maps.Marker({
                position: location,
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP,
                icon: {
                    url: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTE2IDBDOC4yNjggMCAyIDcuMjY4IDIgMTVDMiAyNS41IDE2IDMyIDE2IDMyUzMwIDI1LjUgMzAgMTVDMzAgNy4yNjggMjMuNzMyIDAgMTYgMFpNMTYgMjBDMTMuMjQgMjAgMTEgMTcuNzYgMTEgMTVDMTEgMTIuMjQgMTMuMjQgMTAgMTYgMTBDMTguNzYgMTAgMjEgMTIuMjQgMjEgMTVDMjEgMTcuNzYgMTguNzYgMjAgMTYgMjBaIiBmaWxsPSIjRkY0NDQ0Ii8+Cjwvc3ZnPgo=',
                    scaledSize: new google.maps.Size(32, 32),
                    anchor: new google.maps.Point(16, 32)
                }
            });

            // Add drag listener to marker
            marker.addListener('dragend', (event) => {
                getAddressFromCoordinates(event.latLng);
            });

            updateCoordinates(location);
        }

        function getAddressFromCoordinates(latLng) {
            geocoder.geocode({ location: latLng }, (results, status) => {
                if (status === 'OK') {
                    if (results[0]) {
                        document.getElementById('location').value = results[0].formatted_address;
                        updateCoordinates(latLng);
                        enableSubmitButton();
                    }
                } else {
                    console.log('Geocoder failed due to: ' + status);
                }
            });
        }

        function updateCoordinates(latLng) {
            document.getElementById('latitude').value = latLng.lat();
            document.getElementById('longitude').value = latLng.lng();
        }

        function enableSubmitButton() {
            document.getElementById('submit-btn').disabled = false;
        }

        // Initialize map when page loads
        window.onload = function() {
            // If the Google Maps API hasn't loaded yet, this will be called by the callback
            if (typeof google !== 'undefined') {
                initMap();
            }
        };
    </script>

    <style>
        #map {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .input-group-append {
            display: flex;
        }
        
        #current-location-btn {
            border-left: none;
        }
        
        #submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .pac-container {
            z-index: 9999;
        }
    </style>
@endsection