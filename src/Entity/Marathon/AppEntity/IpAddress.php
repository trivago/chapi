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

class IpAddress
{
    const DIC = self::class;

    public $groups = [];

    public $labels = null;

    public $networkName = '';

    public function __construct($data = [])
    {
        MarathonEntityUtils::setAllPossibleProperties(
            $data,
            $this,
            [
                'groups' => MarathonEntityUtils::convArray(),
                'labels' => MarathonEntityUtils::convObject()
            ]
        );
    }
}
