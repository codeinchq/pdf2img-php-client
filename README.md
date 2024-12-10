# Pdf2Img PHP client

[![Code Inc.](https://img.shields.io/badge/Code%20Inc.-Pdf2Img-blue)](https://github.com/codeinchq/pdf2img)
![Tests](https://github.com/codeinchq/pdf2img-php-client/actions/workflows/phpunit.yml/badge.svg)

This repository contains a PHP 8.2+ library for converting PDF files to images using the [pdf2img](https://github.com/codeinchq/pdf2img) service.

## Installation

The library is available on [Packagist](https://packagist.org/packages/codeinc/pdf2img-client). The recommended way to install it is via Composer:

```bash
composer require codeinc/pdf2img-client
```

## Usage

This client requires a running instance of the [pdf2img](https://github.com/codeinchq/pdf2img) service. The service can be run locally [using Docker](https://hub.docker.com/r/codeinchq/pdf2img) or deployed to a server.

Base example: 
```php
use CodeInc\Pdf2ImgClient\Pdf2ImgClient;
use CodeInc\Pdf2ImgClient\Exception;

$apiBaseUri = 'http://localhost:3000/';
$localPdfPath = '/path/to/local/file.pdf';

try {
    $client = new Pdf2ImgClient($apiBaseUri);

    // convert 
    $image = $client->convert(
        $client->createStreamFromFile($localPdfPath)
    );
    
    // display the image 
    header('Content-Type: image/webp');
    echo (string)$image;
}
catch (Exception $e) {
    // handle exception
}
```

With options:
```php
use CodeInc\Pdf2ImgClient\Pdf2ImgClient;
use CodeInc\Pdf2ImgClient\ConvertOptions;

$apiBaseUri = 'http://localhost:3000/';
$localPdfPath = '/path/to/local/file.pdf';
$destinationPath = '/path/to/destination/file.jpg';
$convertOption = new ConvertOptions(
    format: 'jpg',
    page: 3,
    density: 300,
    height: 800,
    width: 800,
    background: 'red',
    quality: 90,
);

try {
    $client = new Pdf2ImgClient($apiBaseUri);

    // convert 
    $image = $client->convertLocalFile(
        $client->createStreamFromFile($localPdfPath),
        $convertOption
     );
    
    // saves the image to a file 
    $client->saveStreamToFile($image, $destinationPath);
}
catch (Exception $e) {
    // handle exception
}
```

## License

The library is published under the MIT license (see [`LICENSE`](LICENSE) file).