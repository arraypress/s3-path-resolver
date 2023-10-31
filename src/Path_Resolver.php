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
		private array $disallowed_protocols = [ 'https://', 'http://', 'edd-dbfs', 'ftp://' ];

		/**
		 * Path_Resolver constructor.
		 *
		 * @param string $default_bucket       The default bucket to use if none is provided in the path.
		 * @param array  $allowed_extensions   List of allowed file extensions.
		 * @param array  $disallowed_protocols List of protocols that are not allowed in S3 paths.
		 */
		public function __construct( string $default_bucket = '', array $allowed_extensions = [], array $disallowed_protocols = [] ) {
			$this->default_bucket     = trim( $default_bucket, '/' );
			$this->allowed_extensions = $allowed_extensions;

			if ( ! empty( $disallowed_protocols ) ) {
				$this->disallowed_protocols = $disallowed_protocols;
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

				return [
					'bucket' => $this->default_bucket,
					'object' => $this->sanitize_object_key( $path )
				];
			}

			$segments = explode( '/', ltrim( $path, '/' ) );
			if ( count( $segments ) < 2 ) {
				throw new Exception( "The path does not contain a valid object key." );
			}

			$bucket = $segments[0];
			$this->validate_bucket( $bucket );

			$object_key = $this->sanitize_object_key( implode( '/', array_slice( $segments, 1 ) ) );

			return [
				'bucket' => $bucket,
				'object' => $object_key
			];
		}

		/**
		 * Validate the provided bucket name based on S3's naming conventions.
		 *
		 * @param string $bucket The bucket name to validate.
		 *
		 * @throws Exception If the bucket name is invalid.
		 */
		private function validate_bucket( string $bucket ): void {
			if ( strlen( $bucket ) < 3 || strlen( $bucket ) > 63 ) {
				throw new Exception( "Bucket name length should be between 3 and 63 characters." );
			}

			if ( ! preg_match( '/^[a-z0-9\-\.]+$/', $bucket ) ) {
				throw new Exception( "Bucket name contains invalid characters. Only lowercase letters, numbers, hyphens, and dots are allowed." );
			}
		}

		/**
		 * Sanitize the object key to ensure it adheres to S3's naming conventions.
		 *
		 * @param string $object_key The object key to sanitize.
		 *
		 * @return string The sanitized object key.
		 */
		private function sanitize_object_key( string $object_key ): string {
			return preg_replace( '/[^a-zA-Z0-9\-_\.\/]/', '', $object_key );
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
		 * This method checks if the path does not contain any disallowed protocols
		 * and if it has a valid file extension.
		 *
		 * @param string $path The path to check.
		 *
		 * @return bool True if the path is a valid S3 path, false otherwise.
		 */
		public function is_s3_path( string $path ): bool {
			if ( $this->has_disallowed_protocol( $path ) ) {
				return false;
			}

			return $this->has_valid_file_extension( $path );
		}

	}

endif;