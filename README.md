# metromls_api_integration
Custom MLS Integration When Off-the-Shelf Isn't an Option
# JSMLS WordPress Plugin README

**Plugin Name:** JSMLS  
**Description:** A WordPress plugin to dynamically display MLS listings using data from the MLS Aligned API for real estate agents.  
**Version:** 3.6  
**Author:** Jeff Sperandeo

---

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Available Shortcodes](#available-shortcodes)
- [Important Notes](#important-notes)
- [Support](#support)

---

## Introduction

The JSMLS plugin allows real estate agents to display MLS (Multiple Listing Service) listings on their WordPress websites dynamically. By integrating with the MLS Aligned API, the plugin fetches real-time property data and displays it in various formats such as grids, detailed views, and location-specific listings.

**Website Example:** [https://jadegoodhue.com](https://jadegoodhue.com)

---

## Features

- **Dynamic MLS Listings:** Fetch and display real-time property listings from the MLS Aligned API.
- **Location-Specific Listings:** Display properties from specific locations or cities.
- **Property Details Page:** Show detailed information about individual properties.
- **Customizable Grids:** Display listings in grid formats with customizable styling.
- **Shortcode Support:** Use shortcodes to embed listings and property details anywhere on your site.
- **Caching:** Implements caching mechanisms to improve performance and reduce API calls.

---

## Installation

1. **Download the Plugin:**

   - Ensure you have the plugin files ready. If you've received them as a ZIP file, keep it handy.

2. **Upload the Plugin:**

   - Log in to your WordPress admin dashboard.
   - Navigate to `Plugins` > `Add New`.
   - Click on the `Upload Plugin` button at the top.
   - Choose the plugin ZIP file and click `Install Now`.

3. **Activate the Plugin:**

   - After installation, click the `Activate Plugin` button.

4. **Verify Installation:**

   - Once activated, you should see `JSMLS` listed in your installed plugins.

---

## Configuration

Before using the plugin, you need to configure it with your MLS Aligned API credentials.

### 1. Set Up API Credentials

- **API Base URL:** You need your actual API base URL provided by MLS Aligned.
- **API Token:** Obtain your API token from your MLS Aligned account.
- **Other Headers:** Collect any other necessary headers like `OUID` and `User-Agent`.

### 2. Update Plugin Files

- **Open `jsmls_main.php`:**

  - Locate the `define` statements at the beginning of the file.
  - Replace the placeholders with your actual API credentials.

  ```php
  // Define API credentials
  define('JSMLS_API_URL', 'https://your-api-base-url.com'); // Replace with your API base URL
  define('JSMLS_API_TOKEN', 'YOUR_API_TOKEN'); // Replace with your API token
  ```

- **Update Shared Functions (`jsmls_shared_functions.php`):**

  - Replace placeholders in API calls with your actual credentials.

  ```php
  $url = 'https://your-api-base-url.com/reso/odata/Property(' . $listing_key . ')/Media';
  'Authorization' => 'Bearer YOUR_API_TOKEN', // Replace with your API token
  'OUID'          => 'YOUR_OUID',             // Replace with your OUID
  'MLS-Aligned-User-Agent' => 'YOUR_USER_AGENT', // Replace with your user agent
  ```

- **Update Other Files:**

  - Ensure all files that make API calls have the correct API endpoint and credentials.
  - For example, in `property_detail.php` and any location-specific files.

### 3. Include Shared Functions

- Ensure that the files `jsmls_shared_functions.php` and `location_information.php` are present in the same directory as `jsmls_main.php`.
- These files contain essential functions required by the plugin.

### 4. Secure Your API Credentials

- **Important:** Do not expose your API credentials publicly.
- Ensure that your API tokens and sensitive information are kept secure.
- Do not commit them to public repositories or share them in unsecured channels.

---

## Usage

The plugin uses shortcodes to display listings on your website pages or posts. Below are the available shortcodes and how to use them.

### Available Shortcodes

1. **Location-Specific Four Grid:**

   - **Shortcode Format:**
     ```plaintext
     [location_four_grid]
     ```
   - **Example:**
     ```plaintext
     [lake_geneva_four_grid]
     ```
   - **Description:**
     Displays a grid of property listings for a specific location. Replace `location` with one of the supported locations.

2. **Property Detail Page:**

   - **Shortcode:**
     ```plaintext
     [mls_property_detail]
     ```
   - **Description:**
     Displays detailed information about a single property. The property is identified via the `listing_key` parameter in the URL.

   - **Usage:**
     - Create a page (e.g., `Property Detail`) and include the shortcode `[mls_property_detail]`.
     - When linking to this page, append the `listing_key` parameter, e.g., `https://yourwebsite.com/property-detail?listing_key=12345`.

3. **Location Information:**

   - **Shortcode Format:**
     ```plaintext
     [location_information location="location_name"]
     ```
   - **Example:**
     ```plaintext
     [location_information location="Lake Geneva"]
     ```
   - **Description:**
     Displays real estate information about a specific location, including population, median home value, and market data.

4. **Elkhorn Four Grid:**

   - **Shortcode:**
     ```plaintext
     [elkhorn_four_grid]
     ```
   - **Description:**
     Displays a grid of property listings specifically for Elkhorn.

5. **Other Shortcodes:**

   - The plugin supports various other shortcodes for different display types. Refer to the plugin code or documentation for a complete list.

### Shortcode Attributes

- Many shortcodes accept attributes to customize the output:

  - `page`: The page number for pagination (default: `1`).
  - `per_page`: Number of listings per page (default: `12`).
  - `min_price`: Minimum price filter for listings (default: `0`).

- **Example:**

  ```plaintext
  [lake_geneva_four_grid page="2" per_page="8" min_price="100000"]
  ```

---

## Important Notes

- **Dependencies:**

  - The plugin may use external libraries like Font Awesome for icons. Ensure these are loaded in your theme or plugin.
  - For CSS and JavaScript dependencies, enqueue them properly in WordPress to avoid conflicts.

- **Template Compatibility:**

  - The plugin outputs HTML and inline CSS. You may need to adjust styles to match your theme.
  - Consider moving inline styles to your theme's stylesheet for better maintainability.

- **Caching:**

  - The plugin uses WordPress transients to cache API responses and improve performance.
  - Cache duration is set in functions like `set_transient()` (e.g., `300` seconds).

- **Error Handling:**

  - The plugin includes basic error handling and logging.
  - Check the WordPress debug log if you encounter issues.

---

## Support

If you encounter issues or have questions:

- **Developer:** Jeff Sperandeo
- **Website:** [https://jeffsperandeo.com/](https://jeffsperandeo.com)

---

## License

This plugin is provided as-is without any warranty. Use it at your own risk. Ensure compliance with the MLS Aligned API terms of service.

---

## Changelog

**Version 3.6**

- Added dynamic location-specific shortcodes.
- Improved caching for media fetching.
- Enhanced property detail page with carousel and responsive design.
- Fixed bugs related to API data handling.

---

## Conclusion

You now have the JSMLS plugin set up and configured on your WordPress site. With this plugin, you can provide your visitors with up-to-date property listings and detailed real estate information. Customize the plugin as needed to fit your website's design and functionality.

---

**Disclaimer:** Ensure that you have the rights and permissions to display MLS data on your website. Comply with all relevant regulations and terms of service associated with the MLS Aligned API and real estate data display.
