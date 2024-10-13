<?php
// Fetch Lake Geneva MLS data with pagination
function jsmls_fetch_lake_geneva_mls_data( $page = 1, $per_page = 10 ) {
    $skip = ( $page - 1 ) * $per_page;
    $url = 'https://api.mlsaligned.com/reso/odata/Property?$filter=City%20eq%20%27Lake%20Geneva%27%20and%20MlsStatus%20eq%20%27Active%27%20and%20PropertySubType%20ne%20%27Commercial/Industrial%27&$orderby=ListPrice%20desc&$top=' . $per_page . '&$skip=' . $skip;

    $response = wp_remote_get( $url, array(
        'headers' => array(
            'Authorization' => 'Bearer {YOUR_ACCESS_TOKEN}',
            'OUID'          => '{YOUR_OUID}',
            'MLS-Aligned-User-Agent' => '{YOUR_USER_AGENT}',
            'Accept'        => 'application/json',
        ),
    ));

    if ( is_wp_error( $response ) ) {
        error_log( 'Error fetching Lake Geneva MLS data: ' . $response->get_error_message() );
        return false;
    }

    $response_body = wp_remote_retrieve_body( $response );
    $data = json_decode( $response_body, true );

    if ( !isset($data['value']) || empty($data['value']) ) {
        error_log( 'No data found in Lake Geneva MLS API response.' );
        return false;
    }

    return $data['value'];
}

// Display Lake Geneva MLS listings with pagination
function jsmls_display_lake_geneva_mls_listings_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'page' => 1,
    ), $atts );

    $page = max( 1, intval( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : $atts['page'] ) );
    $per_page = 10;

    $properties = jsmls_fetch_lake_geneva_mls_data( $page, $per_page );

    if ( empty($properties) ) {
        return '<p>No listings found or failed to fetch data.</p>';
    }

    $output = '<div class="mls-listings-container">';
    foreach ( $properties as $property ) {
        $listing_url = get_site_url() . '/property-detail?listing_key=' . urlencode( $property['ListingKey'] );
        $media_url = jsmls_fetch_media( $property['ListingKey'] );
        $image = $media_url ? esc_url( $media_url ) : 'https://via.placeholder.com/400x300';

        $output .= '<div class="mls-listing-card">';
        $output .= '<a href="' . esc_url( $listing_url ) . '">';
        $output .= '<img src="' . $image . '" alt="Property Image" style="max-width: 100%;">';
        $output .= '<h3>Price: $' . number_format( $property['ListPrice'] ) . '</h3>';
        $output .= '<p>' . esc_html( $property['StreetNumber'] . ' ' . $property['StreetName'] ) . ', ' . esc_html( $property['City'] ) . '</p>';
        $output .= '<p><span class="bedrooms"><i class="fas fa-bed"></i></span>' . esc_html( $property['BedroomsTotal'] ) . ' Beds';
        $output .= ' | <span class="bathrooms"><i class="fas fa-bath"></i></span>' . esc_html( $property['BathroomsTotalInteger'] ) . ' Baths';
        $output .= ' | <span class="sqft"><i class="fas fa-ruler-combined"></i></span>' . esc_html( $property['BuildingAreaTotal'] ) . ' sqft</p>';
        $output .= '<a class="view-details" href="' . esc_url( $listing_url ) . '">View Details</a>';
        $output .= '</a></div>';
    }
    $output .= '</div>';

    $output .= '<div class="pagination">';
    if ( $page > 1 ) {
        $output .= '<a href="' . esc_url( add_query_arg( 'paged', $page - 1 ) ) . '">Previous</a>';
    }
    $output .= '<a href="' . esc_url( add_query_arg( 'paged', $page + 1 ) ) . '">Next</a>';
    $output .= '</div>';

    return $output;
}
