<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-14
 *
 */


namespace unit\Service\JobValidator\PropertyValidator;

use Chapi\Service\JobValidator\PropertyValidator\JobName;

class JobNameTest extends AbstractValidatorTest
{
    public function testIsValidSuccess()
    {
        $propertyValidator = new JobName();

        $this->handleValidTestCase($propertyValidator, 'jobname', 'JobA');
        $this->handleValidTestCase($propertyValidator, 'jobname', 'job-name-ok_123');
    }

    public function testIsValidFailure()
    {
        $propertyValidator = new JobName();
        
        $this->handleInvalidTestCase($propertyValidator, 'jobname', 'job name');
        $this->handleInvalidTestCase($propertyValidator, 'jobname', '');
        $this->handleInvalidTestCase($propertyValidator, 'jobname', 'job^name');
        $this->handleInvalidTestCase($propertyValidator, 'jobname', 'job.name');
        $this->handleInvalidTestCase($propertyValidator, 'jobname', 'job:name');
        $this->handleInvalidTestCase($propertyValidator, 'jobname', false);
    }

    public function testGetLastErrorMessageMulti()
    {
        $propertyValidator = new JobName();
        $this->handleErrorMessageMultiTestCase($propertyValidator, 'jobname', 'job-name', 'job name');
    }
}
