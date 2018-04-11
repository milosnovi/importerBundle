<?php

namespace Joiz\EpgImporterBundle\Importer;

use Joiz\CmsBundle\Document\Show;
use Joiz\EpgImporterBundle\Models\EpgProgramItem;
use Joiz\HardcoreBundle\Helper\ConfigParamsHelper;
use Joiz\EpgImporterBundle\Helpers\Logger;
use Joiz\HardcoreBundle\Helper\StringHelper;
use Joiz\HardcoreBundle\Model\RegionManagerInterface;
use Joiz\CmsBundle\Repository\ShowRepository;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 4/4/17
 * Time: 15:32
 */

class ShowCrImporter {

    /**
     * @var ShowRepository
     */
    private $showRepository;

    /**
     * @var RegionManagerInterface
     */
    private $regionManager;

    /**
     * @var ImageImporter
     */
    private $imageImporter;

    /**
     * @var ConfigParamsHelper
     */
    private $configParams;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EpgProgramItem
     */
    private $programItem;

    /**
     * ShowInstanceImporter constructor.
     * @param ShowRepository $showRepository
     * @param RegionManagerInterface $regionManager
     * @param ImageImporter $imageImporter
     * @param ConfigParamsHelper $configParams
     * @param Logger $logger
     */
    public function __construct(ShowRepository $showRepository,
                                RegionManagerInterface $regionManager,
                                ImageImporter $imageImporter,
                                ConfigParamsHelper $configParams,
                                Logger $logger
    ){

        $this->showRepository   = $showRepository;
        $this->regionManager    = $regionManager;
        $this->imageImporter    = $imageImporter;
        $this->configParams     = $configParams;
        $this->logger           = $logger;
    }

    /**
     * @param $showNames
     */
    public function updateShow($showNames) {
        $shows = $this->showRepository->findAll();
        /** @var Show $show */
        foreach($shows as $show) {
            $extId = $show->getExtid();
            if(isset($showNames[$extId]) && ($show->getTitle() != $showNames[$extId])) {
                $this->logger->log('CMS UPDATE SHOW TITLE: show #' .$extId. ' with title=' . $show->getTitle() . ' has been changed to ' . $showNames[$extId], true);
                $show->setTitle($showNames[$extId]);
            }
        }
        $this->showRepository->flush();
    }

    /**
     * @param EpgProgramItem $programItem
     * @return Show|EpgProgramItem
     */
    public function import(EpgProgramItem $programItem) :Show {
        $this->programItem = $programItem;

        $showId = $this->programItem->getShowId();
        $showName = StringHelper::slugify($this->programItem->getShowName());

        if(is_object($searchShowByExtId = $this->showRepository->getShowByExtId((string)$showId))) {
            $showCR = $searchShowByExtId;
            $showCR->setTitle($this->programItem->getShowName());

            $this->logger->log('CMS: Update show #ID = ' . $showId . '; Name = ' . $showName . 'with title: ' . $this->programItem->getShowName(), true);
            return $showCR;
        }

        $showCR = $this->createShowCR();

        $this->showRepository->persist($showCR);
        $this->showRepository->flush();

        $this->logger->log('CMS: Insert show #ID = ' . $showId . '; Name = ' . $showName, true);

        return $showCR;
    }


    /**
     * @return Show
     */
    public function createShowCR() {
        $showCR = new Show();
        $showCR->setName($this->returnUniqueShowName($this->createShowName()));
        $showCR->setParentDocument($this->showRepository->findShowByPath());
        $showCR->setTitle($this->programItem->getShowName());
        $showCR->setBody($this->programItem->getShowName());
        $showCR->setExtid((string)$this->programItem->getShowId());
        $showCR->setTeaserText($this->programItem->getShowName());
        $showCR->setPublishable(false);
        $showCR->setAuthor($this->configParams->get('online_email'));
        $showCR->setLocale($this->showRepository->getLocale());

        $this->setDefaultRegion($showCR);
        $this->setTeaserImage($showCR);

        return $showCR;

    }

    public function createShowName() {
        $name = strtolower($this->programItem->getShowName());
        $name = preg_replace('/\s+/', '', $name);
            //$name = str_replace(' ', '_', $name);
        return iconv('utf-8', 'ascii//TRANSLIT//IGNORE', $name);
    }
    /**
     * @param $showCR Show
     * @return Show
     */
    public function setDefaultRegion($showCR) {
        $defaultRegion = $this->regionManager->getDefaultRegion();
        $showCR->setRegion([$defaultRegion->getId()]);
        return $showCR;
    }

    /**
     * @param $showCR Show
     * @return Show
     */
    public function setTeaserImage($showCR) {
        $teaserImage = $this->imageImporter->getDefaultImage($this->programItem->getShowTeaserImage());
        $showCR->setTeaserImage($teaserImage);
        return $showCR;
    }

    public function returnUniqueShowName($showName) {
        $index = 0;
        $normalizeShowName = StringHelper::slugify($showName);

        while(is_object($this->showRepository->findShowByPath($normalizeShowName))) {
            $normalizeShowName = sprintf("%s_%d",
                StringHelper::slugify($showName),
                ++$index
            );
        }
        return $normalizeShowName;
    }
}