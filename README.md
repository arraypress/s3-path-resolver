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

### Understanding Path Resolution in PathResolver

The `PathResolver` class processes S3 paths differently based on the presence of a leading slash (`/`) and whether a default bucket is set. Here's how it works:

#### Paths Starting with `/`

When a path starts with a `/`, the segment immediately following the `/` is treated as the bucket name, and the rest of the path as the object key.

```php
// No default bucket set
$resolver = new ArrayPress\S3\PathResolver();
$pathInfo = $resolver->parsePath('/mybucket/myfile.zip');
```

- **Bucket**: `mybucket`
- **Object Key**: `myfile.zip`

This approach allows explicit specification of the bucket in the path. If no default bucket is set and the path does not start with `/`, the operation will fail because the resolver cannot determine the bucket.

#### Paths Without Leading `/` and No Default Bucket

If a path does not start with `/` and no default bucket is set, the `PathResolver` cannot resolve the bucket and will result in a failure:

```php
$resolver = new ArrayPress\S3\PathResolver();
// Attempting to parse a path without a leading '/' and no default bucket set.
// This will fail because the resolver cannot determine the bucket.
$pathInfo = $resolver->parsePath('myfile.zip');
```

#### Setting a Default Bucket

Setting a default bucket allows the `PathResolver` to resolve paths that do not explicitly specify a bucket:

```php
$resolver = new ArrayPress\S3\PathResolver('default-bucket');
// Since a default bucket is set, this path is resolved successfully.
$pathInfo = $resolver->parsePath('myfile.zip');
```

- **Bucket**: `default-bucket`
- **Object Key**: `myfile.zip`

In this scenario, because a default bucket is provided, paths without a leading `/` are assumed to belong to the default bucket.

#### Conclusion

Understanding how `PathResolver` interprets paths based on the presence of a leading slash and a default bucket setting is crucial for correctly managing S3 object locations. Explicitly starting your path with `/` allows you to specify the bucket directly within the path, offering flexibility in scenarios where objects may reside across multiple buckets. Conversely, setting a default bucket simplifies path handling for applications primarily interacting with a single bucket, reducing the need to repeatedly specify the bucket name.

### Special Case: Default Bucket and Path Without Bucket Name

If you have a default bucket set but also pass a path without specifying a bucket (e.g., `files/mydog.zip`), the `PathResolver` will apply the default bucket to the path:

```php
$resolver->setDefaultBucket('my-default-bucket');
$pathInfo = $resolver->parsePath('files/mydog.zip');
```

- **Bucket**: `my-default-bucket`
- **Object Key**: `files/mydog.zip`

This ensures that even when a bucket name is omitted from the path, the file can still be resolved correctly using the default bucket.

## Easy Digital Downloads and WooCommerce Helper Functions

**Easy Digital Downloads S3 File Path Check:**

```php
use function ArrayPress\S3\EDD\isS3Path;

// Check if an EDD download file is stored on S3.
$isS3File = isS3Path( $downloadId, $fileId, 'default-bucket', [ 'zip' ], [ 'http://', 'https://' ], function ($e) {
    echo "Error: " . $e->getMessage();
} );

echo $isS3File ? "File is on S3." : "File is not on S3.";
```

**WooCommerce S3 File Path Check:**

```php
use function ArrayPress\S3\WC\isS3Path;

// Verify if a WooCommerce product file is hosted on S3.
$isS3ProductFile = isS3Path( $productId, $downloadId, 'default-bucket', [ 'pdf' ], [ 'http://', 'https://' ], function ($e) {
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