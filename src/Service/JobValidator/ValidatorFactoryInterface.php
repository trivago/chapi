<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 *
 */


namespace Chapi\Service\JobValidator;

interface ValidatorFactoryInterface
{
    const NOT_EMPTY_VALIDATOR = 1;
    const NAME_VALIDATOR = 2;
    const EPSILON_VALIDATOR = 4;
    const BOOLEAN_VALIDATOR = 8;
    const SCHEDULE_VALIDATOR = 16;
    const ARRAY_VALIDATOR = 32;
    const RETRY_VALIDATOR = 64;
    const CONSTRAINTS_VALIDATOR = 128;
    const CONTAINER_VALIDATOR = 256;
    const COMMAND_VALIDATOR = 512;

    /**
     * @param $validator
     * @return PropertyValidatorInterface
     */
    public function getValidator($validator);
}
