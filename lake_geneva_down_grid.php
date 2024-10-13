<?php
// Display Lake Geneva MLS listings without pagination, 3 per grid
function jsmls_display_lake_geneva_down_grid_shortcode() {
    $page = 1;  // Always display the first page
    $per_page = 3;  // Limit to 3 listings per grid

    // Fetch MLS data
    $properties = jsmls_fetch_lake_geneva_mls_data( $page, $per_page );

    // Check if there are properties to display
    if ( empty($properties) ) {
        return '<p>No listings found or failed to fetch data.</p>';
    }

    // Start building the output with improved CSS styling based on your requirements
    $output = '
    <style>
        .mls-listings-container {
            display: grid;
            grid-template-columns: 1fr; /* Single column grid */
            gap: 20px;
            padding: 10px;
        }
        .mls-listing-card {
            display: flex;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e1e1e1;
            height: 350px; /* Uniform height for all cards */
        }
        .mls-listing-card:hover {
            transform: scale(1.03);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .mls-listing-card img {
            width: 40%;  /* Set a fixed portion for the image */
            height: 100%;  /* Ensure image fills the height of the card */
            object-fit: cover; /* Crop the image to fit within the container */
            loading: lazy;  /* Lazy loading added */
        }
        .mls-listing-details {
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 60%;  /* Text container takes remaining space */
        }
        .mls-listing-details p {
            margin: 0;
            font-size: 1rem;
            color: #666;
        }
        .mls-listing-details h3 {
            font-size: 1.2rem;
            color: #2c3e50;
            margin: 10px 0;
            font-weight: 600;
        }
        .property-info {
            font-size: 0.9rem;
            color: #888;
            margin: 5px 0;
        }
        .price {
            color: #bf974f;
            font-weight: bold;
            font-size: 1.2rem;
            margin-top: 15px;
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

        /* Ensure that all images and cards have the same height */
        .mls-listing-card img {
            object-fit: cover;
            width: 100%;
            height: 100%;
            border-radius: 10px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .mls-listing-card {
                flex-direction: column;
                height: auto; /* Auto height for mobile */
            }
            .mls-listing-card img {
                width: 100%; /* Full width for mobile */
                height: 200px; /* Fixed height for the image on mobile */
            }
            .mls-listing-details {
                width: 100%; /* Full width for mobile */
            }
        }
    </style>';

    $output .= '<div class="mls-listings-container">';

    // Loop through the properties and generate the listing cards
    foreach ( $properties as $property ) {
        // Generate the listing detail page URL with ListingKey as a query parameter
        $listing_url = get_site_url() . '/property-detail?listing_key=' . urlencode( $property['ListingKey'] );
        
        $output .= '<div class="mls-listing-card">';
        $output .= '<a href="' . esc_url( $listing_url ) . '" style="text-decoration: none; color: inherit;">';

        // Media (property image) with caching and lazy loading
        $media_url = jsmls_fetch_media( $property['ListingKey'] );
        $image = $media_url ? esc_url( $media_url ) : 'https://via.placeholder.com/400x300';
        $output .= '<img src="' . $image . '" alt="Property Image" loading="lazy">';  // Lazy loading added here

        // Details
        $output .= '<div class="mls-listing-details">';

        // Location
        $output .= '<p class="location"><i class="fas fa-map-marker-alt"></i> ' . esc_html( $property['City'] ) . '</p>';

        // Property Title (Street Number and Name)
        $output .= '<h3>' . esc_html( $property['StreetNumber'] . ' ' . $property['StreetName'] ) . '</h3>';

        // Property Information: Beds, Baths, and Sqft
        $output .= '<p class="property-info"><i class="fas fa-bed"></i> ' . esc_html( $property['BedroomsTotal'] ) . ' Beds | ';
        $output .= '<i class="fas fa-bath"></i> ' . esc_html( $property['BathroomsTotalInteger'] ) . ' Baths | ';
        $output .= '<i class="fas fa-ruler-combined"></i> ' . esc_html( $property['BuildingAreaTotal'] ) . ' sqft</p>';

        // Price and View Details
        $output .= '<span class="price">$' . number_format( $property['ListPrice'] ) . '</span>';
        $output .= '<a class="view-details" href="' . esc_url( $listing_url ) . '">View Details</a>';

        $output .= '</div>'; // End of .mls-listing-details

        $output .= '</a>'; // End of the link
        $output .= '</div>'; // End of listing card
    }

    $output .= '</div>'; // End of listings container

    // Return the final HTML output
    return $output;
}
