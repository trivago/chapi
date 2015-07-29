<?php
/**
 * @package: orchestra-
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
 *
 * @link:    http://
 */


namespace Chapi\Entity\Chronos;


class JobCollection extends \ArrayObject
{

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Construct a new array object
     * @link http://php.net/manual/en/arrayobject.construct.php
     *
     * @param \Chapi\Entity\Chronos\JobEntity[] $aJobEntities The input parameter accepts an array of \Chapi\Entity\Chronos\JobEntity.
     * @throws \InvalidArgumentException
     */
    public function __construct(array $aJobEntities)
    {
        if (count($aJobEntities) > 0)
        {
            $_mCheck = current($aJobEntities);
            if (!$_mCheck instanceof JobEntity)
            {
                throw new \InvalidArgumentException('array have to contain JobEntity objects');
            }
        }
        parent::__construct($aJobEntities);
    }
}