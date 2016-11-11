<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 *
 */


namespace unit\Service\JobValidator\PropertyValidator;


use Chapi\Service\JobValidator\PropertyValidator\IsArray;
use ChapiTest\src\TestTraits\JobEntityTrait;

class IsArrayTest extends AbstractValidatorTest
{
    use JobEntityTrait;
    
    public function testIsValidSuccess()
    {
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oIsArray = new IsArray();
        
        $_oJobEntity->parents = [1, 2, 3];
        $this->assertTrue($_oIsArray->isValid('parents', $_oJobEntity));
        $this->assertEmpty($_oIsArray->getLastErrorMessage());
    }

    public function testIsValidFailure()
    {
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oIsArray = new IsArray();
        
        $_oJobEntity->parents = 'foo';
        $this->assertFalse($_oIsArray->isValid('parents', $_oJobEntity));
        $this->assertContains('parents', $_oIsArray->getLastErrorMessage());

        $_oJobEntity->arguments = new \stdClass();
        $this->assertFalse($_oIsArray->isValid('arguments', $_oJobEntity));
        $this->assertContains('arguments', $_oIsArray->getLastErrorMessage());

        $_oJobEntity->parents = 1;
        $this->assertFalse($_oIsArray->isValid('parents', $_oJobEntity));

        $_oJobEntity->parents = false;
        $this->assertFalse($_oIsArray->isValid('parents', $_oJobEntity));
    }

    public function testGetLastErrorMessageMulti()
    {
        $_oIsArray = new IsArray();
        $this->handleTestGetLastErrorMessageMulti($_oIsArray, 'parents', [1, 2, 3], 1);
    }
}