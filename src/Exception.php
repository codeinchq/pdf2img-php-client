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

use Exception as BaseException;

class Exception extends BaseException
{
    public const ERROR_LOCAL_FILE = 100;
    public const ERROR_REQUEST = 200;
    public const ERROR_RESPONSE = 300;
}