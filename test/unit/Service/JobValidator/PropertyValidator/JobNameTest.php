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
        $_oPropertyValidator = new JobName();

        $this->handleValidTestCase($_oPropertyValidator, 'jobname', 'JobA');
        $this->handleValidTestCase($_oPropertyValidator, 'jobname', 'job-name-ok_123');
    }

    public function testIsValidFailure()
    {
        $_oPropertyValidator = new JobName();
        
        $this->handleInvalidTestCase($_oPropertyValidator, 'jobname', 'job name');
        $this->handleInvalidTestCase($_oPropertyValidator, 'jobname', '');
        $this->handleInvalidTestCase($_oPropertyValidator, 'jobname', 'job^name');
        $this->handleInvalidTestCase($_oPropertyValidator, 'jobname', false);
    }

    public function testGetLastErrorMessageMulti()
    {
        $_oPropertyValidator = new JobName();
        $this->handleErrorMessageMultiTestCase($_oPropertyValidator, 'jobname', 'job-name', 'job name');
    }
}