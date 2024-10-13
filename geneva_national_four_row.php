<?php
// Display Geneva National listings in a four-column grid
function jsmls_display_geneva_national_four_row_shortcode() {
    $page = 1;
    $per_page = 50;

    // Fetch MLS data from Lake Geneva
    $properties = jsmls_fetch_lake_geneva_mls_data( $page, $per_page );

    // Check if properties were fetched successfully
    if ( empty( $properties ) ) {
        return '<p>No listings found or failed to fetch data.</p>';
    }

    // Filter properties to include only those in Geneva National subdivision
    $properties = array_filter( $properties, function( $property ) {
        return isset( $property['SubdivisionName'] ) && $property['SubdivisionName'] === 'Geneva National';
    });

    // Slice to get only the first 4 properties
    $properties = array_slice( $properties, 0, 4 );

    if ( empty( $properties ) ) {
        return '<p>No listings found in Geneva National.</p>';
    }

    // Start building the output with CSS styling
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

    foreach ( $properties as $property ) {
        $listing_url = get_site_url() . '/property-detail?listing_key=' . urlencode( $property['ListingKey'] );

        $output .= '<div class="listing-card">';
        $output .= '<a href="' . esc_url( $listing_url ) . '" style="text-decoration: none; color: inherit;">';

        $media_url = jsmls_fetch_media( $property['ListingKey'] );
        $image = $media_url ? esc_url( $media_url ) : 'https://via.placeholder.com/400x300';
        $output .= '<img src="' . $image . '" alt="Property Image" loading="lazy">';

        $output .= '<div class="listing-details">';
        $output .= '<div class="listing-title">' . esc_html( $property['StreetNumber'] . ' ' . $property['StreetName'] ) . '</div>';
        $output .= '<div class="listing-info">';
        $output .= '<i class="fas fa-bed"></i> ' . esc_html( $property['BedroomsTotal'] ) . ' Beds | ';
        $output .= '<i class="fas fa-bath"></i> ' . esc_html( $property['BathroomsTotalInteger'] ) . ' Baths | ';
        $output .= '<i class="fas fa-ruler-combined"></i> ' . esc_html( $property['BuildingAreaTotal'] ) . ' sqft';
        $output .= '</div>';
        $output .= '<div class="listing-price">$' . number_format( $property['ListPrice'] ) . '</div>';
        $output .= '<a class="view-details" href="' . esc_url( $listing_url ) . '">View Details</a>';
        $output .= '</div>'; // End of listing-details

        $output .= '</a>';
        $output .= '</div>'; // End of listing-card
    }

    $output .= '</div>'; // End of four-listings-container

    return $output;
}

add_shortcode( 'geneva_national_four_row', 'jsmls_display_geneva_national_four_row_shortcode' );
