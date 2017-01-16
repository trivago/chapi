<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 11/01/17
 * Time: 09:05
 */

namespace ChapiTest\src\TestTraits;


use Chapi\Entity\Chronos\JobCollection;
use Chapi\Entity\Marathon\MarathonAppEntity;

trait AppEntityTrait
{

    private function createAppCollection()
    {
        $_aAppEntities = [
            $this->getValidMarathonAppEntity('/main/id1')
        ];

        return new JobCollection($_aAppEntities);
    }

    private function getValidMarathonAppEntity($sId)
    {
        /** @var MarathonAppEntity $_oEntity */
        $_oEntity = new MarathonAppEntity();
        $_oEntity->id = $sId;
        return $_oEntity;
    }

    private function getValidMarathonAppEntityGroup($sId)
    {
        $_oEntity1 = new MarathonAppEntity();
        $_oEntity1->id = $sId . "/testapp1";

        $_oEntity2 = new MarathonAppEntity();
        $_oEntity2->id = $sId . "/testapp2";

        $_aRet["id"] = $sId;
        $_aRet["apps"] = [];
        $_aRet["apps"][] = $_oEntity1;
        $_aRet["apps"][] = $_oEntity2;

        return $_aRet;
    }
}