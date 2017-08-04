<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
 *
 */


namespace Chapi\Entity\Chronos;

use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;

class JobCollection extends \ArrayObject
{

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Construct a new array object
     * @link http://php.net/manual/en/arrayobject.construct.php
     *
     * @param ChronosJobEntity[] $aJobEntities The input parameter accepts an array of \Chapi\Entity\Chronos\JobEntity.
     * @throws \InvalidArgumentException
     */
    public function __construct(array $aJobEntities)
    {
        $_aJobs = [];

        if (count($aJobEntities) > 0) {
            $_mCheck = current($aJobEntities);
            if (!$_mCheck instanceof JobEntityInterface) {
                throw new \InvalidArgumentException('array has to contain JobEntity objects');
            }

            foreach ($aJobEntities as $_oJobEntity) {
                $_aJobs[$_oJobEntity->getKey()] = $_oJobEntity;
            }
        }
        parent::__construct($_aJobs);
    }
}
