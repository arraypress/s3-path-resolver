<?php
/**
 * This file contains helper functions for managing Amazon S3 paths in the context of
 * Easy Digital Downloads (EDD) and WooCommerce (WC) plugins. The functions are
 * designed to work with the Path_Resolver class, providing utilities for
 * validating and parsing S3 paths.
 *
 * Functions included:
 * - `is_edd_download_file_s3_url`: Determines if a downloadable file from EDD,
 *   specified by its download ID (and optionally file ID), is stored on an S3 server.
 *   This function checks the file's URL against S3 URL patterns and handles exceptions
 *   with an optional error callback.
 *
 * - `is_wc_download_file_s3_url`: Checks if a WooCommerce product download file,
 *   identified by product ID (and optionally download ID), is hosted on Amazon S3.
 *   It validates the file URL for S3 conformity and supports error handling through
 *   a callback.
 *
 * Both functions ensure compatibility with EDD and WooCommerce, respectively, by
 * validating the existence of necessary functions before proceeding. They also provide
 * flexible error handling and support filtering the results via WordPress hooks.
 *
 * Usage example:
 * $isEDDS3File = is_edd_download_file_s3_url($downloadID, $fileID, 'my_bucket', ['zip'], $errorHandler);
 * $isWCS3File = is_wc_download_file_s3_url($productID, $downloadID, 'my_bucket', ['pdf'], $errorHandler);
 *
 * Note: These functions check for the existence of the Path_Resolver class and the
 * relevant EDD/WC functions to avoid conflicts and redefinitions.
 *
 * @package     ArrayPress/s3-path-resolver
 * @copyright   Copyright (c) 2023, ArrayPress Limited
 * @license     GPL2+
 * @since       1.0.0
 * @author      David Sherlock
 */

namespace ArrayPress\Utils\S3;

use Exception;
use InvalidArgumentException;

if ( ! function_exists( 'is_edd_file_valid_path' ) ) {
	/**
	 * Check if an Easy Digital Downloads (EDD) download file is stored on an S3 provider.
	 *
	 * This function determines if a specific downloadable file from EDD, identified by
	 * download ID and optionally by file ID, is hosted on an Amazon S3 server. It retrieves
	 * the file URL and validates if it matches an S3 URL pattern.
	 *
	 * @param int           $download_id        The ID of the EDD download to check.
	 * @param int|null      $file_id            The ID of the specific file within the download (optional).
	 * @param string        $default_bucket     Default S3 bucket name to use for URL validation.
	 * @param array         $allowed_extensions List of permissible file extensions for downloads.
	 * @param callable|null $error_callback     Function to call for error handling (optional).
	 *
	 * @return bool True if the file is hosted on an S3 provider, false otherwise.
	 * @throws Exception
	 */
	function is_edd_file_valid_path( int $download_id = 0, int $file_id = null, string $default_bucket = 'default_bucket', array $allowed_extensions = [], ?callable $error_callback = null ): bool {

		// Exit early if the download ID is not provided or EDD functions are not available.
		if ( empty( $download_id ) || ! function_exists( 'edd_get_download_files' ) ) {
			return false;
		}

		// Retrieve the downloadable files for the given EDD download.
		$download_files = \edd_get_download_files( $download_id );

		// Initialize the return value.
		$ret = false;

		// Check if the specified file ID is provided and valid.
		if ( $file_id !== null && isset( $download_files[ $file_id ] ) ) {
			$file_url = trim( $download_files[ $file_id ]['file'] );

			// Validate whether the file URL is an S3 path.
			if ( ! empty( $file_url ) && is_valid_path( $file_url, $default_bucket, $allowed_extensions, $error_callback ) ) {
				$ret = true;
			}
		}

		// Apply filters and return the result.
		return apply_filters( 'is_edd_file_valid_path', $ret, $download_id, $file_id, $default_bucket, $allowed_extensions, $error_callback );
	}

}

if ( ! function_exists( 'is_wc_file_valid_path' ) ) {
	/**
	 * Check if a WooCommerce product download file is stored on an Amazon S3 server.
	 *
	 * This function verifies whether a file associated with a WooCommerce product's downloadable
	 * files, identified by product ID and an optional download ID, is hosted on Amazon S3.
	 * It checks the file's URL to ascertain if it conforms to an S3 URL pattern.
	 *
	 * @param int           $product_id         The ID of the WooCommerce product to check.
	 * @param string|null   $download_id        The ID of the specific download within the product (optional).
	 * @param string        $default_bucket     Default S3 bucket name to use for URL validation.
	 * @param array         $allowed_extensions List of permissible file extensions for downloads.
	 * @param callable|null $error_callback     Function to call for error handling (optional).
	 *
	 * @return bool True if the file is hosted on Amazon S3, false otherwise.
	 * @throws Exception
	 */
	function is_wc_file_valid_path( int $product_id = 0, string $download_id = null, string $default_bucket = 'default_bucket', array $allowed_extensions = [], ?callable $error_callback = null ): bool {

		// Exit early if the product ID is not provided or WooCommerce functions are not available.
		if ( empty( $product_id ) || ! function_exists( 'wc_get_product' ) ) {
			return false;
		}

		// Retrieve the specified WooCommerce product.
		$product = \wc_get_product( $product_id );

		// Initialize the return value.
		$ret = false;

		// Ensure the product is downloadable and has downloadable files.
		if ( ! empty( $product ) && $product->is_downloadable() ) {
			$downloads = $product->get_downloads();

			// Exit if there are no downloadable files for the product.
			if ( empty( $downloads ) ) {
				return false;
			}

			// If a specific download ID is provided, use it to get the file URL.
			if ( $download_id !== null && isset( $downloads[ $download_id ] ) ) {
				$file_url = trim( $downloads[ $download_id ]->get_file() );
			}

			// Validate whether the file URL corresponds to an S3 path.
			if ( ! empty( $file_url ) && is_valid_path( $file_url, $default_bucket, $allowed_extensions, $error_callback ) ) {
				$ret = true;
			}
		}

		// Apply filters and return the result.
		return apply_filters( 'is_wc_file_valid_path', $ret, $product_id, $download_id, $default_bucket, $allowed_extensions, $error_callback );
	}
}