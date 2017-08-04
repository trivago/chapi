<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2016-10-16
 *
 */
namespace Chapi\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\MarathonEntityUtils;

class ContainerVolume
{
    const DIC = self::class;

    public $containerPath = '';

    public $hostPath = '';

    public $mode = '';

    public function __construct($aData = [])
    {
        MarathonEntityUtils::setAllPossibleProperties($aData, $this);
    }
}
