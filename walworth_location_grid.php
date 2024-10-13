<?php
// Display a grid of 6 listings (2 rows, 3 columns) based on the selected location in Walworth County
function jsmls_display_walworth_location_grid() {
    // Define the Walworth County locations
    $locations = ['Lake Geneva', 'Elkhorn', 'Delavan', 'Williams Bay', 'Fontana', 'Walworth'];

    // Get the selected location from dropdown (default to Lake Geneva)
    $selected_location = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : 'Lake Geneva';

    // Ensure the location is properly URL-encoded
    $encoded_location = urlencode($selected_location);

    // Fetch MLS data for the selected location
    $page = 1;
    $per_page = 6; // We want 6 listings (2 rows of 3 listings)
    $properties = jsmls_fetch_mls_data_by_location($encoded_location, $page, $per_page);

    // Check if there are properties to display
    if (empty($properties)) {
        return '<p>No listings found or failed to fetch data for ' . esc_html($selected_location) . '.</p>';
    }

    // Start building the dropdown and grid with improved CSS styling
    $output = '
    <style>
        .location-dropdown {
            margin-bottom: 20px;
        }
        .walworth-listings-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 columns */
            gap: 20px;
        }
        .walworth-listing-card {
            display: flex;
            flex-direction: column;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            background-color: #fff;
            border: 1px solid #ddd;
        }
        .walworth-listing-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .walworth-listing-details {
            padding: 20px;
        }
        .walworth-listing-details h3 {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .walworth-listing-details p {
            font-size: 0.9rem;
            color: #555;
        }
        .walworth-listing-price {
            color: #bf974f;
            font-weight: bold;
            font-size: 1.4rem;
            margin-top: 10px;
        }
        .view-details {
            display: inline-block;
            margin-top: 10px;
            color: #3498db;
            font-weight: bold;
            text-decoration: none;
        }
        .view-details:hover {
            text-decoration: underline;
        }

        /* Responsive grid for mobile */
        @media (max-width: 768px) {
            .walworth-listings-container {
                grid-template-columns: 1fr; /* Single column on mobile */
            }
        }
    </style>';

    // Create the dropdown form for selecting location
    $output .= '
    <form class="location-dropdown" method="GET" action="">
        <label for="location">Select Location: </label>
        <select id="location" name="location" onchange="this.form.submit()">
    ';

    foreach ($locations as $location) {
        $selected = ($selected_location === $location) ? 'selected' : '';
        $output .= '<option value="' . esc_html($location) . '" ' . $selected . '>' . esc_html($location) . '</option>';
    }

    $output .= '</select>
    </form>';

    // Start the listing grid
    $output .= '<div class="walworth-listings-container">';

    // Loop through properties and build each listing card
    foreach ($properties as $property) {
        // Generate the listing detail page URL with ListingKey as a query parameter
        $listing_url = get_site_url() . '/property-detail?listing_key=' . urlencode($property['ListingKey']);

        // Listing Card
        $output .= '<div class="walworth-listing-card">';
        $media_url = jsmls_fetch_media($property['ListingKey']);
        $image = $media_url ? esc_url($media_url) : 'https://via.placeholder.com/400x300';
        $output .= '<img src="' . $image . '" alt="Property Image">';

        // Listing Details
        $output .= '<div class="walworth-listing-details">';
        $output .= '<h3>' . esc_html($property['StreetNumber'] . ' ' . $property['StreetName']) . '</h3>';
        $output .= '<p>' . esc_html($property['City']) . '</p>';
        $output .= '<p>' . esc_html($property['BedroomsTotal']) . ' Beds | ' . esc_html($property['BathroomsTotalInteger']) . ' Baths | ' . esc_html($property['BuildingAreaTotal']) . ' sqft</p>';
        $output .= '<div class="walworth-listing-price">$' . number_format($property['ListPrice']) . '</div>';
        $output .= '<a class="view-details" href="' . esc_url($listing_url) . '">View Details</a>';
        $output .= '</div>';
        $output .= '</div>';
    }

    $output .= '</div>'; // End of listings container

    // Return the final output
    return $output;
}

// Register the shortcode
add_shortcode('walworth_location_grid', 'jsmls_display_walworth_location_grid');

// Function to fetch MLS data by location
function jsmls_fetch_mls_data_by_location($location, $page, $per_page) {
    // URL-encode the location properly to handle spaces and special characters
    $encoded_location = urlencode($location);

    // Construct the API URL for fetching listings filtered by City
    $url = 'https://api.mlsaligned.com/reso/odata/Property?$filter=City%20eq%20%27' . $encoded_location . '%27&$top=' . $per_page . '&$skip=' . (($page - 1) * $per_page);

    // Make the API request with necessary headers
    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Bearer {YOUR_ACCESS_TOKEN}',
            'OUID'          => '{YOUR_OUID}',
            'MLS-Aligned-User-Agent' => '{YOUR_USER_AGENT}',
            'Accept'        => 'application/json',
        ),
    ));

    // Check for API request errors
    if (is_wp_error($response)) {
        return [];
    }

    // Retrieve and decode the response body
    $response_body = wp_remote_retrieve_body($response);
    return json_decode($response_body, true)['value'] ?? [];
}
