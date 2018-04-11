<?php

namespace Joiz\EpgImporterBundle\ImporterManager;

use JMS\Serializer\SerializerBuilder;
use Joiz\EpgImporterBundle\Helpers\Logger;
use Joiz\EpgImporterBundle\Importer\ShowImporter;
use Joiz\EpgImporterBundle\Importer\ShowInstanceImporter;
use Joiz\EpgImporterBundle\Models\EpgProgram;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 4/4/17
 * Time: 15:32
 */

class EpgImporter {

    /**
     * @var ShowImporter
     */
    private $showImporter;

    /**
     * @var ShowInstanceImporter
     */
    private $showInstanceImporter;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        ShowImporter $showImporter,
        ShowInstanceImporter $showInstanceImporter,
        Logger $logger
    ) {
        $this->showImporter             = $showImporter;
        $this->showInstanceImporter     = $showInstanceImporter;
        $this->logger                   = $logger;
    }

    public function import(EpgProgram $epgProgram) {
        /** @var EpgProgramItem $programItem */
        foreach($epgProgram->getProgramItems() as $index => $programItem) {
            $serializer = SerializerBuilder::create()->build();
            $array = $serializer->toArray($programItem);

            $this->logger->log("PROGRAM ITEM: <br/> <pre>" . print_r($array, 1) . "</pre>", true);

            $showDB = $this->showImporter->import($programItem);
            $showInstanceDB = $this->showInstanceImporter->import($programItem, $showDB);
            $this->logger->log('-------------------------------------------', true);
        }
    }
}