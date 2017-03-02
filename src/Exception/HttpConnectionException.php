<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-01
 *
 */


namespace Chapi\Exception;


class HttpConnectionException extends \Exception
{
    const ERROR_CODE_REQUEST_EXCEPTION = 1;
    const ERROR_CODE_CONNECT_EXCEPTION = 2;
    const ERROR_CODE_TOO_MANY_REDIRECT_EXCEPTION = 3;
    const ERROR_CODE_UNKNOWN = 0;
}