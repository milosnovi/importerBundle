<?php

namespace Joiz\EpgImporterBundle\ImporterRestClient;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/16/17
 * Time: 12:00
 */

interface EpgImporterInterface {

    /**
     * @return mixed
     */
    public function getEpg();
}