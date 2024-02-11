<?php
/**
 * Amazon S3 Path Validator for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/s3-path-resolver
 * @copyright     Copyright 2023, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\S3\EDD;

use Exception;

if ( ! function_exists( 'is_s3_path' ) ) {
	/**
	 * Validates if an Easy Digital Downloads (EDD) download file URL points to an S3 location. This function
	 * enhances EDD's digital asset management by ensuring that files stored on Amazon S3 are correctly identified,
	 * supporting secure and efficient delivery of downloadable content. It checks the download file against S3 URL
	 * patterns, leveraging the Path_Resolver class for accurate validation. This utility is crucial for EDD
	 * store owners using Amazon S3 for file storage, offering an integrated solution for file validation and error management.
	 *
	 * Usage:
	 * Utilize this function to verify if a specific file associated with an EDD product is hosted on S3. This is
	 * particularly useful for stores that distribute files stored on S3, ensuring that only valid, properly
	 * formatted S3 URLs are processed. An optional error callback can be provided to handle validation failures or misconfigurations.
	 *
	 * Example:
	 * $downloadID = 123; // The ID of the EDD download.
	 * $fileID = 1; // Optional: Specific file ID within the download.
	 * $isS3Path = is_s3_path( $downloadID, $fileID, 'my_bucket', [ 'zip' ], [ 'http', 'https' ], function( $error ) {
	 *     // Handle error.
	 *     echo "Error validating S3 URL: $error";
	 * });
	 * if ( $isS3Path ) {
	 *     echo "The file is hosted on S3.";
	 * } else {
	 *     echo "The file is not hosted on S3 or validation failed.";
	 * }
	 *
	 * @param int           $download_id          The ID of the EDD download to check.
	 * @param int|null      $file_id              Optional. The ID of a specific file within the download.
	 * @param string        $default_bucket       Optional. The default S3 bucket name for URL validation.
	 * @param array         $allowed_extensions   Optional. List of permissible file extensions for downloads.
	 * @param array         $disallowed_protocols Optional. List of protocols that are not allowed in S3 paths.
	 * @param callable|null $error_callback       Optional. Function to call for error handling.
	 *
	 * @return bool True if the file is hosted on an S3 provider, false otherwise.
	 * @throws Exception If validation fails or necessary functions/classes are missing.
	 */
	function is_s3_path( int $download_id, int $file_id, string $default_bucket = '', array $allowed_extensions = [], array $disallowed_protocols = [], ?callable $error_callback = null ): bool {

		// Exit early if the download ID is not provided or EDD functions are not available.
		if ( empty( $download_id ) || ! function_exists( 'edd_get_download_files' ) ) {
			return false;
		}

		// Retrieve the downloadable files for the given EDD download.
		$download_files = \edd_get_download_files( $download_id );

		// Initialize the return value.
		$retval = false;

		// Check if the specified file ID is provided and valid.
		if ( isset( $download_files[ $file_id ] ) ) {
			$file_url = trim( $download_files[ $file_id ]['file'] );

			// Validate whether the file URL is an S3 path.
			if ( ! empty( $file_url ) && \ArrayPress\S3\isValidPath( $file_url, $default_bucket, $allowed_extensions, $disallowed_protocols, $error_callback ) ) {
				$retval = true;
			}
		}

		// Return the result
		return $retval;
	}

}