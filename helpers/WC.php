<?php
/**
 * Amazon S3 Path Validator for WooCommerce (WC)
 *
 * @package       ArrayPress/s3-path-resolver
 * @copyright     Copyright 2023, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\S3\WC;

use Exception;

if ( ! function_exists( 'is_s3_path' ) ) {
	/**
	 * Assists in identifying if a WooCommerce product's downloadable file resides on Amazon S3. This function is essential
	 * for integrating WooCommerce stores with Amazon S3, providing a reliable method for verifying that a product's download
	 * file is stored on S3. It streamlines the process of validating S3 URLs for WooCommerce downloadable products, ensuring
	 * that files adhere to S3 storage conventions and facilitating secure, scalable digital product distribution.
	 *
	 * Usage:
	 * This utility function is invaluable for WooCommerce store operators utilizing Amazon S3 for storing downloadable content.
	 * It checks if a WooCommerce product's downloadable file URL is formatted correctly as an S3 URL, enabling efficient error
	 * handling and integration with existing WooCommerce setups. An optional error callback provides flexibility in managing
	 * validation failures or configuration issues.
	 *
	 * Example:
	 * $productID = 456; // The ID of the WooCommerce product.
	 * $downloadID = 'abc123'; // Optional: The specific download ID within the product.
	 * $isS3Path = is_s3_path($productID, $downloadID, 'my_bucket', ['pdf', 'docx'], function($error) {
	 *     // Error handling logic here.
	 *     echo "Error while validating S3 URL: $error";
	 * });
	 * if ($isS3Path) {
	 *     echo "The downloadable file is hosted on S3.";
	 * } else {
	 *     echo "The file is not on S3 or failed validation.";
	 * }
	 *
	 * @param int           $product_id           The ID of the WooCommerce product to check.
	 * @param string|null   $download_id          Optional. The specific download ID within the product.
	 * @param string        $default_bucket       The default S3 bucket name for URL validation.
	 * @param array         $allowed_extensions   List of permissible file extensions for downloads.
	 * @param array         $disallowed_protocols Optional. List of protocols that are not allowed in S3 paths.
	 * @param callable|null $error_callback       Optional. Function to call for error handling.
	 *
	 * @return bool True if the file is hosted on Amazon S3, false otherwise.
	 * @throws Exception If validation fails or if necessary WooCommerce functions are unavailable.
	 */
	function is_s3_path( int $product_id, string $download_id, string $default_bucket = '', array $allowed_extensions = [], array $disallowed_protocols = [], ?callable $error_callback = null ): bool {

		// Exit early if the product ID is not provided or WooCommerce functions are not available.
		if ( empty( $product_id ) || ! function_exists( 'wc_get_product' ) ) {
			return false;
		}

		// Retrieve the specified WooCommerce product.
		$product = \wc_get_product( $product_id );

		// Initialize the return value.
		$retval = false;

		// Ensure the product is downloadable and has downloadable files.
		if ( ! empty( $product ) && $product->is_downloadable() ) {
			$downloads = $product->get_downloads();

			// Exit if there are no downloadable files for the product.
			if ( empty( $downloads ) ) {
				return false;
			}

			// If a specific download ID is provided, use it to get the file URL.
			if ( isset( $downloads[ $download_id ] ) ) {
				$file_url = trim( $downloads[ $download_id ]->get_file() );
			}

			// Validate whether the file URL corresponds to an S3 path.
			if ( ! empty( $file_url ) && \ArrayPress\S3\isValidPath( $file_url, $default_bucket, $allowed_extensions, $disallowed_protocols, $error_callback ) ) {
				$retval = true;
			}
		}

		// Return the result
		return $retval;
	}
}