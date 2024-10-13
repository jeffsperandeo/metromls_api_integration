<?php
// Display Lake Geneva MLS listings without pagination, 6 per grid
function jsmls_display_lake_geneva_mls_grid_shortcode() {
    $page = 1;  // Always display the first page
    $per_page = 6;  // Limit to 6 listings per grid

    // Fetch MLS data
    $properties = jsmls_fetch_lake_geneva_mls_data( $page, $per_page );

    // Check if there are properties to display
    if ( empty($properties) ) {
        return '<p>No listings found or failed to fetch data.</p>';
    }

    // Start building the output with improved CSS styling
    $output = '
    <style>
        .mls-listings-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .mls-listing-card {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 10px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .mls-listing-card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .mls-listing-card img {
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .mls-listing-card h3 {
            color: #2a2a2a;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        .mls-listing-card p {
            font-size: 0.9rem;
            color: #555;
            margin: 5px 0;
            text-align: left;
        }
        .mls-listing-card p span {
            display: inline-block;
            margin-right: 5px;
        }
        .view-details {
            display: inline-block;
            margin-top: 10px;
            color: #bf974f;
            font-weight: bold;
            text-decoration: none;
        }
        .view-details:hover {
            text-decoration: underline;
        }
    </style>';

    $output .= '<div class="mls-listings-container">';
    
    // Loop through the properties and generate the listing cards
    foreach ( $properties as $property ) {
        // Generate the listing detail page URL with ListingKey as a query parameter
        $listing_url = get_site_url() . '/property-detail?listing_key=' . urlencode( $property['ListingKey'] );
        
        $output .= '<div class="mls-listing-card">';
        $output .= '<a href="' . esc_url( $listing_url ) . '" style="text-decoration: none; color: inherit;">';

        // Media (property image)
        $media_url = jsmls_fetch_media( $property['ListingKey'] );
        $image = $media_url ? esc_url( $media_url ) : 'https://via.placeholder.com/400x300';
        $output .= '<img src="' . $image . '" alt="Property Image" style="max-width: 100%;">';

        // Price
        $output .= '<h3>Price: $' . number_format( $property['ListPrice'] ) . '</h3>';

        // Full Address (no label)
        $output .= '<p>' . esc_html( $property['StreetNumber'] . ' ' . $property['StreetName'] ) . ', ' . esc_html( $property['City'] ) . '</p>';
        
        // Bedrooms, Bathrooms, and Square Feet
        $output .= '<p><span class="bedrooms"><i class="fas fa-bed"></i></span>' . esc_html( $property['BedroomsTotal'] ) . ' Beds';
        $output .= ' | <span class="bathrooms"><i class="fas fa-bath"></i></span>' . esc_html( $property['BathroomsTotalInteger'] ) . ' Baths';
        $output .= ' | <span class="sqft"><i class="fas fa-ruler-combined"></i></span>' . esc_html( $property['BuildingAreaTotal'] ) . ' sqft</p>';

        $output .= '<a class="view-details" href="' . esc_url( $listing_url ) . '">View Details</a>';

        $output .= '</a>'; // End of the link
        $output .= '</div>'; // End of listing card
    }

    $output .= '</div>'; // End of listings container

    // Return the final HTML output
    return $output;
}
