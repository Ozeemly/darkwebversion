@extends('layouts.front')
@section('content')
    <div class="gs-user-panel-review wow-replaced" data-wow-delay=".1s">
        <div class="container">
            <div class="d-flex">
                <!-- sidebar -->
                @include('includes.rider.sidebar')
                @php
                    $order = $data->order;
                    
                    // Get coordinates for pickup and delivery locations
                    $pickupLocation = $data->pickup->location ?? '';
                    $deliveryLocation = $order->customer_address ?? '';
                    
                    // Format coordinates for map use (assuming they are stored in format "lat,lng" or need to be geocoded)
                    $pickupCoords = !empty($data->pickup->coordinates) ? $data->pickup->coordinates : '0,0';
                    $deliveryCoords = !empty($order->customer_coordinates) ? $order->customer_coordinates : '0,0';
                    
                    // Parse order status for displaying appropriate status badge
                    $statusClass = [
                        'pending' => 'warning',
                        'accepted' => 'info',
                        'rejected' => 'danger',
                        'completed' => 'success',
                    ][$data->status] ?? 'secondary';
                @endphp
                <!-- main content -->
                <div class="gs-dashboard-user-content-wrapper gs-dashboard-outlet">
                    <div class="ud-page-title-box gap-4">
                        <!-- mobile sidebar trigger btn -->
                        <a href="{{ url()->previous() }}" class="back-btn">
                            <i class="fa-solid fa-arrow-left-long"></i>
                        </a>

                        <h3 class="ud-page-title">@lang('Delivery Details')</h3>
                        <span class="badge bg-{{ $statusClass }} ms-2">{{ ucfirst($data->status) }}</span>
                    </div>

                    <!-- Delivery status timeline -->
                    <div class="delivery-timeline my-4">
                        <div class="progress-track">
                            <ul id="progressbar">
                                <li class="{{ in_array($data->status, ['pending', 'accepted', 'completed']) ? 'active' : '' }}">@lang('Order Received')</li>
                                <li class="{{ in_array($data->status, ['accepted', 'completed']) ? 'active' : '' }}">@lang('Picked Up')</li>
                                <li class="{{ $data->status == 'completed' ? 'active' : '' }}">@lang('Delivered')</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Accept and reject button -->
                    <div class="accept-reject-btn my-3">
                        @if ($data->status == 'pending')
                            <a class="template-btn green-btn"
                                href="{{ route('rider-order-delivery-accept', $data->id) }}">@lang('Accept')</a>
                            <a class="template-btn red-btn"
                                href="{{ route('rider-order-delivery-reject', $data->id) }}">@lang('Reject')</a>
                        @elseif($data->status == 'accepted')
                            <a class="template-btn green-btn"
                                href="{{ route('rider-order-delivery-complete', $data->id) }}">@lang('Make Delivered')</a>
                            <button class="template-btn blue-btn" id="updateLocationBtn">@lang('Update My Location')</button>
                        @elseif($data->status == 'rejected')
                            <button class="template-btn red-btn">@lang('Rejected')</button>
                        @else
                            <button class="template-btn green-btn"> @lang('Delivered')</button>
                        @endif
                    </div>

                    <!-- Map showing delivery path -->
                    <div class="delivery-map-container mb-4">
                        <h5 class="mb-3"><i class="fa-solid fa-route me-2"></i>@lang('Delivery Route')</h5>
                        <div id="deliveryMap" style="height: 350px; width: 100%; border-radius: 10px;"></div>
                    </div>

                    <div class="delivery-details">
                        <div class="row g-4 my-3">
                            <div class="col-md-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fa-solid fa-location-dot me-2"></i>@lang('Delivery Address')</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="delivery-address-info">
                                            <div class="account-info-item">
                                                <span class="info-title">@lang('Name:') </span>
                                                <span class="info-content">{{ $order->customer_name }}</span>
                                            </div>
                                            <div class="account-info-item">
                                                <span class="info-title">@lang('Email:') </span>
                                                <span class="info-content">{{ $order->customer_email }}</span>
                                            </div>
                                            <div class="account-info-item">
                                                <span class="info-title">@lang('Phone:') </span>
                                                <span class="info-content">{{ $order->customer_phone }}</span>
                                            </div>
                                            <div class="account-info-item">
                                                <span class="info-title">@lang('City:') </span>
                                                <span class="info-content">{{ $order->customer_address }}</span>
                                            </div>
                                            <div class="account-info-item">
                                                <span class="info-title">@lang('Address:') </span>
                                                <span class="info-content">{{ $order->customer_city }}-{{ $order->customer_zip }}</span>
                                            </div>
                                            <div class="mt-3">
                                                <a href="tel:{{ $order->customer_phone }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa-solid fa-phone me-1"></i> @lang('Call Customer')
                                                </a>
                                                <a href="https://maps.google.com/?q={{ $deliveryCoords }}" target="_blank" class="btn btn-sm btn-outline-success ms-2">
                                                    <i class="fa-solid fa-map-location-dot me-1"></i> @lang('Open in Maps')
                                                </a>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="mb-0"><i class="fa-solid fa-store me-2"></i>@lang('Vendor Information')</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="delivery-address-info">
                                            <div class="account-info-item">
                                                <span class="info-title">@lang('Shop Name:') </span>
                                                <span class="info-content">{{ $data->vendor->shop_name }}</span>
                                            </div>
                                            <div class="account-info-item">
                                                <span class="info-title">@lang('Email:') </span>
                                                <span class="info-content">{{ $data->vendor->email }}</span>
                                            </div>
                                            <div class="account-info-item">
                                                <span class="info-title">@lang('Phone:') </span>
                                                <span class="info-content">{{ $data->vendor->phone }}</span>
                                            </div>
                                            @if ($data->vendor->city)
                                                <div class="account-info-item">
                                                    <span class="info-title">@lang('City:') </span>
                                                    <span class="info-content">{{ $data->vendor->city }}</span>
                                                </div>
                                            @endif
                                            @if ($data->vendor->address)
                                                <div class="account-info-item">
                                                    <span class="info-title">@lang('Address:') </span>
                                                    <span class="info-content">{{ $data->vendor->address }}</span>
                                                </div>
                                            @endif

                                            <div class="account-info-item">
                                                <span class="info-title"><strong>@lang('Pickup Location:')</strong> </span>
                                                <span class="info-content">{{ $data->pickup->location }}</span>
                                            </div>
                                            <div class="mt-3">
                                                <a href="tel:{{ $data->vendor->phone }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa-solid fa-phone me-1"></i> @lang('Call Vendor')
                                                </a>
                                                <a href="https://maps.google.com/?q={{ $pickupCoords }}" target="_blank" class="btn btn-sm btn-outline-success ms-2">
                                                    <i class="fa-solid fa-map-location-dot me-1"></i> @lang('Open in Maps')
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ordered-products mt-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fa-solid fa-box me-2"></i>@lang('Ordered Products')</h5>
                            </div>
                            <div class="card-body">
                                <div class="user-table-wrapper all-orders-table-wrapper" data-wow-delay=".1s">
                                    <div class="user-table table-responsive position-relative">
                                        <table class="gs-data-table custom-table-rider w-100">
                                            <tr class="ordered-tbg">
                                                <th><span class="title">@lang('ID#')</span></th>
                                                <th><span class="title">@lang('Product Name')</span></th>
                                                <th><span class="title">@lang('Details')</span></th>
                                            </tr>
                                            @php
                                                $extra_price = 0;
                                            @endphp
                                            @foreach (json_decode($order->cart, true)['items'] as $product)
                                                @if ($product['user_id'] == $data->vendor_id)
                                                    <tr>
                                                        <td data-label="{{ ('ID#') }}">
                                                            <div>
                                                            <span class="title">
                                                                {{ $product['item']['id'] }}
                                                            </span>
                                                            </div>
                                                        </td>
                                                        <td data-label="{{ ('Name') }}">
                                                          <span class="title">
                                                            {{ mb_strlen($product['item']['name'], 'UTF-8') > 50
                                                            ? mb_substr($product['item']['name'], 0, 50, 'UTF-8') . '...'
                                                            : $product['item']['name'] }}
                                                          </span>
                                                        </td>
                                                        <td data-label="{{ ('Details') }}">
                                                            <div>
                                                                <b>{{ ('Quantity') }}</b>: {{ $product['qty'] }} <br>
                                                                @if (!empty($product['size']))
                                                                    <b>{{ ('Size') }}</b>:
                                                                    {{ $product['item']['measure'] }}{{ str_replace('-', ' ', $product['size']) }}
                                                                    <br>
                                                                @endif
                                                                @if (!empty($product['color']))
                                                                    <div class="d-flex mt-2">
                                                                        <b>{{ ('Color') }}</b>: <span id="color-bar"
                                                                            style="width: 20px; height: 20px; display: inline-block; vertical-align: middle; border-radius: 50%; background: #{{ $product['color'] }};"></span>
                                                                    </div>
                                                                @endif
                                                                @if (!empty($product['keys']))
                                                                    @foreach (array_combine(explode(',', $product['keys']), explode(',', $product['values'])) as $key => $value)
                                                                        <b>{{ ucwords(str_replace('_', ' ', $key)) }} : </b>
                                                                        {{ $value }} <br>
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </table>
                                    </div>
                                </div>

                                <div class="collection-info mt-4 text-center p-3 bg-light rounded">
                                    @php
                                        $order_shipping = json_decode($order->vendor_shipping_id, true) ?? [];
                                        $order_package = json_decode($order->vendor_packing_id, true) ?? [];
    
                                        // Retrieve vendor-specific shipping and packing IDs, defaulting to null if not found
                                        $vendor_shipping_id = $order_shipping[$order->vendor_id] ?? null;
                                        $vendor_package_id = $order_package[$order->vendor_id] ?? null;
    
                                        // Retrieve the Shipping and Package models, or null if not found
                                        $shipping = $vendor_shipping_id ? App\Models\Shipping::find($vendor_shipping_id) : null;
                                        $package = $vendor_package_id ? App\Models\Package::find($vendor_package_id) : null;
    
                                        // Calculate shipping and packing costs, defaulting to 0 if models are not found
                                        $shipping_cost = $shipping ? $shipping->price : 0;
                                        $packing_cost = $package ? $package->price : 0;
    
                                        // Total extra cost
                                        $extra_price = $shipping_cost + $packing_cost;
                                        
                                        // Calculate total amount to collect
                                        $total_collection = $order->method == 'Cash On Delivery' ? 
                                            ($order->vendororders->where('user_id', $data->vendor_id)->sum('price') + $extra_price) * $data->order->currency_value : 0;
                                    @endphp

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6>@lang('Payment Method')</h6>
                                                    <p class="mb-0 badge {{ $order->method == 'Cash On Delivery' ? 'bg-success' : 'bg-primary' }}">
                                                        {{ $order->method }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6>@lang('Collection Amount')</h6>
                                                    <p class="mb-0 fw-bold">
                                                        @if ($order->method == 'Cash On Delivery')
                                                            {{ \PriceHelper::showAdminCurrencyPrice($total_collection, $order->currency_sign) }}
                                                        @else
                                                            {{ __('N/A') }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery notes section -->
                    @if ($data->status == 'accepted')
                    <div class="delivery-notes mt-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fa-solid fa-note-sticky me-2"></i>@lang('Delivery Notes')</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('rider-order-delivery-note', $data->id) }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="deliveryNote">@lang('Add Note for This Delivery')</label>
                                        <textarea class="form-control" id="deliveryNote" name="note" rows="3" 
                                            placeholder="@lang('Add any notes or special instructions for this delivery')">{{ $data->note ?? '' }}</textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary mt-3">@lang('Save Note')</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Issue reporting section -->
                    @if ($data->status == 'accepted')
                    <div class="issue-reporting mt-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="fa-solid fa-triangle-exclamation me-2"></i>@lang('Report Issue')</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('rider-order-delivery-issue', $data->id) }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="issueType">@lang('Issue Type')</label>
                                        <select class="form-control" id="issueType" name="issue_type">
                                            <option value="customer_not_available">@lang('Customer Not Available')</option>
                                            <option value="wrong_address">@lang('Wrong Address')</option>
                                            <option value="package_damaged">@lang('Package Damaged')</option>
                                            <option value="other">@lang('Other')</option>
                                        </select>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label for="issueDescription">@lang('Issue Description')</label>
                                        <textarea class="form-control" id="issueDescription" name="issue_description" rows="3"
                                            placeholder="@lang('Please describe the issue in detail')"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger mt-3">@lang('Report Issue')</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <!-- Add Google Maps API Script -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', '') }}&libraries=places,directions&callback=initMap" async defer></script>
    
    <script>
        // Global variables for map functionality
        let map;
        let directionsService;
        let directionsRenderer;
        let currentMarker;
        let pickupMarker;
        let deliveryMarker;
        
        // Initialize the map
        function initMap() {
            // Parse coordinates
            const pickupCoords = "{{ $pickupCoords }}".split(',');
            const deliveryCoords = "{{ $deliveryCoords }}".split(',');
            
            // Create LatLng objects
            const pickupLatLng = new google.maps.LatLng(parseFloat(pickupCoords[0]), parseFloat(pickupCoords[1]));
            const deliveryLatLng = new google.maps.LatLng(parseFloat(deliveryCoords[0]), parseFloat(deliveryCoords[1]));
            
            // Initialize map centered between pickup and delivery
            map = new google.maps.Map(document.getElementById("deliveryMap"), {
                zoom: 12,
                center: {
                    lat: (parseFloat(pickupCoords[0]) + parseFloat(deliveryCoords[0])) / 2,
                    lng: (parseFloat(pickupCoords[1]) + parseFloat(deliveryCoords[1])) / 2
                }
            });
            
            // Initialize directions service
            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                map: map,
                suppressMarkers: true // We'll add custom markers
            });
            
            // Create custom markers
            pickupMarker = new google.maps.Marker({
                position: pickupLatLng,
                map: map,
                icon: {
                    url: "https://maps.google.com/mapfiles/ms/icons/green-dot.png",
                    labelOrigin: new google.maps.Point(16, 40)
                },
                label: {
                    text: "{{ __('Pickup') }}",
                    color: "#000000",
                    fontWeight: "bold"
                }
            });
            
            deliveryMarker = new google.maps.Marker({
                position: deliveryLatLng,
                map: map,
                icon: {
                    url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
                    labelOrigin: new google.maps.Point(16, 40)
                },
                label: {
                    text: "{{ __('Delivery') }}",
                    color: "#000000",
                    fontWeight: "bold"
                }
            });
            
            // Create info windows
            const pickupInfo = new google.maps.InfoWindow({
                content: '<div><strong>{{ __("Pickup") }}</strong><br>{{ $data->pickup->location }}</div>'
            });
            
            const deliveryInfo = new google.maps.InfoWindow({
                content: '<div><strong>{{ __("Delivery") }}</strong><br>{{ $order->customer_address }}</div>'
            });
            
            // Add click listeners to markers
            pickupMarker.addListener("click", () => {
                pickupInfo.open(map, pickupMarker);
            });
            
            deliveryMarker.addListener("click", () => {
                deliveryInfo.open(map, deliveryMarker);
            });
            
            // Calculate and display route
            calculateAndDisplayRoute(pickupLatLng, deliveryLatLng);
            
            // Set up update location button
            document.getElementById('updateLocationBtn')?.addEventListener('click', updateCurrentLocation);
            
            // If delivery is in progress, periodically update rider location
            @if($data->status == 'accepted')
            // Get current location initially
            updateCurrentLocation();
            
            // Then update every 30 seconds
            setInterval(updateCurrentLocation, 30000);
            @endif
        }
        
        // Calculate and display route between two points
        function calculateAndDisplayRoute(origin, destination) {
            directionsService.route(
                {
                    origin: origin,
                    destination: destination,
                    travelMode: google.maps.TravelMode.DRIVING,
                },
                (response, status) => {
                    if (status === "OK") {
                        directionsRenderer.setDirections(response);
                        
                        // Calculate and display ETA
                        const route = response.routes[0];
                        const leg = route.legs[0];
                        
                        // Display distance and duration
                        const infoDiv = document.createElement('div');
                        infoDiv.className = 'bg-light p-2 mt-2 rounded text-center';
                        infoDiv.innerHTML = `
                            <strong>{{ __('Distance') }}:</strong> ${leg.distance.text} | 
                            <strong>{{ __('Estimated Time') }}:</strong> ${leg.duration.text}
                        `;
                        
                        // Insert after the map
                        const mapContainer = document.getElementById('deliveryMap');
                        mapContainer.parentNode.insertBefore(infoDiv, mapContainer.nextSibling);
                    } else {
                        console.error("Directions request failed due to " + status);
                    }
                }
            );
        }
        
        // Update current rider location
        function updateCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const currentLatLng = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        
                        // Update or create current location marker
                        if (currentMarker) {
                            currentMarker.setPosition(currentLatLng);
                        } else {
                            currentMarker = new google.maps.Marker({
                                position: currentLatLng,
                                map: map,
                                icon: {
                                    url: "https://maps.google.com/mapfiles/ms/icons/blue-dot.png",
                                    labelOrigin: new google.maps.Point(16, 40)
                                },
                                label: {
                                    text: "{{ __('You') }}",
                                    color: "#000000",
                                    fontWeight: "bold"
                                }
                            });
                        }
                        
                        // Send location update to server
                        @if($data->status == 'accepted')
                        fetch('{{ route("rider-update-location", $data->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Location updated successfully');
                            }
                        })
                        .catch(error => {
                            console.error('Error updating location:', error);
                        });
                        @endif
                    },
                    (error) => {
                        console.error("Error getting location: ", error);
                        alert("{{ __('Unable to get your location. Please enable location services.') }}");
                    }
                );
            } else {
                alert("{{ __('Geolocation is not supported by this browser.') }}");
            }
        }
        
        // Add CSS for the timeline
        document.addEventListener('DOMContentLoaded', function() {
            const style = document.createElement('style');
            style.textContent = `
                /* Timeline styling */
                #progressbar {
                    margin-bottom: 30px;
                    overflow: hidden;
                    color: lightgrey;
                    padding-left: 0px;
                    display: flex;
                    justify-content: space-between;
                }
                
                #progressbar li {
                    list-style-type: none;
                    font-size: 14px;
                    width: 33.33%;
                    float: left;
                    position: relative;
                    font-weight: 500;
                    text-align: center;
                }
                
                #progressbar li:before {
                    content: '\\f111';
                    font-family: 'Font Awesome 5 Free';
                    font-weight: 900;
                    font-size: 18px;
                    width: 50px;
                    height: 50px;
                    line-height: 45px;
                    display: block;
                    background: #fff;
                    border: 2px solid #ddd;
                    border-radius: 50%;
                    margin: 0 auto 10px auto;
                    padding: 0px;
                }
                
                #progressbar li:after {
                    content: '';
                    width: 100%;
                    height: 4px;
                    background: #ddd;
                    position: absolute;
                    left: 0;
                    top: 25px;
                    z-index: -1;
                }
                
                #progressbar li:first-child:after {
                    width: 50%;
                    left: 50%;
                }
                
                #progressbar li:last-child:after {
                    width: 50%;
                    right: 50%;
                }
                
                #progressbar li.active {
                    color: #3498db;
                }
                
                #progressbar li.active:before,
                #progressbar li.active:after {
                    background: #3498db;
                    color: white;
                    border-color: #3498db;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
@endsection