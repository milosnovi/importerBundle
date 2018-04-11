<?php

namespace Joiz\EpgImporterBundle\ImporterManager;

use JMS\Serializer\SerializerBuilder;
use Joiz\EpgImporterBundle\Helpers\Logger;
use Joiz\EpgImporterBundle\Importer\ShowCrImporter;
use Joiz\EpgImporterBundle\Importer\ShowInstanceCrImporter;
use Joiz\EpgImporterBundle\Importer\VideoImporter;
use Joiz\EpgImporterBundle\Models\EpgProgram;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 4/4/17
 * Time: 15:32
 */

class ContentImporter {

    /**
     * @var ShowCrImporter
     */
    private $showCrImporter;

    /**
     * @var ShowInstanceCrImporter
     */
    private $showInstanceCrImporter;

    /**
     * @var VideoImporter
     */
    private $videoImporter;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        ShowCrImporter $showCrImporter,
        ShowInstanceCrImporter $showInstanceCrImporter,
        VideoImporter $videoImporter,
        Logger $logger
    ) {
        $this->showCrImporter           = $showCrImporter;
        $this->showInstanceCrImporter   = $showInstanceCrImporter;
        $this->videoImporter            = $videoImporter;
        $this->logger                   = $logger;
    }

    public function import(EpgProgram $epgProgram) {
        /** @var EpgProgramItem $programItem */
        foreach($epgProgram->getProgramItems() as $index => $programItem) {
            $serializer = SerializerBuilder::create()->build();
            $array = $serializer->toArray($programItem);

            $this->logger->log("CONTENT ITEM: <br/> <pre>" . print_r($array, 1) . "</pre>", true);

            $showCR = $this->showCrImporter->import($programItem);
            $instanceCR = $this->showInstanceCrImporter->import($programItem, $showCR);
            if($videoCr = $this->videoImporter->import($programItem, $instanceCR)) {
                $instanceCR->setHasVideos(TRUE);
            }
            $this->logger->log('-------------------------------------------', true);
        }
    }
}