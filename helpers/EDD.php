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
use function ArrayPress\S3\isValidPath;

if ( ! function_exists( 'isS3Path' ) ) {
	/**
	 * Validates if an Easy Digital Downloads (EDD) download file URL points to an S3 location. This function
	 * enhances EDD's digital asset management by ensuring that files stored on Amazon S3 are correctly identified,
	 * supporting secure and efficient delivery of downloadable content. It checks the download file against S3 URL
	 * patterns, leveraging the PathResolver class for accurate validation. This utility is crucial for EDD
	 * store owners using Amazon S3 for file storage, offering an integrated solution for file validation and error management.
	 *
	 * Usage:
	 * Utilize this function to verify if a specific file associated with an EDD product is hosted on S3. This is
	 * particularly useful for stores that distribute files stored on S3, ensuring that only valid, properly
	 * formatted S3 URLs are processed. An optional error callback can be provided to handle validation failures or misconfigurations.
	 *
	 * Example:
	 * $downloadId = 123; // The ID of the EDD download.
	 * $fileId = 1; // Optional: Specific file ID within the download.
	 * $isS3Path = isS3Path( $downloadId, $fileId, 'my_bucket', [ 'zip' ], [ 'http', 'https' ], function( $error ) {
	 *     // Handle error.
	 *     echo "Error validating S3 URL: $error";
	 * });
	 *
	 * if ( $isS3Path ) {
	 *     echo "The file is hosted on S3.";
	 * } else {
	 *     echo "The file is not hosted on S3 or validation failed.";
	 * }
	 *
	 * @param int           $downloadId          The ID of the EDD download to check.
	 * @param int|null      $fileId              Optional. The ID of a specific file within the download.
	 * @param string        $defaultBucket       Optional. The default S3 bucket name for URL validation.
	 * @param array         $allowedExtensions   Optional. List of permissible file extensions for downloads.
	 * @param array         $disallowedProtocols Optional. List of protocols that are not allowed in S3 paths.
	 * @param callable|null $errorCallback       Optional. Function to call for error handling.
	 *
	 * @return bool True if the file is hosted on an S3 provider, false otherwise.
	 * @throws Exception If validation fails or necessary functions/classes are missing.
	 */
	function isS3Path( int $downloadId, int $fileId = null, string $defaultBucket = '', array $allowedExtensions = [], array $disallowedProtocols = [], ?callable $errorCallback = null ): bool {

		// Exit early if the download ID is not provided or EDD functions are not available.
		if ( empty( $downloadId ) || ! function_exists( 'edd_get_download_files' ) ) {
			return false;
		}

		// Retrieve the downloadable files for the given EDD download.
		$downloadFiles = \edd_get_download_files( $downloadId );

		// Initialize the return value.
		$retval = false;

		// Check if the specified file ID is provided and valid.
		if ( isset( $downloadFiles[ $fileId ] ) ) {
			$path = trim( $downloadFiles[ $fileId ]['file'] );

			// Validate whether the file URL is an S3 path.
			if ( ! empty( $path ) && isValidPath( $path, $defaultBucket, $allowedExtensions, $disallowedProtocols, $errorCallback ) ) {
				$retval = true;
			}
		}

		// Return the result
		return $retval;
	}

}