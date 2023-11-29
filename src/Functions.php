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
 * @package     ArrayPress/s3-path-resolver
 * @copyright   Copyright (c) 2023, ArrayPress Limited
 * @license     GPL2+
 * @since       1.0.0
 * @author      David Sherlock
 */

namespace ArrayPress\Utils\S3;

use Exception;
use InvalidArgumentException;

if ( ! function_exists( 'parse_path' ) ) {
	/**
	 * Parse the provided path to extract the bucket and object key.
	 *
	 * @param string        $path                 The S3 path.
	 * @param string        $default_bucket       The default bucket to use if none is provided in the path.
	 * @param array         $allowed_extensions   List of allowed file extensions.
	 * @param callable|null $error_callback       Callback function for error handling.
	 * @param array         $disallowed_protocols List of protocols that are not allowed in S3 paths.
	 *
	 * @return array|false An associative array with 'bucket' and 'object' keys or false on failure.
	 * @throws Exception
	 */
	function parse_path( string $path, string $default_bucket = '', array $allowed_extensions = [], ?callable $error_callback = null, array $disallowed_protocols = [] ) {
		$resolver = new Path_Resolver( $default_bucket, $allowed_extensions, $disallowed_protocols );
		try {
			return $resolver->parse_path( $path );
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			// Handle the exception or log it if needed
			return false;
		}
	}
}

if ( ! function_exists( 'is_valid_path' ) ) {
	/**
	 * Determines if the provided path is a valid S3 path.
	 *
	 * @param string        $path                 The path to check.
	 * @param string        $default_bucket       The default bucket to use if none is provided in the path.
	 * @param array         $allowed_extensions   List of allowed file extensions.
	 * @param callable|null $error_callback       Callback function for error handling.
	 * @param array         $disallowed_protocols List of protocols that are not allowed in S3 paths.
	 *
	 * @return bool True if the path is a valid S3 path, false otherwise.
	 * @throws Exception
	 */
	function is_valid_path( string $path, string $default_bucket = '', array $allowed_extensions = [], ?callable $error_callback = null, array $disallowed_protocols = [] ): bool {
		$resolver = new Path_Resolver( $default_bucket, $allowed_extensions, $disallowed_protocols );
		try {
			return $resolver->is_valid_path( $path );
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			// Handle the exception or log it if needed
			return false;
		}
	}
}