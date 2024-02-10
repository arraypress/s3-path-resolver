<?php
/**
 * The PathResolver class manages and provides utilities related to S3 paths.
 *
 * This class offers methods to parse S3 paths, validate and sanitize object keys,
 * and ensure that paths adhere to S3's naming conventions. It checks for disallowed
 * protocols, validates file extensions, and ensures that bucket names are valid
 * according to S3's naming rules. The class also provides the ability to set a
 * default bucket, specify allowed file extensions, and define disallowed protocols.
 *
 * Example usage:
 * $resolver = new PathResolver( 'my-default-bucket', ['zip', 'jpg'], ['ftp://'] );
 * $pathInfo = $resolver->parse_path( '/my-bucket/my-object.zip' );
 * if (!$resolver->hasValidFileExtension($pathInfo['object'])) {
 *     throw new Exception("Invalid file extension.");
 * }
 *
 * Note: This class checks for its own existence before being defined to prevent redefinition.
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
use InvalidArgumentException;

/**
 * Manages S3 providers and offers utilities for interacting with them and their regions.
 *
 * If the class already exists in the namespace, it won't be redefined.
 */
if ( ! class_exists( __NAMESPACE__ . '\\PathResolver' ) ) :

	class PathResolver {

		/**
		 * @var string The default bucket to use if none is provided.
		 */
		private string $defaultBucket;

		/**
		 * @var array List of allowed file extensions.
		 */
		private array $allowedExtensions;

		/**
		 * @var array List of protocols that are not allowed in S3 paths.
		 */
		private array $disallowedProtocols = [
			'https://',
			'http://',
			'edd-dbfs',
			'ftp://',
			's3://'
		];

		/**
		 * PathResolver constructor.
		 *
		 * @param string $defaultBucket       The default bucket to use if none is provided in the path.
		 * @param array  $allowedExtensions   List of allowed file extensions.
		 * @param array  $disallowedProtocols List of protocols that are not allowed in S3 paths.
		 *
		 * @throws InvalidArgumentException Thrown if the provided default bucket name is invalid.
		 */
		public function __construct( string $defaultBucket = '', array $allowedExtensions = [], array $disallowedProtocols = [] ) {
			$this->setDefaultBucket( $defaultBucket );
			$this->setAllowedExtensions( $allowedExtensions );
			$this->setDisallowedProtocols( $disallowedProtocols );
		}

		/**
		 * Sets the default bucket to use if none is provided in the path.
		 *
		 * This method also trims the bucket name of any leading or trailing slashes and
		 * validates the bucket name according to S3's naming conventions. If the bucket name
		 * is invalid, an InvalidArgumentException is thrown.
		 *
		 * @param string $defaultBucket The default bucket name to set.
		 *
		 * @throws InvalidArgumentException If the provided bucket name is invalid.
		 */
		public function setDefaultBucket( string $defaultBucket ): void {
			$this->defaultBucket = trim( $defaultBucket, '/' );
			if ( ! empty( $this->defaultBucket ) ) {
				Validate::bucket( $this->defaultBucket );
			}
		}

		/**
		 * Sets the list of allowed file extensions.
		 *
		 * This method ensures that the list of allowed extensions is unique and updates
		 * the class's configuration accordingly. File extensions should be provided without
		 * leading dots (e.g., 'jpg' instead of '.jpg').
		 *
		 * @param array $allowedExtensions An array of allowed file extensions.
		 */
		public function setAllowedExtensions( array $allowedExtensions ): void {
			$this->allowedExtensions = array_unique( $allowedExtensions );
		}

		/**
		 * Adds a single allowed file extension to the list of allowed extensions.
		 *
		 * This method ensures that the added extension is unique within the list of allowed
		 * extensions. It does not add the extension if it already exists in the list.
		 * File extensions should be provided without leading dots (e.g., 'jpg' instead of '.jpg').
		 *
		 * @param string $extension The file extension to add to the list of allowed extensions.
		 */
		public function addAllowedExtension( string $extension ): void {
			$extension = trim( $extension );
			if ( ! in_array( $extension, $this->allowedExtensions ) ) {
				$this->allowedExtensions[] = $extension;
			}
		}

		/**
		 * Sets the list of disallowed protocols in S3 paths.
		 *
		 * This method updates the class's configuration with a new list of protocols that
		 * should not be allowed in S3 paths (e.g., 'http://', 'ftp://'). It's used to prevent
		 * security risks associated with unwanted protocols.
		 *
		 * @param array $disallowedProtocols An array of disallowed protocols.
		 */
		public function setDisallowedProtocols( array $disallowedProtocols ): void {
			if ( ! empty( $disallowedProtocols ) ) {
				$this->disallowedProtocols = $disallowedProtocols;
			}
		}

		/**
		 * Adds a single protocol to the list of disallowed protocols in S3 paths.
		 *
		 * This method updates the class's configuration by appending the specified protocol
		 * to the list of disallowed protocols if it is not already present. This is used to
		 * prevent security risks associated with unwanted protocols.
		 *
		 * @param string $protocol The protocol to add to the list of disallowed protocols.
		 */
		public function addDisallowedProtocol( string $protocol ): void {
			$protocol = trim( $protocol );
			if ( ! in_array( $protocol, $this->disallowedProtocols ) ) {
				$this->disallowedProtocols[] = $protocol;
			}
		}

		/**
		 * Parse the provided path to extract the bucket and object key.
		 *
		 * @param string $path      The S3 path.
		 * @param bool   $as_object Whether to return the result as an object. Default false.
		 *
		 * @return array|stdClass An associative array or stdClass object with 'bucket' and 'object_key' properties.
		 * @throws Exception If the path is empty, contains a disallowed protocol,
		 *                   has an invalid file extension, or does not contain a valid object key.
		 * @throws InvalidArgumentException If the bucket name is invalid.
		 */
		public function parsePath( string $path, bool $as_object = false ) {
			$path = trim( $path );

			if ( empty( $path ) ) {
				throw new Exception( "The provided path is empty." );
			}

			if ( $this->hasDisallowedProtocol( $path ) ) {
				throw new Exception( "The provided path contains a disallowed protocol." );
			}

			if ( ! $this->hasValidFileExtension( $path ) ) {
				throw new Exception( "The provided path has an invalid file extension." );
			}

			if ( $path[0] !== '/' ) {
				if ( empty( $this->defaultBucket ) ) {
					throw new Exception( "No bucket provided and no default bucket set." );
				}

				Validate::bucket( $this->defaultBucket );
				$result = [
					'bucket'     => $this->defaultBucket,
					'object_key' => Sanitize::objectKey( $path )
				];
			} else {
				$segments = explode( '/', ltrim( $path, '/' ) );
				if ( count( $segments ) < 2 ) {
					throw new Exception( "The path does not contain a valid object key." );
				}

				$bucket = array_shift( $segments );
				Validate::bucket( $bucket );
				$result = [
					'bucket'     => $bucket,
					'object_key' => Sanitize::objectKey( implode( '/', $segments ) )
				];
			}

			return $as_object ? (object) $result : $result;
		}

		/**
		 * Check if the provided path contains any disallowed protocols.
		 *
		 * @param string $path The path to check.
		 *
		 * @return bool True if the path contains a disallowed protocol, false otherwise.
		 */
		private function hasDisallowedProtocol( string $path ): bool {
			foreach ( $this->disallowedProtocols as $protocol ) {
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
		private function hasValidFileExtension( string $path ): bool {
			$extension = pathinfo( $path, PATHINFO_EXTENSION );

			// If allowedExtensions is set and not empty, check against it
			if ( ! empty( $this->allowedExtensions ) ) {
				return in_array( $extension, $this->allowedExtensions );
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
		public function isValidPath( string $path ): bool {
			// Check for disallowed protocol.
			if ( $this->hasDisallowedProtocol( $path ) ) {
				return false;
			}

			// Check for valid file extension.
			if ( ! $this->hasValidFileExtension( $path ) ) {
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
				if ( empty( $this->defaultBucket ) ) {
					return false;
				}

				// Validate the default bucket.
				try {
					Validate::bucket( $this->defaultBucket );
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