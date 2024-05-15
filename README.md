S3 Path Resolver Library
========================

The S3 Path Resolver Library is a versatile toolkit tailored for developers who manage Amazon S3 and S3-compatible storage paths. It streamlines the process of parsing, validating, and sanitizing S3 paths, aligning with AWS standards and improving the robustness of cloud storage operations.

Key Features:
-------------

*   **Dynamic Path Parsing:** Extracts bucket and object keys from S3 paths, handling default buckets and custom scenarios.
*   **Disallowed Protocol Handling:** Prevents errors related to unsupported protocols within S3 paths.
*   **File Extension Validation:** Ensures that object keys have valid file extensions, according to customizable lists.
*   **Flexibility in Configuration:** Allows dynamic setting of default buckets, allowed file extensions, and disallowed protocols.
*   **Enhanced Security:** Adds an extra layer of validation to S3 operations, ensuring paths are safe and formatted correctly.

Incorporate the S3 Path Resolver into your projects to navigate S3 paths with confidence and precision.

Minimum Requirements
--------------------

*   **PHP:** 7.4 or later

Installation
------------

To integrate the S3 Path Resolver into your project, use Composer:

```bash
composer require arraypress/s3-path-resolver
```

```php
// Require the Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';

// Use the class and functions from the ArrayPress\S3 namespace.
use ArrayPress\S3\PathResolver;
use function ArrayPress\S3\EDD\is_s3_path;
use function ArrayPress\S3\WC\is_s3_path;
```

### Understanding Path Resolution in PathResolver

The `PathResolver` class processes S3 paths differently based on the presence of a leading slash (`/`) and whether a default bucket is set. Here's how it works:

#### Paths Starting with `/`

When a path starts with a `/`, the segment immediately following the `/` is treated as the bucket name, and the rest of the path as the object key.

```php
// No default bucket set
$resolver = new PathResolver();
$pathInfo = $resolver->parsePath( '/mybucket/myfile.zip' );

echo "Bucket: " . $pathInfo->bucket; // Outputs: mybucket
echo "Object Key: " . $pathInfo->objectKey; // Outputs: myfile.zip`
```

#### Paths Without Leading `/` and No Default Bucket

If a path does not start with `/` and no default bucket is set, the `PathResolver` cannot resolve the bucket and will result in a failure:

```php
$resolver = new PathResolver();
try {
    $pathInfo = $resolver->parsePath( 'myfile.zip' );
} catch (Exception $e) {
    echo "Error: " . $e->getMessage(); // Outputs: The provided path does not contain a valid bucket and object key.
}
```

#### Setting a Default Bucket

Setting a default bucket allows the `PathResolver` to resolve paths that do not explicitly specify a bucket:

```php
$resolver = new PathResolver('default-bucket');
$pathInfo = $resolver->parsePath('myfile.zip');

echo "Bucket: " . $pathInfo->bucket; // Outputs: default-bucket
echo "Object Key: " . $pathInfo->objectKey; // Outputs: myfile.zip
```

#### Special Case: Default Bucket and Path Without Bucket Name

If you have a default bucket set but also pass a path without specifying a bucket (e.g., `files/mydog.zip`), the `PathResolver` will apply the default bucket to the path:

```php
$resolver = new PathResolver('my-default-bucket');
$pathInfo = $resolver->parsePath('files/mydog.zip');

echo "Bucket: " . $pathInfo->bucket; // Outputs: my-default-bucket
echo "Object Key: " . $pathInfo->objectKey; // Outputs: files/mydog.zip
```

### Examples

#### 1\. Valid path with bucket and object key:

```php
$resolver = new PathResolver();
$pathInfo = $resolver->parsePath('/mybucket/myfile.zip');

echo "Bucket: " . $pathInfo->bucket; // Outputs: mybucket
echo "Object Key: " . $pathInfo->objectKey; // Outputs: myfile.zip
```


#### 2\. Path without leading slash and with default bucket:

```php
$resolver = new PathResolver('my-default-bucket');
$pathInfo = $resolver->parsePath('myfile.zip');

echo "Bucket: " . $pathInfo->bucket; // Outputs: my-default-bucket
echo "Object Key: " . $pathInfo->objectKey; // Outputs: myfile.zip
```

#### 3\. Invalid protocol in path:

```php
$resolver = new PathResolver();
try {
    $pathInfo = $resolver->parsePath('ftp://mybucket/myfile.zip');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage(); // Outputs: The provided path contains a disallowed protocol.
}
```

#### 4\. Invalid file extension:

```php
$resolver = new PathResolver();
$resolver->setAllowedExtensions( ['zip', 'rar'] );
try {
    $pathInfo = $resolver->parsePath('/mybucket/myfile.exe');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage(); // Outputs: The provided path has an invalid file extension.
}
```

#### 5\. Path with nested folders:

```php
$resolver = new PathResolver();
$pathInfo = $resolver->parsePath('/mybucket/folder1/folder2/myfile.zip');

echo "Bucket: " . $pathInfo->bucket; // Outputs: mybucket
echo "Object Key: " . $pathInfo->objectKey; // Outputs: folder1/folder2/myfile.zip
```

#### 6\. Path without leading slash and no default bucket:

```php
$resolver = new PathResolver();
try {
    $pathInfo = $resolver->parsePath('myfile.zip');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage(); // Outputs: The provided path does not contain a valid bucket and object key.
}
```

### Easy Digital Downloads and WooCommerce Helper Functions

#### Easy Digital Downloads S3 File Path Check:

```php
// Check if an EDD download file is stored on S3.
$is_s3_file = is_s3_path($download_id, $file_id, 'default-bucket', ['zip'], ['http://', 'https://'], function ($e) {
    echo "Error: " . $e->getMessage();
});

echo $is_s3_file ? "File is on S3." : "File is not on S3.";
```


#### WooCommerce S3 File Path Check:

```php
// Verify if a WooCommerce product file is hosted on S3.
$is_s3_file = is_s3_path($product_id, $download_id, 'default-bucket', ['pdf'], ['http://', 'https://'], function ($e) {
    echo "Error: " . $e->getMessage();
});

echo $is_s3_file ? "Product file is on S3." : "Product file is not on S3.";
```

### Contributions

Contributions to this library are highly appreciated. Raise issues on GitHub or submit pull requests for bug fixes or new features. Share feedback and suggestions for improvements.

### License: GPLv2 or later

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.