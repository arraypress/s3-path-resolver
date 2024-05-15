<?php
/**
 * These helper functions provide utilities for working with S3 paths using the Path_Resolver class.
 *
 * These functions allow you to determine if a given path is a valid S3 path using the `is_s3_path` function.
 * Additionally, you can parse S3 paths to extract the bucket and object key using the `parse_path` function.
 * These functions handle exceptions and provide error callback options for robust path management.
 *
 * Example usage:
 * $isS3Path = is_s3_path( '/my-bucket/my-object.zip' );
 * $pathInfo = parse_path( '/my-bucket/my-object.zip', 'my-default-bucket' );
 * if ( ! $pathInfo ) {
 *     throw new Exception( "Invalid S3 path." );
 * }
 *
 * Note: These functions check for the existence of the Path_Resolver class to prevent redefinition.
 *
 * @package       ArrayPress/s3-path-resolver
 * @copyright     Copyright (c) 2023, ArrayPress Limited
 * @license       GPL2+
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\S3;

use Exception;
use function is_callable;
use function call_user_func;

if ( ! function_exists( 'parse_s3_path' ) ) {
	/**
	 * Parse the provided path to extract the bucket and object key.
	 *
	 * @param string        $path                The S3 path.
	 * @param string        $defaultBucket       Optional. The default bucket to use if none is provided in the path.
	 * @param array         $allowedExtensions   Optional. List of allowed file extensions.
	 * @param array         $disallowedProtocols Optional. List of protocols that are not allowed in S3 paths.
	 * @param callable|null $errorCallback       Optional. Callback function for error handling.
	 *
	 * @return false|object An associative array or object with 'bucket' and 'object' keys, or false on failure.
	 * @throws Exception
	 */
	function parse_s3_path( string $path, string $defaultBucket = '', array $allowedExtensions = [], array $disallowedProtocols = [], ?callable $errorCallback = null ) {
		$resolver = new PathResolver( $defaultBucket, $allowedExtensions, $disallowedProtocols );
		try {
			return $resolver->parsePath( $path );
		} catch ( Exception $e ) {
			if ( is_callable( $errorCallback ) ) {
				call_user_func( $errorCallback, $e );
			}

			// Handle the exception or log it if needed
			return false;
		}
	}
}

if ( ! function_exists( 'is_valid_s3_path' ) ) {
	/**
	 * Determines if the provided path is a valid S3 path.
	 *
	 * @param string        $path                The path to check.
	 * @param string        $defaultBucket       Optional. The default bucket to use if none is provided in the path.
	 * @param array         $allowedExtensions   Optional. List of allowed file extensions.
	 * @param array         $disallowedProtocols Optional. List of protocols that are not allowed in S3 paths.
	 * @param callable|null $errorCallback       Optional. Callback function for error handling.
	 *
	 * @return bool True if the path is a valid S3 path, false otherwise.
	 * @throws Exception
	 */
	function is_valid_s3_path( string $path, string $defaultBucket = '', array $allowedExtensions = [], array $disallowedProtocols = [], ?callable $errorCallback = null ): bool {
		$resolver = new PathResolver( $defaultBucket, $allowedExtensions, $disallowedProtocols );
		try {
			return $resolver->isValidPath( $path );
		} catch ( Exception $e ) {
			if ( is_callable( $errorCallback ) ) {
				call_user_func( $errorCallback, $e );
			}

			// Handle the exception or log it if needed
			return false;
		}
	}
}