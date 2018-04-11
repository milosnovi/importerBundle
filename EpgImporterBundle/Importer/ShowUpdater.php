<?php

namespace Joiz\EpgImporterBundle\Importer;

use Joiz\HardcoreBundle\Entity\Show;
use Joiz\HardcoreBundle\Entity\ShowInstance;
use Joiz\EpgImporterBundle\Models\EpgProgramItem;
use Joiz\EpgImporterBundle\Helpers\Logger;
use Joiz\HardcoreBundle\Repository\ShowInstanceRepository;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 4/4/17
 * Time: 17:16
 */

class ShowUpdater {

    /**
     * @var
     */
    protected $showImporter;

    /**
     * @var
     */
    protected $showCrImporter;


    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ShowImporter $showImporter
     * @param ShowCrImporter $showCrImporter
     * @param Logger $logger
     */
    public function __construct(ShowImporter $showImporter,
                                ShowCrImporter $showCrImporter,
                                Logger $logger)
    {
        $this->showImporter     = $showImporter;
        $this->showCrImporter   = $showCrImporter;
        $this->logger           = $logger;
    }

    /**
     * @param $showNames
     */
    public function update($showNames) {
        $this->showImporter->updateShow($showNames);
        $this->showCrImporter->updateShow($showNames);
    }
}