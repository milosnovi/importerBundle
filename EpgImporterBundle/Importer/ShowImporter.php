<?php

namespace Joiz\EpgImporterBundle\Importer;

use Joiz\EpgImporterBundle\Models\EpgProgramItem;
use Joiz\HardcoreBundle\Entity\Show;
use Joiz\EpgImporterBundle\Helpers\Logger;
use Joiz\HardcoreBundle\Repository\ShowRepository;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 4/4/17
 * Time: 17:16
 */

class ShowImporter {

    /**
     * @var ShowRepository
     */
    private $showRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EpgProgramItem
     */
    private $programItem;

    /**
     * @param ShowRepository $showRepository
     * @param Logger $logger
     */
    public function __construct(ShowRepository $showRepository,
                                Logger $logger)
    {
        $this->showRepository = $showRepository;
        $this->logger = $logger;
    }

    /**
     * @param EpgProgramItem $programItem
     * @return Show|null|object
     */
    public function import(EpgProgramItem $programItem) :Show{
        $this->programItem = $programItem;

        $showId = $programItem->getShowId();
        $showDB = $this->showRepository->find($showId);
        if($showDB) {
            $this->logger->log('DB: Skipping existing show #ID=' . $showId . ' #title= ' . $programItem->getShowName(), true);
            return $showDB;
        }

        $this->logger->log('DB: Insert show #ID=' . $this->programItem->getShowId() . ' #title= ' . $this->programItem->getShowName(), true);
        return $this->createShow();
    }

    public function updateShow($showNames) {
        $shows = $this->showRepository->findAll();
        /** @var Show $show */
        foreach($shows as $show) {
            $id = $show->getId();
            if(isset($showNames[$id]) && ($show->getName() != $showNames[$id])) {
                $this->logger->log('DB UPDATE SHOW TITLE: show #' .$id. 'with title= ' . $show->getName() . ' has been changed to ' . $showNames[$id], true);
                $show->setName($showNames[$id]);
            }
        }
    }

    /**
     * @return Show
     */
    public function createShow() {
        $showDB = new Show();
        $showDB->setId($this->programItem->getShowId());
        $showDB->setName($this->programItem->getShowName());
        $showDB->setCreatedAt(new \DateTime());

        $this->showRepository->persist($showDB);
        return $showDB;
    }


}