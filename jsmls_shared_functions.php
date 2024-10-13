<?php
// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Fetch media for a specific property with caching
function jsmls_fetch_media( $listing_key ) {
    $cached_media_url = get_transient( 'jsmls_media_' . $listing_key );
    if ( $cached_media_url ) {
        return $cached_media_url;
    }

    $url = 'https://api.example.com/reso/odata/Property(' . $listing_key . ')/Media'; // Replace with your API endpoint
    $response = wp_remote_get( $url, array(
        'headers' => array(
            'Authorization' => 'Bearer YOUR_API_TOKEN', // Replace with your API token
            'OUID'          => 'YOUR_OUID', // Replace with your OUID
            'MLS-Aligned-User-Agent' => 'YOUR_USER_AGENT', // Replace with your user agent
            'Accept'        => 'application/json',
        ),
    ));

    if (is_wp_error($response)) {
        error_log('Error fetching media: ' . $response->get_error_message());
        return null;
    }

    $response_body = wp_remote_retrieve_body( $response );
    $media_data = json_decode( $response_body, true );
    $media_url = $media_data['value'][0]['MediaURL'] ?? null;

    if ( $media_url ) {
        set_transient( 'jsmls_media_' . $listing_key, $media_url, 300 ); // Cache for 5 minutes
    }

    return $media_url;
}

// Helper function to fetch MLS data for a specific location
function jsmls_fetch_location_mls_data($location, $page = 1, $per_page = 12, $min_price = 0) {
    $skip = ($page - 1) * $per_page;
    $location_name = str_replace('_', ' ', $location);

    $url = 'https://api.example.com/reso/odata/Property?$filter=City%20eq%20%27' . urlencode($location_name) . '%27%20and%20MlsStatus%20eq%20%27Active%27%20and%20ListPrice%20ge%20' . $min_price . '&$orderby=ListPrice%20desc&$top=' . $per_page . '&$skip=' . $skip . '&$count=true'; // Replace with your API endpoint

    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Bearer YOUR_API_TOKEN', // Replace with your API token
            'OUID'          => 'YOUR_OUID', // Replace with your OUID
            'MLS-Aligned-User-Agent' => 'YOUR_USER_AGENT', // Replace with your user agent
            'Accept'        => 'application/json',
        ),
    ));

    if (is_wp_error($response)) {
        error_log('Error fetching MLS data for ' . $location_name . ': ' . $response->get_error_message());
        return false;
    }

    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body, true);

    if (!isset($data['value']) || empty($data['value'])) {
        error_log('No data found in MLS API response for ' . $location_name);
        return false;
    }

    return array(
        'properties' => $data['value'],
        'total_count' => $data['@odata.count'] ?? 0
    );
}

// Helper functions to generate HTML for different display types
function jsmls_generate_down_grid($properties) {
    $output = '<div class="jsmls-down-grid">';
    foreach ($properties as $property) {
        $output .= '<div class="jsmls-property-card">';
        $output .= '<h3>' . esc_html($property['StreetNumber'] . ' ' . $property['StreetName'] ?? 'Address Not Available') . '</h3>';
        $output .= '<p>Price: $' . number_format($property['ListPrice'] ?? 0) . '</p>';
        $output .= '</div>';
    }
    $output .= '</div>';
    return $output;
}

function jsmls_generate_four_row($properties) {
    $output = '<div class="jsmls-four-row">';
    $count = 0;
    foreach ($properties as $property) {
        if ($count >= 4) break;
        $output .= '<div class="jsmls-property-card">';
        $output .= '<h3>' . esc_html($property['StreetNumber'] . ' ' . $property['StreetName'] ?? 'Address Not Available') . '</h3>';
        $output .= '<p>Price: $' . number_format($property['ListPrice'] ?? 0) . '</p>';
        $output .= '</div>';
        $count++;
    }
    $output .= '</div>';
    return $output;
}

function jsmls_generate_featured_listing($property) {
    $output = '<div class="jsmls-featured-listing">';
    $output .= '<h2>Featured Listing</h2>';
    $output .= '<h3>' . esc_html($property['StreetNumber'] . ' ' . $property['StreetName'] ?? 'Address Not Available') . '</h3>';
    $output .= '<p>Price: $' . number_format($property['ListPrice'] ?? 0) . '</p>';
    $output .= '<p>Bedrooms: ' . esc_html($property['BedroomsTotal'] ?? 'N/A') . '</p>';
    $output .= '<p>Bathrooms: ' . esc_html($property['BathroomsTotalInteger'] ?? 'N/A') . '</p>';
    $output .= '</div>';
    return $output;
}

function jsmls_generate_four_grid($properties) {
    $output = '<div class="jsmls-four-grid">';
    $count = 0;
    foreach ($properties as $property) {
        if ($count >= 4) break;
        $output .= '<div class="jsmls-property-card">';
        $output .= '<h3>' . esc_html($property['StreetNumber'] . ' ' . $property['StreetName'] ?? 'Address Not Available') . '</h3>';
        $output .= '<p>Price: $' . number_format($property['ListPrice'] ?? 0) . '</p>';
        $output .= '</div>';
        $count++;
    }
    $output .= '</div>';
    return $output;
}

// New function to generate grid HTML for any location
function jsmls_generate_location_grid_html($properties, $location_name) {
    $output = "
    <style>
        /* Your CSS styles here */
    </style>
    <div class='location-grid'>";

    foreach ($properties as $property) {
        $listing_url = get_site_url() . '/property-detail?listing_key=' . urlencode($property['ListingKey']);
        $media_url = jsmls_fetch_media($property['ListingKey']);
        $image = $media_url ? esc_url($media_url) : 'https://via.placeholder.com/400x300';

        $output .= "
        <div class='location-card'>
            <a href='" . esc_url($listing_url) . "' style='text-decoration: none; color: inherit;'>
                <img src='" . $image . "' alt='Property Image' loading='lazy'>
                <div class='location-details'>
                    <h3>" . esc_html($property['StreetNumber'] . ' ' . $property['StreetName']) . "</h3>
                    <p><i class='fas fa-map-marker-alt'></i> " . esc_html($property['City']) . "</p>
                    <p><i class='fas fa-bed'></i> " . esc_html($property['BedroomsTotal']) . " Beds | 
                       <i class='fas fa-bath'></i> " . esc_html($property['BathroomsTotalInteger']) . " Baths | 
                       <i class='fas fa-ruler-combined'></i> " . esc_html($property['BuildingAreaTotal']) . " sqft</p>
                    <p class='location-price'>$" . number_format($property['ListPrice']) . "</p>
                </div>
            </a>
        </div>";
    }

    $output .= "</div>";
    return $output;
}
