# Path Resolver for S3-Compatible Storage Systems

The `Path_Resolver` class is a meticulously crafted tool designed to parse and validate paths within S3-compatible object storage systems. With its innate capability to understand and process various path formats, it offers a flexible yet precise mechanism for your applications, making path resolutions for S3 buckets a breeze.

**Key Features:**

* **Intuitive Path Parsing:** Extract crucial path components such as the bucket and object key seamlessly. The class understands a range of formats, for example:
    * `/my-bucket/my-file.zip`: Resolves to a bucket named 'my-bucket' and an object key 'my-file.zip'.
    * `my-file.zip`: With a default bucket set, the file resolves to the default bucket.
* **Protocol Restrictions:** Safeguard your paths from potentially unsafe protocols. The default configuration blocks HTTP(s), EDD-DBFS, and FTP, but can be tailored to your specific requirements.
* **File Extension Validation:** Ensure paths conform to specific file types through a customizable list of allowed file extensions.
* **Bucket Name Verification:** Adhere to S3's stringent naming conventions and ensure every bucket name is valid.
* **Object Key Sanitization:** Modify object keys automatically to meet S3's naming criteria, ensuring a consistent naming pattern.
* **Dynamic Configuration with Defaults:** Configure the class dynamically through its constructor. Set a default bucket, which acts as a fallback, ensuring that even standalone filenames like `my-file.zip` get associated with the default bucket.
* **Error Handling:** Comprehensive, intuitive error messages provide guidance in addressing common path-related pitfalls.
* **S3 Path Verification:** Instantly check the validity of a provided path for S3 compatibility, ensuring paths meet essential criteria.

Integrating the `Path_Resolver` class into your system eliminates the guesswork in path resolutions for S3-compatible storage. Empower your applications with smart path parsing, validation, and much more, ensuring you always get the most out of your object storage systems.

## Installation and set up

The extension in question needs to have a `composer.json` file, specifically with the following:

```json 
{
  "require": {
    "arraypress/s3-path-resolver": "*"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/arraypress/s3-path-resolver"
    }
  ]
}
```

Once set up, run `composer install --no-dev`. This should create a new `vendors/` folder
with `arraypress/s3-path-resolver/` inside.

## Leveraging the Object Storage Provider Library

The `Path_Resolver` class streamlines the integration with a range of popular object storage providers, including CloudFlare R2 and more. With this tool, you can effortlessly fetch correct endpoints based on provider and region, ensuring accurate and secure connections to your storage solutions. Below is a step-by-step guide to unlock its potential:

## Leveraging the Path Resolver for Object Storage Solutions

The `Path_Resolver` class is intricately designed to parse paths for S3-compatible storage systems. Whether you're working with AWS S3, CloudFlare R2, or any other compatible storage, this class ensures you address and manage your paths efficiently. Dive deep into its potential with these examples:

### Initialization

To initialize without any defaults:

```php
$resolver = new Path_Resolver();
```

Setting a default bucket during initialization:

```php
$resolver = new Path_Resolver( 'default-bucket-name' );
```

### Path Parsing

Parsing a straightforward path with a bucket and object:

```php
$result = $resolver->parse_path( '/my-bucket/my-file.zip' );
print_r( $result ); // Outputs: ['bucket' => 'my-bucket', 'object' => 'my-file.zip']
```

Parsing with just an object and using the default bucket:

```php
$result = $resolver->parse_path( 'my-file.zip' );
print_r( $result ); // Outputs (assuming default bucket is 'default-bucket-name'): ['bucket' => 'default-bucket-name', 'object' => 'my-file.zip']
```

### Checking for Valid S3 Paths

Validating a path:

```php
$is_valid = $resolver->is_valid_path( '/my-bucket/my-file.zip' );
echo $is_valid ? "Valid S3 Path" : "Invalid S3 Path";
```

### Working with Allowed and Disallowed Protocols

This class has built-in functionality to restrict or allow specific protocols. By default, it restricts 'https://', 'http://', 'edd-dbfs', and 'ftp://'. However, you can override this during initialization:

```php
$custom_disallowed_protocols = ['custom-protocol://'];
$resolver = new Path_Resolver( 'default-bucket-name', [], $custom_disallowed_protocols );
```

If a path containing one of these protocols is passed to the parser, it will throw an exception.

### Managing Custom Allowed File Extensions

By default, the class checks for the existence of any file extension in the path. However, you can restrict the allowed file extensions during initialization. Here's how:

```php
$allowed_extensions = [ 'zip', 'jpg', 'pdf' ];
$resolver = new Path_Resolver( 'default-bucket-name', $allowed_extensions );
```

Now, the class will only recognize paths with '.zip', '.jpg', or '.pdf' as valid:

```php
$is_valid = $resolver->is_valid_path( 'my-file.zip' );  // True
$is_valid = $resolver->is_valid_path( 'my-file.exe' );  // False
echo $is_valid ? "Valid S3 Path" : "Invalid S3 Path";
```

If a disallowed extension is passed to the parser, an exception will be thrown.

### Utilizing the Standalone Helper Functions

For users who prefer working with functions rather than directly with the `Path_Resolver` class, two standalone helper functions are available: `parse_path` and `is_s3_path`. These functions internally utilize the class but offer a more straightforward and flexible interface.

#### The `parse_path` Function

This function aims to parse the provided path and extract the bucket and object key. It also handles exceptions gracefully with an optional callback.

```php
$result = parse_path( 'my-bucket/my-file.zip', 'default-bucket-name', ['zip', 'jpg'], ['ftp://'] ) ;
// Result: ['bucket' => 'my-bucket', 'object' => 'my-file.zip']
```

#### The `is_s3_path` Function

This function determines if the given path is a valid S3 path based on allowed extensions and disallowed protocols.

```php
$isValid = is_valid_path( 'my-file.zip', 'default-bucket', ['zip', 'jpg'], ['ftp://'] ) ;
// Result: true
```

Both functions accept an optional error callback, allowing users to define custom error handling routines. For example, if you're using Easy Digital Downloads and want to log exceptions with `edd_debug_log_exception`:

```php
$result = parse_path( 'invalid/path', 'default-bucket', ['zip'], [], 'edd_debug_log_exception' );
// If there's an exception, it will be logged via edd_debug_log_exception
```

## Contributions

Contributions to this library are highly appreciated. Raise issues on GitHub or submit pull requests for bug
fixes or new features. Share feedback and suggestions for improvements.

## License

This library is licensed under
the [GNU General Public License v2.0](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html).