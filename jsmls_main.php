<?php
/**
 * Plugin Name: JSMLS
 * Description: A plugin to display MLS listings dynamically using data from the MLS Aligned API for real estate agents.
 * Version: 3.6
 * Author: Jeff Sperandeo
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Define API credentials (replace with your actual credentials)
define('JSMLS_API_URL', 'https://api.example.com'); // Replace with your API base URL
define('JSMLS_API_TOKEN', 'YOUR_API_TOKEN'); // Replace with your API token

// Include shared functions
include_once plugin_dir_path(__FILE__) . 'jsmls_shared_functions.php';

// Include location information functionality
include_once plugin_dir_path(__FILE__) . 'location_information.php';

// Function to dynamically include shortcode files
function jsmls_include_shortcode($shortcode_name) {
    $file_path = plugin_dir_path(__FILE__) . $shortcode_name . '.php';
    if (file_exists($file_path)) {
        include_once $file_path;
        return true;
    } else {
        error_log("Shortcode file for {$shortcode_name} not found at {$file_path}");
        return false;
    }
}

// List of locations in the Lake Geneva Area
function jsmls_get_locations() {
    return array(
        'lake_geneva'    => 'Lake Geneva',
        'genoa_city'     => 'Genoa City',
        'fontana'        => 'Fontana',
        'williams_bay'   => 'Williams Bay',
        'delavan'        => 'Delavan',
        'elkhorn'        => 'Elkhorn',
        'burlington'     => 'Burlington',
        'east_troy'      => 'East Troy',
        'twin_lakes'     => 'Twin Lakes',
        'walworth'       => 'Walworth',
        'salem'          => 'Salem',
        'silver_lake'    => 'Silver Lake',
        'lyons'          => 'Lyons',
        'powers_lake'    => 'Powers Lake',
        'whitewater'     => 'Whitewater'
    );
}

// Register all shortcodes
function jsmls_register_shortcodes() {
    $shortcodes = array(
        'lake_geneva_mls_listings_pagination'         => 'jsmls_display_lake_geneva_mls_listings_shortcode',
        'lake_geneva_grid'                            => 'jsmls_display_lake_geneva_mls_grid_shortcode',
        'lake_geneva_down_grid'                       => 'jsmls_display_lake_geneva_down_grid_shortcode',
        'mls_property_detail'                         => 'jsmls_fetch_single_property',
        'lake_geneva_featured_listing'                => 'jsmls_display_lake_geneva_featured_listing_shortcode',
        'lake_geneva_four_row'                        => 'jsmls_display_lake_geneva_four_row_shortcode',
        'walworth_location_grid'                      => 'jsmls_display_walworth_location_grid',
        'geneva_national_four_row'                    => 'jsmls_display_geneva_national_four_row_shortcode',
        'lake_geneva_neighborhood_four_grid_horizontal'=> 'jsmls_display_lake_geneva_neighborhood_four_grid_horizontal_shortcode',
        'location_information'                        => 'jsmls_location_information_shortcode'
    );

    // Add location-specific shortcodes
    $locations = jsmls_get_locations();
    foreach ($locations as $location_slug => $location_name) {
        $shortcodes["{$location_slug}_four_grid"] = 'jsmls_display_location_four_grid_shortcode';
    }

    foreach ($shortcodes as $shortcode => $function) {
        if (jsmls_include_shortcode($shortcode) || function_exists($function)) {
            add_shortcode($shortcode, $function);
        }
    }
}
add_action('init', 'jsmls_register_shortcodes');

// Generic function to display location-specific four grid
function jsmls_display_location_four_grid_shortcode($atts, $content = null, $shortcode = '') {
    $location = str_replace('_four_grid', '', $shortcode);
    $location_name = jsmls_get_locations()[$location] ?? ucwords(str_replace('_', ' ', $location));

    $atts = shortcode_atts(array(
        'page'      => 1,
        'per_page'  => 12,
        'min_price' => 0,
    ), $atts);

    $page       = max(1, intval(get_query_var('paged') ? get_query_var('paged') : $atts['page']));
    $per_page   = intval($atts['per_page']);
    $min_price  = intval($atts['min_price']);

    $result = jsmls_fetch_location_mls_data($location_name, $page, $per_page, $min_price);

    if ($result === false || empty($result['properties'])) {
        return "<p>No listings found or failed to fetch data for {$location_name}. Please try again later.</p>";
    }

    $properties   = $result['properties'];
    $total_count  = $result['total_count'];
    $total_pages  = ceil($total_count / $per_page);

    $output = jsmls_generate_grid_html($properties, $location_name);

    if ($total_pages > 1) {
        $output .= jsmls_generate_pagination_html($page, $total_pages);
    }

    return $output;
}

// Function to fetch MLS data for a specific location
function jsmls_fetch_location_mls_data($location_name, $page, $per_page, $min_price) {
    // Build the API request URL
    $api_endpoint = JSMLS_API_URL . '/listings';

    // Build the query parameters
    $params = array(
        'city'      => $location_name,
        'page'      => $page,
        'per_page'  => $per_page,
        'min_price' => $min_price,
    );

    // Build the full URL with query parameters
    $request_url = $api_endpoint . '?' . http_build_query($params);

    // Set up the API request headers
    $headers = array(
        'Authorization: Bearer ' . JSMLS_API_TOKEN,
        'Accept: application/json',
    );

    // Initialize cURL
    $ch = curl_init($request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute the request
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        error_log('cURL error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    // Close cURL
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);

    // Check for decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON decode error: ' . json_last_error_msg());
        return false;
    }

    return $data;
}

// Function to fetch media (image URL) for a specific property
function jsmls_fetch_media($listing_key) {
    // Build the API request URL
    $api_endpoint = JSMLS_API_URL . '/listings/' . urlencode($listing_key) . '/media';

    // Set up the API request headers
    $headers = array(
        'Authorization: Bearer ' . JSMLS_API_TOKEN,
        'Accept: application/json',
    );

    // Initialize cURL
    $ch = curl_init($api_endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute the request
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        error_log('cURL error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    // Close cURL
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);

    // Check for decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON decode error: ' . json_last_error_msg());
        return false;
    }

    // Assuming the API returns an array of media items, and we take the first image
    if (!empty($data) && isset($data[0]['MediaURL'])) {
        return $data[0]['MediaURL'];
    }

    return false;
}

// Function to generate grid HTML
function jsmls_generate_grid_html($properties, $location_name) {
    $output = "
    <style>
        .location-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 10px;
        }
        .location-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .location-card:hover {
            transform: scale(1.03);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .location-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .location-details {
            padding: 15px;
        }
        .location-details h3 {
            margin: 0 0 10px;
            font-size: 1.2rem;
        }
        .location-details p {
            margin: 5px 0;
            font-size: 0.9rem;
        }
        .location-price {
            font-weight: bold;
            color: #bf974f;
        }
    </style>
    <div class='location-grid'>";

    foreach ($properties as $property) {
        $listing_url = get_site_url() . '/property-detail?listing_key=' . urlencode($property['ListingKey']);
        $media_url   = jsmls_fetch_media($property['ListingKey']);
        $image       = $media_url ? esc_url($media_url) : 'https://via.placeholder.com/400x300';

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

// Function to generate pagination HTML
function jsmls_generate_pagination_html($current_page, $total_pages) {
    $output = "<div class='location-pagination' style='display: flex; justify-content: center; margin-top: 20px;'>";
    if ($current_page > 1) {
        $output .= "<a href='" . esc_url(add_query_arg('paged', $current_page - 1)) . "' style='padding: 5px 10px; margin: 0 5px; background-color: #bf974f; color: white; text-decoration: none; border-radius: 5px;'>Previous</a>";
    }
    $output .= "<span style='padding: 5px 10px;'>Page " . $current_page . " of " . $total_pages . "</span>";
    if ($current_page < $total_pages) {
        $output .= "<a href='" . esc_url(add_query_arg('paged', $current_page + 1)) . "' style='padding: 5px 10px; margin: 0 5px; background-color: #bf974f; color: white; text-decoration: none; border-radius: 5px;'>Next</a>";
    }
    $output .= "</div>";
    return $output;
}
