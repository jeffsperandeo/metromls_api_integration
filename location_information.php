<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

function jsmls_location_information_shortcode($atts) {
    $atts = shortcode_atts(array(
        'location' => '',
    ), $atts);

    $location = strtolower(str_replace(' ', '_', $atts['location']));
    $locations = jsmls_get_locations();

    if (!array_key_exists($location, $locations)) {
        return "<p>Invalid location specified.</p>";
    }

    $location_name = $locations[$location];

    // Fetch Census data
    $census_data = jsmls_fetch_census_data($location);

    // Fetch real estate market data
    $market_data = jsmls_fetch_market_data($location);

    // Compile the information
    $output = "
    <style>
        /* Your CSS styles here */
    </style>
    <div class='location-info'>
        <h2>{$location_name} Real Estate Information</h2>
        <ul>
            <li><strong>Population:</strong> " . number_format($census_data['population']) . "</li>
            <li><strong>Median Home Value:</strong> $" . number_format($census_data['median_home_value']) . "</li>
            <li><strong>Median Household Income:</strong> $" . number_format($census_data['median_household_income']) . "</li>
            <li><strong>Active Listings:</strong> " . $market_data['active_listings'] . "</li>
            <li><strong>Average Days on Market:</strong> " . $market_data['avg_days_on_market'] . "</li>
            <li><strong>Price per Square Foot:</strong> $" . number_format($market_data['price_per_sqft'], 2) . "</li>
            <li><strong>Year-over-Year Price Change:</strong> " . $market_data['yoy_price_change'] . "%</li>
        </ul>
        <p><small>Data sources: U.S. Census Bureau and local MLS data (placeholder data)</small></p>
    </div>
    ";

    return $output;
}

function jsmls_fetch_census_data($location) {
    // Placeholder data for all locations
    $data = array(
        'lake_geneva' => array('population' => 7894, 'median_home_value' => 276800, 'median_household_income' => 54956),
        // Add other locations as needed
    );

    return $data[$location] ?? array('population' => 'N/A', 'median_home_value' => 'N/A', 'median_household_income' => 'N/A');
}

function jsmls_fetch_market_data($location) {
    // Placeholder data for all locations
    $data = array(
        'lake_geneva' => array('active_listings' => 120, 'avg_days_on_market' => 45, 'price_per_sqft' => 225.50, 'yoy_price_change' => 5.2),
        // Add other locations as needed
    );

    return $data[$location] ?? array('active_listings' => 'N/A', 'avg_days_on_market' => 'N/A', 'price_per_sqft' => 'N/A', 'yoy_price_change' => 'N/A');
}

add_shortcode('location_information', 'jsmls_location_information_shortcode');
