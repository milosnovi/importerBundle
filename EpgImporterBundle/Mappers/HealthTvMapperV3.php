<?php

namespace Joiz\EpgImporterBundle\Mappers;

use Joiz\EpgImporterBundle\ImporterRestClient\EpgImporterInterface;
use Joiz\EpgImporterBundle\Models\EpgProgram;
use Joiz\EpgImporterBundle\Helpers\Logger;
use Symfony\Component\Validator\Validator\RecursiveValidator;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/16/17
 * Time: 11:33
 */

class HealthTvMapperV3 extends HealthTvMapper {

    const ID         = 'ID';

    const ORIGINAL_ID = 'OriginalID';

    /**
     * @param $epgProgram EpgProgram
     * @param $importer EpgImporterInterface
     * @param $validator RecursiveValidator
     * @param $logger Logger
     */
    public function __construct(EpgProgram $epgProgram,
                                EpgImporterInterface $importer,
                                RecursiveValidator $validator,
                                Logger $logger)
    {
        parent::__construct($epgProgram, $importer, $validator, $logger);
    }


    /**
     * @return int
     */
    public function getInstanceId() {
        return $this->programItem[self::ID] ?? NULL;
    }

    /**
     * @return int
     */
    public function getProgramId() {
        return $this->programItem[self::ORIGINAL_ID] ?? NULL;
    }

    /**
     * @return bool
     */
    public function getIsRerun() {
        return isset($this->programItem[self::PROGRAM_ID]) && $this->programItem[self::ID] != $this->programItem[self::PROGRAM_ID];
    }

    public function generateContent() {
        $content = $this->importer->getContent();

        foreach($content as $program) {
            $this->programItem = $program;

            $epgProgramItem = $this->generateProgramItem();
            if($this->validate($epgProgramItem)) {
                $this->epgProgram->addProgramItem($epgProgramItem);
            }
        }
        return $this->epgProgram;
    }

}