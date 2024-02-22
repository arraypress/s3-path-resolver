# S3 Path Resolver Library

The S3 Path Resolver Library is a versatile toolkit tailored for developers who manage Amazon S3 and S3-compatible storage paths. It streamlines the process of parsing, validating, and sanitizing S3 paths, aligning with AWS standards and improving the robustness of cloud storage operations.

**Key Features:**

- **Dynamic Path Parsing:** Extracts bucket and object keys from S3 paths, handling default buckets and custom scenarios.
- **Disallowed Protocol Handling:** Prevents errors related to unsupported protocols within S3 paths.
- **File Extension Validation:** Ensures that object keys have valid file extensions, according to customizable lists.
- **Flexibility in Configuration:** Allows dynamic setting of default buckets, allowed file extensions, and disallowed protocols.
- **Enhanced Security:** Adds an extra layer of validation to S3 operations, ensuring paths are safe and formatted correctly.

Incorporate the S3 Path Resolver into your projects to navigate S3 paths with confidence and precision.

## Minimum Requirements

- **PHP:** 7.4 or later

## Installation

To integrate the S3 Path Resolver into your project, use Composer:

```bash
composer require arraypress/s3-path-resolver
```

## PathResolver Class Examples

**Instantiate the PathResolver:**

```php
use ArrayPress\S3\PathResolver;

// Create a resolver instance with a default bucket and allowed file extensions.
$resolver = new PathResolver( 'default-bucket', [ 'zip', 'jpg' ], [ 'http://' ] );
```

**Parse an S3 Path:**

```php
try {
    // Parse an S3 path to get the bucket and object key.
    $pathInfo = $resolver->parsePath( '/my-bucket/my-object.zip' );
    echo "Bucket: {$pathInfo->bucket}, Object Key: {$pathInfo->object_key}";
} catch ( Exception $e ) {
    // Handle exceptions, such as invalid paths or protocols.
    echo "Error: " . $e->getMessage();
}
```

**Add Allowed Extensions and Disallowed Protocols:**

```php
// Add additional allowed file extensions.
$resolver->addAllowedExtension( 'pdf' );
$resolver->addAllowedExtension( 'docx' );

// Append a new disallowed protocol.
$resolver->addDisallowedProtocol( 'file://' );
```

**Validate an S3 Path:**

```php
// Check if the S3 path is valid.
if ($resolver->isValidPath( '/my-bucket/my-object.zip' ) ) {
    echo "The path is valid.";
} else {
    echo "The path is invalid.";
}
```

## Easy Digital Downloads and WooCommerce Helper Functions

**Easy Digital Downloads S3 File Check:**

```php
use ArrayPress\S3\EDD;

// Check if an EDD download file is stored on S3.
$isS3File = EDD\is_s3_path( $downloadId, $fileId, 'default-bucket', [ 'zip' ], [ 'http://', 'https://' ], function ($e) {
    echo "Error: " . $e->getMessage();
} );

echo $isS3File ? "File is on S3." : "File is not on S3.";
```

**WooCommerce S3 File Check:**

```php
use ArrayPress\S3\WC;

// Verify if a WooCommerce product file is hosted on S3.
$isS3ProductFile = WC\is_s3_path( $productId, $downloadId, 'default-bucket', [ 'pdf' ], [ 'http://', 'https://' ], function ($e) {
    echo "Error: " . $e->getMessage();
} );

echo $isS3ProductFile ? "Product file is on S3." : "Product file is not on S3.";
```

## Contributions

Contributions to this library are highly appreciated. Raise issues on GitHub or submit pull requests for bug
fixes or new features. Share feedback and suggestions for improvements.

## License: GPLv2 or later

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.