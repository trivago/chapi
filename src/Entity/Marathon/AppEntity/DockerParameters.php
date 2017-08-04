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

class DockerParameters
{
    const DIC = self::class;

    public $key = '';

    public $value = '';

    public function __construct($data = [])
    {
        MarathonEntityUtils::setAllPossibleProperties($data, $this);
    }
}
