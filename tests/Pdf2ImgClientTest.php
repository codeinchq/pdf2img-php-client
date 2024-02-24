<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace CodeInc\Pdf2ImgClient\Tests;

use CodeInc\Pdf2ImgClient\ConvertOptions;
use CodeInc\Pdf2ImgClient\Exception;
use CodeInc\Pdf2ImgClient\Pdf2ImgClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * Class Pdf2ImgClientTest
 *
 * @see Pdf2ImgClient
 * @package CodeInc\Pdf2ImgClient\Tests
 * @license https://opensource.org/licenses/MIT MIT
 * @author Joan Fabrégat <joan@codeinc.co>
 */
final class Pdf2ImgClientTest extends TestCase
{
    private const DEFAULT_PDF2IMG_BASE_URL = 'http://localhost:3000';
    private const TEST_PDF_PATH = __DIR__.'/assets/file.pdf';
    private const TEST_PDF_RESULT_IMG = __DIR__.'/assets/file.jpg';

    /**
     * @throws Exception
     */
    public function testConvert(): void
    {
        $client = $this->getNewClient();
        $stream = $client->convert($client->createStreamFromFile(self::TEST_PDF_PATH));
        $this->assertInstanceOf(StreamInterface::class, $stream, "The stream is not valid");

        $imageContent = (string)$stream;
        $this->assertStringContainsString('WEBP', $imageContent, "The image is not a WEBP");
    }

    /**
     * @throws Exception
     */
    public function testConvertWithOptions(): void
    {
        $this->assertIsWritable(dirname(self::TEST_PDF_RESULT_IMG), "The result file is not writable");

        $client = $this->getNewClient();
        $stream = $client->convert(
            $client->createStreamFromFile(self::TEST_PDF_PATH),
            new ConvertOptions(
                format: 'jpeg',
                page: 1,
                density: 72,
                height: 300,
                width: 300,
                background: 'white',
                quality: 80,
            )
        );
        $this->assertInstanceOf(StreamInterface::class, $stream, "The stream is not valid");

        $client->saveStreamToFile($stream, self::TEST_PDF_RESULT_IMG);
        $this->assertFileExists(self::TEST_PDF_RESULT_IMG, "The result file does not exist");
        $this->assertStringContainsString(
            'JFIF',
            file_get_contents(self::TEST_PDF_RESULT_IMG),
            "The image is not valid"
        );

        unlink(self::TEST_PDF_RESULT_IMG);
    }

    private function getNewClient(): Pdf2ImgClient
    {
        $apiBaseUrl = self::DEFAULT_PDF2IMG_BASE_URL;
        if (defined('PDF2IMG_BASE_URL')) {
            $apiBaseUrl = constant('PDF2IMG_BASE_URL');
        }
        return new Pdf2ImgClient($apiBaseUrl);
    }

    private function assertIsValidResponseStream(mixed $stream): void
    {
        $this->assertInstanceOf(StreamInterface::class, $stream, "The stream is not valid");
        $image = (string)$stream;
        $this->assertNotEmpty($image, "The stream is empty");
        $this->assertEquals(md5_file(self::TEST_PDF_RESULT_IMG), md5($image), "The image is not valid");
    }
}