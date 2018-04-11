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

class ShowInstanceImporter {

    /**
     * @var ShowInstanceRepository
     */
    private $showInstanceRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EpgProgramItem
     */
    private $programItem;

    /**
     * @var \DateTime
     */
    private $creationTime;

    /**
     * @param ShowInstanceRepository $showInstanceRepository
     * @param Logger $logger
     */
    public function __construct(ShowInstanceRepository $showInstanceRepository,
                                Logger $logger)
    {
        $this->showInstanceRepository = $showInstanceRepository;

        $this->logger = $logger;
        $this->creationTime = new \DateTime('now');
    }

    /**
     * @param EpgProgramItem $programItem
     * @param Show $parentShow | null
     * @return ShowInstance
     */
    public function import(EpgProgramItem $programItem, $parentShow = null) :ShowInstance{
        $this->programItem = $programItem;

        $instance = $this->getInstance();
        if($parentShow) {
            $instance->setShow($parentShow);
        }
        return $instance;
    }

    public function getInstance() {
        if (empty($instance = $this->showInstanceRepository->find($this->programItem->getInstanceId()))) {
            return $this->createShowInstance();
        }

        return $this->updateShowInstance($instance);
    }

    /**
     * @return ShowInstance
     */
    public function createShowInstance() {
        $instance = new ShowInstance();
        $instance->setId($this->programItem->getInstanceId());
        $instance->setCreatedAt($this->creationTime);

        $instance = $this->setShowInstanceData($instance);
        $this->showInstanceRepository->persist($instance);

        $this->logger->log('DB: Insert show-instance #ID=' . $instance->getId() . ' title= ' . $this->programItem->getTitle() . ' startime:' . $instance->getStartTime()->format('H:i'), true);

        return $instance;
    }

    /**
     * @param $instance
     * @return ShowInstance
     */
    public function updateShowInstance($instance) {
        $this->setShowInstanceData($instance);
        $this->logger->log('DB: Update show-instance #ID=' . $instance->getId() . ' title= ' . $this->programItem->getTitle() . ' startime:' . $instance->getStartTime()->format('H:i'), true);
        return $instance;
    }

    /**
     * @param ShowInstance $instance
     * @return ShowInstance
     */
    public function setShowInstanceData($instance) {
        $instance->setProgramId($this->programItem->getProgramId());
        $instance->setTitle($this->programItem->getTitle());
        $instance->setOnAir(FALSE);
        $instance->setIsRerun($this->programItem->getIsRerun());
        $instance->setDate($this->programItem->getStartTime());
        $instance->setStartTime($this->programItem->getStartTime());
        $instance->setEndTime($this->programItem->getEndTime());
        $instance->setDuration($this->programItem->getDuration());
        $instance->setIsPublished($this->programItem->getIsPublished());
        $instance->setUpdatedAt($this->creationTime);

        return $instance;
    }
}