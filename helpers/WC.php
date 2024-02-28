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
use function ArrayPress\S3\isValidPath;

if ( ! function_exists( 'isS3Path' ) ) {
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
	 * $productId = 456; // The ID of the WooCommerce product.
	 * $downloadId = 'abc123'; // Optional: The specific download ID within the product.
	 * $isS3Path = isS3Path($productId, $downloadId, 'my_bucket', ['pdf', 'docx'], function($error) {
	 *     // Error handling logic here.
	 *     echo "Error while validating S3 URL: $error";
	 * });
	 *
	 * if ($isS3Path) {
	 *     echo "The downloadable file is hosted on S3.";
	 * } else {
	 *     echo "The file is not on S3 or failed validation.";
	 * }
	 *
	 * @param int           $productId           The ID of the WooCommerce product to check.
	 * @param string|null   $downloadId          Optional. The specific download ID within the product.
	 * @param string        $defaultBucket       The default S3 bucket name for URL validation.
	 * @param array         $allowedExtensions   List of permissible file extensions for downloads.
	 * @param array         $disallowedProtocols Optional. List of protocols that are not allowed in S3 paths.
	 * @param callable|null $errorCallback       Optional. Function to call for error handling.
	 *
	 * @return bool True if the file is hosted on Amazon S3, false otherwise.
	 * @throws Exception If validation fails or if necessary WooCommerce functions are unavailable.
	 */
	function isS3Path( int $productId, ?string $downloadId = null, string $defaultBucket = '', array $allowedExtensions = [], array $disallowedProtocols = [], ?callable $errorCallback = null ): bool {

		// Exit early if the product ID is not provided or WooCommerce functions are not available.
		if ( empty( $productId ) || ! function_exists( 'wc_get_product' ) ) {
			return false;
		}

		// Retrieve the specified WooCommerce product.
		$product = wc_get_product( $productId );

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
			$path = null;
			if ( $downloadId !== null && isset( $downloads[ $downloadId ] ) ) {
				$path = trim( $downloads[ $downloadId ]->get_file() );
			}

			// Validate whether the file URL corresponds to an S3 path.
			if ( ! empty( $path ) && isValidPath( $path, $defaultBucket, $allowedExtensions, $disallowedProtocols, $errorCallback ) ) {
				$retval = true;
			}
		}

		// Return the result
		return $retval;
	}
}
