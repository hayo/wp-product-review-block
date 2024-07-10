<?php
/*
Plugin Name: Product Review Block
Description: Adds a product review block to articles.
Version: 1.0
Author: Hayo Bethlehem
*/

function register_product_review_block() {
    register_block_type(
        'custom/product-review-block',
        array(
            'attributes' => array(
                'productName' => array(
                    'type' => 'string',
                    'default' => 'Product Name',
                ),
                'manufacturerName' => array(
                    'type' => 'string',
                    'default' => 'Manufacturer Name',
                ),
                'productURL' => array(
                    'type' => 'string',
                    'default' => 'https://www.example.com/product-page',
                ),
                'productImage' => array(
                    'type' => 'string',
                    'default' => '', // Default image URL
                ),
                'ratingValue' => array(
                    'type' => 'number',
                    'default' => 4, // Default rating
                ),
                'reviewBody' => array(
                    'type' => 'string',
                    'default' => 'Here is the review text...',
                ),
            ),
            'render_callback' => 'render_product_review_block',
        )
    );
}
add_action('init', 'register_product_review_block');

function render_product_review_block($attributes) {
    // Retrieve attributes
    $productName = $attributes['productName'];
    $manufacturerName = $attributes['manufacturerName'];
    $productURL = $attributes['productURL'];
    $productImage = $attributes['productImage'];
    $ratingValue = $attributes['ratingValue'];

    // Get the saved block content for the post
    $post_content = get_post_field('post_content', get_the_ID());

    // Extract reviewBody for each review from the block content
    preg_match_all('/<!-- wp:custom\/product-review-block {"productName":"' . preg_quote($productName) . '".*?} -->(.*?)<!-- \/wp:custom\/product-review-block -->/s', $post_content, $matches);

    $output = '';

    if (isset($matches[1])) {
        foreach ($matches[1] as $index => $reviewBlock) {
            preg_match('/<div itemprop="reviewBody" class="review-body">(.*?)<\/div>/s', $reviewBlock, $bodyMatches);
            $reviewBody = isset($bodyMatches[1]) ? wp_kses_post($bodyMatches[1]) : '';

            $output .= '<section itemscope itemtype="http://schema.org/Review" class="review" id="review-' . sanitize_title($productName) . '-' . $index . '">';
            $output .= '<h2 itemprop="itemReviewed" itemscope itemtype="http://schema.org/Product"><a href="' . esc_url($productURL) . '" itemprop="url">' . esc_html($productName) . '</a></h2><div class="reviewMeta">';

            // Check if the product image exists
            if (!empty($productImage)) {

                $imageUrlParts = pathinfo($productImage);
                $filename = $imageUrlParts['filename']; // Extracting 'scar-l'
                $extension = $imageUrlParts['extension']; // Extracting 'webp'

                // List of available image sizes
                $imageSizes = array('400x400', '500x500', '700x700');

                // Output the responsive image code
                $output .= '<a href="' . esc_url($productImage) . '" class="lightbox"><picture itemprop="image" itemscope itemtype="http://schema.org/ImageObject">';
                foreach ($imageSizes as $size) {
                    $modifiedImageUrl = str_replace($filename, $filename . '-' . $size, $productImage); // Adding size info
                    $output .= '<source media="(min-width: ' . explode('x', $size)[0] . 'px)" srcset="' . esc_url($modifiedImageUrl) . '">';
                }
                $output .= '<img src="' . esc_url($productImage) . '" decoding="async" alt="Product Image" itemprop="contentUrl" />';
                $output .= '</picture></a>';
            }

            $output .= '<strong itemprop="manufacturer">' . esc_html($manufacturerName) . '</strong> | ';
            $output .= '<a href="' . esc_url($productURL) . '" itemprop="url">Product Page</a> ';
            $output .= '<span itemprop="reviewRating" class="rating stars' . esc_html($ratingValue) . '" itemscope itemtype="http://schema.org/Rating"><span itemprop="ratingValue">' . esc_html($ratingValue) . '</span></span></div>';

            // Output the corresponding review body for each review
            $output .= '<div class="review-body" itemprop="reviewBody">' . $reviewBody . '</div>';
            $output .= '</section>';
        }
    }

    return $output;
}

// Enqueue necessary scripts and styles
function product_review_block_enqueue_assets() {
    wp_enqueue_script(
        'product-review-block-script',
        plugin_dir_url(__FILE__) . 'block.js', // Path to your block.js file
        array('wp-blocks', 'wp-editor', 'wp-components', 'wp-element'), // Dependencies
        filemtime(plugin_dir_path(__FILE__) . 'block.js') // Version number
    );

    wp_enqueue_style(
        'product-review-block-styles', // Handle name
        plugin_dir_url(__FILE__) . 'block-editor-styles.css', // Path to your block-editor-styles.css file
        array(), // Dependencies
        filemtime(plugin_dir_path(__FILE__) . 'block-editor-styles.css') // Version number
    );

}
add_action('enqueue_block_editor_assets', 'product_review_block_enqueue_assets');


function extract_product_names_from_reviews() {
    global $post;

    // Get the saved block content for the post
    $post_content = get_post_field('post_content', $post->ID);

    // Initialize an array to store product names
    $product_names = array();

    // Regular expression pattern to match product review blocks
    $pattern = '/<!-- wp:custom\/product-review-block.*?productName":"(.*?)"/s';

    // Perform a global regular expression match to find all product names
    preg_match_all($pattern, $post_content, $matches);

    // If matches are found, extract product names and add to the array
    if (!empty($matches[1])) {
        $product_names = $matches[1];
    }

    return $product_names;
}

function review_index_shortcode() {
    $product_names = extract_product_names_from_reviews();

    // Create a list of links using the retrieved product names
    $links = '<ul class="review-links">';
    foreach ($product_names as $name) {
        $links .= '<li><a href="#review-' . sanitize_title($name) . '">' . esc_html($name) . '</a></li>';
    }
    $links .= '</ul>';

    return $links;
}
add_shortcode('review_index', 'review_index_shortcode');


?>