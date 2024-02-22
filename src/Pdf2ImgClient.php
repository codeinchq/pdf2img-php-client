<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace CodeInc\Pdf2ImgClient;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Message\MultipartStream\MultipartStreamBuilder;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class Pdf2ImgClient
{
    public function __construct(
        private readonly string $baseUrl,
        private ClientInterface|null $client = null,
        private StreamFactoryInterface|null $streamFactory = null,
        private RequestFactoryInterface|null $requestFactory = null,
    ) {
        $this->client ??= Psr18ClientDiscovery::find();
        $this->streamFactory ??= Psr17FactoryDiscovery::findStreamFactory();
        $this->requestFactory ??= Psr17FactoryDiscovery::findRequestFactory();
    }

    /**
     * @param StreamInterface|resource|string $stream
     * @param ConvertOptions $options
     * @return StreamInterface
     * @throws Exception
     */
    public function convert(mixed $stream, ConvertOptions $options = new ConvertOptions()): StreamInterface
    {
        $multipartStreamBuilder = $this->createStreamBuilder()
            ->addResource(
                'file',
                $stream,
                [
                    'filename' => 'file.pdf',
                    'headers'  => ['Content-Type' => 'application/pdf']
                ]
            )
            ->addResource('format', $options->format)
            ->addResource('density', (string)$options->density)
            ->addResource('height', (string)$options->height)
            ->addResource('width', (string)$options->width)
            ->addResource('background', $options->background)
            ->addResource('quality', (string)$options->quality)
            ->addResource('page', (string)$options->page);

        try {
            $response = $this->client->sendRequest(
                $this->requestFactory
                    ->createRequest("POST", $this->getConvertEndpointUri())
                    ->withHeader(
                        "Content-Type",
                        "multipart/form-data; boundary={$multipartStreamBuilder->getBoundary()}"
                    )
                    ->withBody($multipartStreamBuilder->build())
            );
        } catch (ClientExceptionInterface $e) {
            throw new Exception(
                message: "An error occurred while sending the request to the PDF2IMG API",
                code: Exception::ERROR_REQUEST,
                previous: $e
            );
        }

        if ($response->getStatusCode() !== 200) {
            throw new Exception(
                message: "The PDF2IMG API returned an error {$response->getStatusCode()}",
                code: Exception::ERROR_RESPONSE,
                previous: new Exception((string)$response->getBody())
            );
        }

        return $response->getBody();
    }

    /**
     * @param string $pdfPath
     * @param ConvertOptions $options
     * @return StreamInterface
     * @throws Exception
     */
    public function convertLocalFile(string $pdfPath, ConvertOptions $options = new ConvertOptions()): StreamInterface
    {
        $f = fopen($pdfPath, 'r');
        if ($f === false) {
            throw new Exception(
                message: "The file '$pdfPath' could not be opened",
                code: Exception::ERROR_LOCAL_FILE
            );
        }

        return $this->convert($f, $options);
    }

    private function createStreamBuilder(): MultipartStreamBuilder
    {
        return new MultipartStreamBuilder($this->streamFactory);
    }

    private function getConvertEndpointUri(): string
    {
        $url = $this->baseUrl;
        if (!str_ends_with($url, '/')) {
            $url .= '/';
        }
        return "{$url}convert";
    }
}
