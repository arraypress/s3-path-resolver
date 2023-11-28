<?php
/**
 * The Path_Resolver class manages and provides utilities related to S3 paths.
 *
 * This class offers methods to parse S3 paths, validate and sanitize object keys,
 * and ensure that paths adhere to S3's naming conventions. It checks for disallowed
 * protocols, validates file extensions, and ensures that bucket names are valid
 * according to S3's naming rules. The class also provides the ability to set a
 * default bucket, specify allowed file extensions, and define disallowed protocols.
 *
 * Example usage:
 * $resolver = new Path_Resolver( 'my-default-bucket', ['zip', 'jpg'], ['ftp://'] );
 * $pathInfo = $resolver->parse_path( '/my-bucket/my-object.zip' );
 * if (!$resolver->has_valid_file_extension($pathInfo['object'])) {
 *     throw new Exception("Invalid file extension.");
 * }
 *
 * Note: This class checks for its own existence before being defined to prevent redefinition.
 *
 * @package     ArrayPress/s3-path-resolver
 * @copyright   Copyright (c) 2023, ArrayPress Limited
 * @license     GPL2+
 * @since       1.0.0
 * @author      David Sherlock
 */

namespace ArrayPress\Utils\S3;

use Exception;

/**
 * Manages S3 providers and offers utilities for interacting with them and their regions.
 *
 * If the class already exists in the namespace, it won't be redefined.
 */
if ( ! class_exists( __NAMESPACE__ . '\\Path_Resolver' ) ) :

	class Path_Resolver {

		/**
		 * @var string The default bucket to use if none is provided.
		 */
		private string $default_bucket;

		/**
		 * @var array List of allowed file extensions.
		 */
		private array $allowed_extensions;

		/**
		 * @var array List of protocols that are not allowed in S3 paths.
		 */
		private array $disallowed_protocols = [ 'https://', 'http://', 'edd-dbfs', 'ftp://', 's3://' ];

		/**
		 * Path_Resolver constructor.
		 *
		 * @param string $default_bucket       The default bucket to use if none is provided in the path.
		 * @param array  $allowed_extensions   List of allowed file extensions.
		 * @param array  $disallowed_protocols List of protocols that are not allowed in S3 paths.
		 *
		 * @throws Exception
		 */
		public function __construct( string $default_bucket = '', array $allowed_extensions = [], array $disallowed_protocols = [] ) {
			$this->default_bucket     = trim( $default_bucket, '/' );
			$this->allowed_extensions = array_unique( $allowed_extensions );

			if ( ! empty( $disallowed_protocols ) ) {
				$this->disallowed_protocols = $disallowed_protocols;
			}

			// Validate the default bucket if it's not empty
			if ( ! empty( $this->default_bucket ) ) {
				Validate::bucket( $this->default_bucket );
			}
		}

		/**
		 * Parse the provided path to extract the bucket and object key.
		 *
		 * @param string $path The S3 path.
		 *
		 * @return array An associative array with 'bucket' and 'object' keys.
		 * @throws Exception If the path is invalid.
		 */
		public function parse_path( string $path ): array {
			$path = trim( $path );

			if ( empty( $path ) ) {
				throw new Exception( "The provided path is empty." );
			}

			if ( $this->has_disallowed_protocol( $path ) ) {
				throw new Exception( "The provided path contains a disallowed protocol." );
			}

			if ( ! $this->has_valid_file_extension( $path ) ) {
				throw new Exception( "The provided path has an invalid file extension." );
			}

			if ( $path[0] !== '/' ) {
				if ( empty( $this->default_bucket ) ) {
					throw new Exception( "No bucket provided and no default bucket set." );
				}

				// Validate the default bucket again to ensure it's still valid
				Validate::bucket( $this->default_bucket );

				return [
					'bucket'     => $this->default_bucket,
					'object_key' => Sanitize::object_key( $path )
				];
			}

			$segments = explode( '/', ltrim( $path, '/' ) );
			if ( count( $segments ) < 2 ) {
				throw new Exception( "The path does not contain a valid object key." );
			}

			$bucket = $segments[0];
			Validate::bucket( $bucket );

			$object_key = Sanitize::object_key( implode( '/', array_slice( $segments, 1 ) ) );

			return [
				'bucket'     => $bucket,
				'object_key' => $object_key
			];
		}

		/**
		 * Check if the provided path contains any disallowed protocols.
		 *
		 * @param string $path The path to check.
		 *
		 * @return bool True if the path contains a disallowed protocol, false otherwise.
		 */
		private function has_disallowed_protocol( string $path ): bool {
			foreach ( $this->disallowed_protocols as $protocol ) {
				if ( strpos( $path, $protocol ) !== false ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Check if the object key has a valid file extension.
		 *
		 * @param string $path The object key to check.
		 *
		 * @return bool True if the object key has a valid file extension, false otherwise.
		 */
		private function has_valid_file_extension( string $path ): bool {
			$extension = pathinfo( $path, PATHINFO_EXTENSION );

			// If allowedExtensions is set and not empty, check against it
			if ( ! empty( $this->allowed_extensions ) ) {
				return in_array( $extension, $this->allowed_extensions );
			}

			// Otherwise, just check for the existence of any extension
			return ! empty( $extension );
		}

		/**
		 * Check if the provided path is a valid S3 path.
		 *
		 * This method checks if the path does not contain any disallowed protocols,
		 * if it has a valid file extension, and if it contains or defaults to a valid bucket.
		 *
		 * @param string $path The path to check.
		 *
		 * @return bool True if the path is a valid S3 path, false otherwise.
		 */
		public function is_valid_path( string $path ): bool {
			// Check for disallowed protocol.
			if ( $this->has_disallowed_protocol( $path ) ) {
				return false;
			}

			// Check for valid file extension.
			if ( ! $this->has_valid_file_extension( $path ) ) {
				return false;
			}

			// Check if path starts with a '/' (indicating it includes a bucket name).
			if ( $path[0] === '/' ) {
				// Extract the bucket name from the path.
				$bucket = explode( '/', ltrim( $path, '/' ) )[0];
				try {
					// Validate the extracted bucket name.
					Validate::bucket( $bucket );
				} catch ( Exception $e ) {
					// If validation fails, return false.
					return false;
				}
			} else {
				// If the path doesn't start with '/', check the default bucket.
				if ( empty( $this->default_bucket ) ) {
					return false;
				}

				// Validate the default bucket.
				try {
					Validate::bucket( $this->default_bucket );
				} catch ( Exception $e ) {
					// If validation fails, return false.
					return false;
				}
			}

			// If all checks pass, return true.
			return true;
		}

	}

endif;