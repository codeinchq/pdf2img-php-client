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

/**
 * pdf2img convert options.
 *
 * @see https://github.com/codeinchq/pdf2img?tab=readme-ov-file#usage
 */
final readonly class ConvertOptions
{
    public function __construct(
        public string $format = 'webp',
        public int $page = 1,
        public int $density = 300,
        public int $height = 1000,
        public int $width = 1000,
        public string $background = 'white',
        public int $quality = 80,
    ) {
    }
}