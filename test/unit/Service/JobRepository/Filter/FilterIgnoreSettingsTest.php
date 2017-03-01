<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-10
 *
 */

namespace unit\Service\JobRepository\Filter;


use Chapi\Service\JobRepository\Filter\FilterIgnoreSettings;
use ChapiTest\src\TestTraits\AppEntityTrait;
use ChapiTest\src\TestTraits\JobEntityTrait;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class FilterIgnoreSettingsTest extends \PHPUnit_Framework_TestCase
{
    use JobEntityTrait;
    use AppEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oLogger;

    /** @var string */
    private $sRepositoryDir = 'FilterIgnoreSettingsTestDir';

    /** @var string  */
    private $sTempTestDir = '';

    /** @var  vfsStreamDirectory */
    private $oVfsRoot;

    public function setUp()
    {
        $this->oLogger = $this->prophesize('Psr\Log\LoggerInterface');

        $_aStructure = array(
            '.chapiignore' => "^test-123.*\n/test/123/.*"
        );

        $this->oVfsRoot = vfsStream::setup($this->sRepositoryDir, null, $_aStructure);

        // init and set up temp directory
        $_sTempTestDir = sys_get_temp_dir();
        $this->sTempTestDir = $_sTempTestDir . DIRECTORY_SEPARATOR . 'ChapiUnitTest';
        if (!is_dir($this->sTempTestDir))
        {
            mkdir($this->sTempTestDir, 0755);
        }
    }

    public function testIgnoreRules()
    {
        $_oFilter = new FilterIgnoreSettings(
            [vfsStream::url($this->sRepositoryDir)],
            $this->oLogger->reveal()
        );


        $_oEntity = $this->getValidScheduledJobEntity('test-234');
        $this->assertTrue($_oFilter->isInteresting($_oEntity));

        $_oEntity = $this->getValidScheduledJobEntity('test-123-xyz');
        $this->assertFalse($_oFilter->isInteresting($_oEntity));

        $_oEntity = $this->getValidMarathonAppEntity('/test/234/x');
        $this->assertTrue($_oFilter->isInteresting($_oEntity));

        $_oEntity = $this->getValidMarathonAppEntity('/test/123/xyz');
        $this->assertFalse($_oFilter->isInteresting($_oEntity));
    }

}