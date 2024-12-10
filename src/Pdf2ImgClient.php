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

/**
 * Client to interact with the Pdf2Img API.
 *
 * @package CodeInc\Pdf2ImgClien
 * @link    https://github.com/codeinchq/pdf2img-php-client
 * @link    https://github.com/codeinchq/pdf2img
 * @license https://opensource.org/licenses/MIT MIT
 * @author  Joan Fabrégat <joan@codeinc.co>
 */
readonly class Pdf2ImgClient
{
    public ClientInterface $client;
    public StreamFactoryInterface $streamFactory;
    public RequestFactoryInterface $requestFactory;

    public function __construct(
        private string $baseUrl,
        ClientInterface|null $client = null,
        StreamFactoryInterface|null $streamFactory = null,
        RequestFactoryInterface|null $requestFactory = null,
    ) {
        $this->client = $client ?? Psr18ClientDiscovery::find();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
    }

    /**
     * @param StreamInterface|resource|string $stream
     * @param ConvertOptions $options
     * @return StreamInterface
     * @throws Exception
     */
    public function convert(mixed $stream, ConvertOptions $options = new ConvertOptions()): StreamInterface
    {
        $multipartStreamBuilder = (new MultipartStreamBuilder($this->streamFactory))
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
                    ->createRequest("POST", $this->getEndpointUri('/convert'))
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
     * Returns an endpoint URI.
     *
     * @param string $endpoint
     * @return string
     */
    private function getEndpointUri(string $endpoint): string
    {
        $url = $this->baseUrl;
        if (str_ends_with($url, '/')) {
            $url = substr($url, 0, -1);
        }
        if (str_starts_with($endpoint, '/')) {
            $endpoint = substr($endpoint, 1);
        }

        return "$url/$endpoint";
    }

    /**
     * Health check to verify the service is running.
     *
     * @return bool Health check response, expected to be "ok".
     */
    public function checkServiceHealth(): bool
    {
        try {
            $response = $this->client->sendRequest(
                $this->requestFactory->createRequest(
                    "GET",
                    $this->getEndpointUri("/health")
                )
            );

            // The response status code should be 200
            if ($response->getStatusCode() !== 200) {
                return false;
            }

            // The response body should be {"status":"up"}
            $responseBody = json_decode((string)$response->getBody(), true);
            return isset($responseBody['status']) && $responseBody['status'] === 'up';
        } catch (ClientExceptionInterface) {
            return false;
        }
    }
}
