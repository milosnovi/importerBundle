<?php

namespace Joiz\EpgImporterBundle\Mappers;

use Joiz\EpgImporterBundle\Models\EpgProgram;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/16/17
 * Time: 11:22
 */

interface ClientMapperInterface {

    /**
     * @return string
     */
    public function getTitle():string;

    /**
     * @return int
     */
    public function getShowId();

    /**
     * @return int
     */
    public function getInstanceId();

    /**
     * @return \DateTime
     */
    public function getDate();

    /**
     * @return \DateTime
     */
    public function getStartTime();

    /**
     * @return \DateTime
     */
    public function getEndTime();

    /**
     * @return EpgProgram
     */
    public function generateEpg():EpgProgram;

    /**
     * @return array
     */
    public function getShows(): array;


}