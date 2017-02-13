<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 10/02/17
 */

namespace unit\Entity\Marathon\AppEntity;


use Chapi\Entity\Marathon\AppEntity\DockerParameters;

class DockerParametersTest extends \PHPUnit_Framework_TestCase
{
    public function testAllKeysAreCorrect() {
        $aKeys = ["key", "value"];

        $oDockerParameters = new DockerParameters();

        foreach ($aKeys as $sProperty) {
            $this->assertObjectHasAttribute($sProperty, $oDockerParameters);
        }
    }

    public function testDockerParameterIsSetCorrectly() {
        $aData = ["key" => "someKey", "value" => "somevalue"];

        $oDockerParameters = new DockerParameters($aData);

        $this->assertEquals("someKey", $oDockerParameters->key);
        $this->assertEquals("somevalue", $oDockerParameters->value);

    }

}
