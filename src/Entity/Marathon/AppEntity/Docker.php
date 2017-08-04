<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2016-10-16
 *
 */
namespace Chapi\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\DockerParameters;
use Chapi\Entity\Marathon\AppEntity\DockerPortMapping;
use Chapi\Entity\Marathon\MarathonEntityUtils;

class Docker
{
    const DIC = self::class;

    public $image = '';

    public $network = '';

    /**
     * @var DockerPortMapping[]
     */
    public $portMappings = [];


    public $privileged = false;

    public $forcePullImage = false;

    /**
     * @var DockerParameters
     */
    public $parameters = [];

    public function __construct($data = [])
    {
        MarathonEntityUtils::setAllPossibleProperties($data, $this);

        if (isset($data['portMappings'])) {
            foreach ($data['portMappings'] as $portMapping) {
                $this->portMappings[] = new DockerPortMapping((array) $portMapping);
            }
        }

        if (isset($data['parameters'])) {
            foreach ($data['parameters'] as $parameter) {
                $this->parameters[] = new DockerParameters((array) $parameter);
            }
        }
    }
}
