<?php
// Display a random featured listing for Lake Geneva
function jsmls_display_lake_geneva_featured_listing_shortcode() {
    $page = 1;
    $per_page = 50; // Fetch more listings to choose a random one

    // Fetch MLS data
    $properties = jsmls_fetch_lake_geneva_mls_data($page, $per_page);

    // Check if there are properties to display
    if (empty($properties)) {
        return '<p>No listings found or failed to fetch data.</p>';
    }

    // Select a random property from the list
    $random_property = $properties[array_rand($properties)];

    // Start building the output with improved CSS styling
    $output = '
    <style>
        .featured-listing-container {
            display: flex;
            flex-direction: column;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            background-color: #fff;
            border: 1px solid #ddd;
            margin: 20px 0;
        }
        .featured-listing-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        .featured-listing-details {
            padding: 20px;
        }
        .featured-listing-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .featured-listing-location {
            font-size: 1rem;
            color: #888;
            margin-bottom: 10px;
        }
        .featured-listing-description {
            font-size: 1rem;
            color: #555;
            margin-bottom: 15px;
        }
        .featured-listing-price {
            font-size: 1.8rem;
            color: #bf974f;
            font-weight: bold;
        }
        .featured-listing-info-bar {
            background-color: #2c3e50;
            padding: 15px;
            color: #fff;
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .featured-listing-info-item {
            display: flex;
            align-items: center;
            font-size: 1.2rem;
        }
        .featured-listing-info-item i {
            margin-right: 8px;
        }
        .view-details {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            font-weight: bold;
            text-decoration: none;
        }
        .view-details:hover {
            text-decoration: underline;
        }
    </style>';

    $output .= '<div class="featured-listing-container">';

    // Media (property image)
    $media_url = jsmls_fetch_media($random_property['ListingKey']);
    $image = $media_url ? esc_url($media_url) : 'https://via.placeholder.com/800x400';
    $output .= '<img class="featured-listing-image" src="' . $image . '" alt="Property Image">';

    // Info Bar (Beds, Baths, Garages, SqFt)
    $output .= '<div class="featured-listing-info-bar">';
    $output .= '<div class="featured-listing-info-item"><i class="fas fa-bed"></i> ' . esc_html($random_property['BedroomsTotal']) . ' Beds</div>';
    $output .= '<div class="featured-listing-info-item"><i class="fas fa-bath"></i> ' . esc_html($random_property['BathroomsTotalInteger']) . ' Baths</div>';
    $output .= '<div class="featured-listing-info-item"><i class="fas fa-car"></i> ' . esc_html($random_property['GarageSpaces']) . ' Garages</div>';
    $output .= '<div class="featured-listing-info-item"><i class="fas fa-ruler-combined"></i> ' . esc_html($random_property['BuildingAreaTotal']) . ' sqft</div>';
    $output .= '</div>'; // End of .featured-listing-info-bar

    // Property details
    $output .= '<div class="featured-listing-details">';
    $output .= '<div class="featured-listing-location"><i class="fas fa-map-marker-alt"></i> ' . esc_html($random_property['City']) . ', ' . esc_html($random_property['StateOrProvince']) . '</div>';
    $output .= '<div class="featured-listing-title">' . esc_html($random_property['StreetNumber']) . ' ' . esc_html($random_property['StreetName']) . '</div>';
    $output .= '<div class="featured-listing-description">Integer posuere erat a ante venenatis dapibus posuere velit aliquet dapibus ac facilisis in egestas eget quam.</div>';

    // Price and View Details
    $output .= '<div class="featured-listing-price">$' . number_format($random_property['ListPrice']) . '</div>';
    $output .= '<a class="view-details" href="' . esc_url(get_site_url() . '/property-detail?listing_key=' . urlencode($random_property['ListingKey'])) . '">View Details</a>';

    $output .= '</div>'; // End of .featured-listing-details
    $output .= '</div>'; // End of .featured-listing-container

    // Return the final HTML output
    return $output;
}

// Register the shortcode
add_shortcode('lake_geneva_featured_listing', 'jsmls_display_lake_geneva_featured_listing_shortcode');
