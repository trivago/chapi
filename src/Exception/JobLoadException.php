<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-10-26
 */

namespace Chapi\Exception;

class JobLoadException extends \Exception
{
    const ERROR_CODE_NO_VALID_JSON = 1;
    const ERROR_CODE_DUPLICATE_JOB_ID = 2;
    const ERROR_CODE_UNKNOWN_ENTITY_TYPE = 3;
}
