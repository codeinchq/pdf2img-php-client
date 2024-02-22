# pdf2img PHP client

This repository contains a PHP 8.2+ library for converting PDF files to images using the [pdf2img](https://github.com/codeinchq/pdf2img) service.

## Installation

The recommended way to install the library is through [Composer](http://getcomposer.org):

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
    # convert 
    $client = new Pdf2ImgClient($apiBaseUri);
    $image = $client->convertLocalFile($localPdfPath);
    
    # display the image 
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
    # convert 
    $client = new Pdf2ImgClient($apiBaseUri);
    $image = $client->convertLocalFile($localPdfPath, $convertOption);
    
    # saves the image to a file 
    $f = fopen($destinationPath, 'w');
    stream_copy_to_stream($image->detach(), $f);
    fclose($f);
}
catch (Exception $e) {
    // handle exception
}
```

## License

The library is published under the MIT license (see [`LICENSE`](LICENSE) file).