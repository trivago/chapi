<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2017-01-11
 *
 */

namespace ChapiTest\src\TestTraits;

use Chapi\Entity\Chronos\JobCollection;
use Chapi\Entity\Marathon\MarathonAppEntity;

trait AppEntityTrait
{

    private function createAppCollection($appNames)
    {
        $appEntities = [];

        foreach ($appNames as $appName) {
            $appEntities[] = $this->getValidMarathonAppEntity($appName);
        }

        return new JobCollection($appEntities);
    }

    private function getValidMarathonAppEntity($id)
    {
        /** @var MarathonAppEntity $entity */
        $entity = new MarathonAppEntity();
        $entity->id = $id;
        return $entity;
    }


    private function getValidMarathonAppEntityGroup($id)
    {
        $entity1 = new MarathonAppEntity();
        $entity1->id = $id . "/testapp1";

        $entity2 = new MarathonAppEntity();
        $entity2->id = $id . "/testapp2";

        $return["id"] = $id;
        $return["apps"] = [];
        $return["apps"][] = $entity1;
        $return["apps"][] = $entity2;

        return $return;
    }
}
