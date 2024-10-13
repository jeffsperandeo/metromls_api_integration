<?php
// Display four Lake Geneva listings from specified neighborhoods in a horizontal grid
function jsmls_display_lake_geneva_neighborhood_four_grid_horizontal_shortcode() {
    // Define the list of neighborhoods
    $neighborhoods = array(
        'BAKER & BROWNS',
        'BOULEVARD',
        'CASA DEL SUENO',
        'CEYLON COURT EST',
        'COLUMBIAN SUB',
        'EAST SHORE ESTATE',
        'EDGEWOOD HILLS',
        'GENEVA MANOR',
        'GENEVA WOODS',
        'GLEN OAKS',
        'HUNT RIDGE',
        'LAKE GENEVA MANOR',
        'LGN EAST SHORE ES',
        'MERRILY-ON',
        'STURWOOD',
        'TWINS ON CURTIS',
        'WILDWOOD'
    );

    // Build the filter string
    $filter_parts = array();
    foreach ($neighborhoods as $neighborhood) {
        // Escape single quotes by doubling them
        $escaped_neighborhood = str_replace("'", "''", $neighborhood);
        $filter_parts[] = "SubdivisionName eq '$escaped_neighborhood'";
    }
    $filter_string = '(' . implode(' or ', $filter_parts) . ') and MlsStatus eq 'Active'';

    // URL-encode the filter string
    $filter_encoded = urlencode($filter_string);

    // Construct the URL
    $select_fields = 'ListingKey,StreetNumber,StreetName,ListPrice,City,BedroomsTotal,BathroomsTotalInteger,BuildingAreaTotal,PostalCode';
    $top = 50; // Fetch up to 50 listings
    $url = "https://api.mlsaligned.com/reso/odata/Property?\$filter=$filter_encoded&\$select=$select_fields&\$top=$top";

    // Set headers (using the same headers as your shared functions file)
    $headers = array(
        'Authorization' => 'Bearer {YOUR_ACCESS_TOKEN}',
        'OUID' => '{YOUR_OUID}',
        'MLS-Aligned-User-Agent' => '{YOUR_USER_AGENT}',
        'Accept' => 'application/json'
    );

    // Make the API request using wp_remote_get
    $response = wp_remote_get($url, array(
        'headers' => $headers
    ));

    if (is_wp_error($response)) {
        return '<p>There was an error fetching the property data.</p>';
    }

    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body, true);

    if (!isset($data['value']) || empty($data['value'])) {
        return '<p>No listings found in the specified neighborhoods.</p>';
    }

    $properties = $data['value'];

    // Slice to get only the first 4 properties
    $properties = array_slice($properties, 0, 4);

    $output = '
    <style>
        .four-listings-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 20px;
        }
        .listing-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .listing-card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .listing-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .listing-details {
            padding: 15px;
        }
        .listing-title {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .listing-info {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }
        .listing-price {
            color: #bf974f;
            font-weight: bold;
            font-size: 1.4rem;
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

        @media (max-width: 1024px) {
            .four-listings-container {
                grid-template-columns: 1fr;
            }
        }
    </style>';

    $output .= '<div class="four-listings-container">';

    foreach ($properties as $property) {
        $listing_url = get_site_url() . '/property-detail?listing_key=' . urlencode($property['ListingKey']);

        $output .= '<div class="listing-card">';
        $output .= '<a href="' . esc_url($listing_url) . '" style="text-decoration: none; color: inherit;">';

        // Fetch media URL using your shared function
        $media_url = jsmls_fetch_media($property['ListingKey']);
        $image = $media_url ? esc_url($media_url) : ''; // No placeholder image

        // Only display the image if it exists
        if ($image) {
            $output .= '<img src="' . $image . '" alt="Property Image">';
        }

        $output .= '<div class="listing-details">';
        $output .= '<div class="listing-title">' . esc_html($property['StreetNumber'] . ' ' . $property['StreetName']) . '</div>';
        $output .= '<div class="listing-info">' . esc_html($property['BedroomsTotal']) . ' Beds | ';
        $output .= esc_html($property['BathroomsTotalInteger']) . ' Baths | ';
        $output .= esc_html(number_format($property['BuildingAreaTotal'])) . ' sqft</div>';
        $output .= '<div class="listing-price">$' . number_format($property['ListPrice']) . '</div>';
        $output .= '<a class="view-details" href="' . esc_url($listing_url) . '">View Details</a>';
        $output .= '</div>';

        $output .= '</a>';
        $output .= '</div>';
    }

    $output .= '</div>';

    return $output;
}
?>
