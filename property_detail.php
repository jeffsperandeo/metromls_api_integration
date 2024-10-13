<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

function jsmls_fetch_single_property() {
    if (isset($_GET['listing_key'])) {
        $listing_key = sanitize_text_field($_GET['listing_key']);

        $url = 'https://api.example.com/reso/odata/Property(' . $listing_key . ')?$expand=Media'; // Replace with your API endpoint

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization'             => 'Bearer YOUR_API_TOKEN', // Replace with your API token
                'OUID'                      => 'YOUR_OUID',             // Replace with your OUID
                'MLS-Aligned-User-Agent'    => 'YOUR_USER_AGENT',      // Replace with your user agent
                'Accept'                    => 'application/json',
            ),
        ));

        if (is_wp_error($response)) {
            return '<p>Error fetching property details.</p>';
        }

        $response_body = wp_remote_retrieve_body($response);
        $property = json_decode($response_body, true);

        if (!isset($property) || empty($property)) {
            return '<p>No property details found.</p>';
        }

        // Start building the output with enhanced styling and spacing
        $output = '
        <style>
            /* Your CSS styles here */
        </style>';

        // Add hero image if available
        if (!empty($property['Media'])) {
            $hero_image = esc_url($property['Media'][0]['MediaURL']);
            $output .= '<div class="property-image-container">';
            $output .= '<img id="hero-image" class="hero-image" src="' . $hero_image . '" alt="Property Image">';
            $output .= '</div>';

            // Carousel for additional images
            if (count($property['Media']) > 1) {
                $output .= '<div class="carousel-container">';
                foreach ($property['Media'] as $media) {
                    $output .= '<img class="carousel-image" src="' . esc_url($media['MediaURL']) . '" alt="Property Image" onclick="changeHeroImage(this.src)">';
                }
                $output .= '</div>';
            }
        } else {
            $output .= '<img src="https://via.placeholder.com/800x400" alt="No Image Available" class="hero-image">';
        }

        // Add property info bar (conditionally display only if data exists)
        $output .= '<div class="info-bar">';
        if (!empty($property['BedroomsTotal'])) {
            $output .= '<div class="info-item"><i class="fas fa-bed"></i> ' . esc_html($property['BedroomsTotal']) . ' Beds</div>';
        }
        if (!empty($property['BathroomsTotalInteger'])) {
            $output .= '<div class="info-item"><i class="fas fa-bath"></i> ' . esc_html($property['BathroomsTotalInteger']) . ' Baths</div>';
        }
        if (!empty($property['GarageSpaces'])) {
            $output .= '<div class="info-item"><i class="fas fa-car"></i> ' . esc_html($property['GarageSpaces']) . ' Garages</div>';
        }
        if (!empty($property['BuildingAreaTotal'])) {
            $output .= '<div class="info-item"><i class="fas fa-ruler-combined"></i> ' . esc_html($property['BuildingAreaTotal']) . ' sqft</div>';
        }
        if (!empty($property['PropertySubType'])) {
            $output .= '<div class="info-item"><i class="fas fa-home"></i> ' . esc_html($property['PropertySubType']) . '</div>';
        }
        if (!empty($property['LotSizeAcres'])) {
            $output .= '<div class="info-item"><i class="fas fa-tree"></i> Lot Size: ' . esc_html($property['LotSizeAcres']) . ' acres</div>';
        }
        if (!empty($property['YearBuilt'])) {
            $output .= '<div class="info-item"><i class="fas fa-calendar-alt"></i> Year Built: ' . esc_html($property['YearBuilt']) . '</div>';
        }
        $output .= '</div>'; // End of info-bar

        // Property details
        $output .= '<div class="property-details">';
        $output .= '<div class="property-location"><i class="fas fa-map-marker-alt"></i> ' . esc_html($property['City']) . ', ' . esc_html($property['StateOrProvince']) . '</div>';
        $output .= '<div class="property-title">' . esc_html($property['StreetNumber'] . ' ' . $property['StreetName']) . '</div>';
        $output .= '<div class="property-description">' . esc_html($property['PublicRemarks']) . '</div>';
        $output .= '<div class="property-price">$' . number_format($property['ListPrice']) . '</div>';
        $output .= '<a class="chat-link" href="https://yourwebsite.com/contact/">Chat with us about this listing</a>';
        $output .= '</div>'; // End of property-details

        // Add Back Button
        $output .= '<div style="text-align:center; margin-top: 30px;">';
        $output .= '<a href="javascript:history.back()" class="back-button">&larr; Back to Listings</a>';
        $output .= '</div>';

        // Add JavaScript to handle image swapping
        $output .= '<script>
            function changeHeroImage(src) {
                document.getElementById("hero-image").src = src;
            }
        </script>';

        return $output;
    }

    return '<p>No listing key provided.</p>';
}
?>
