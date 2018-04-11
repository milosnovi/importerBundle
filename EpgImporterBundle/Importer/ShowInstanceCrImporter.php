<?php

namespace Joiz\EpgImporterBundle\Importer;

use Joiz\CmsBundle\Document\Show;
use Joiz\CmsBundle\Repository\ShowInstanceRepository;
use Joiz\EpgImporterBundle\Models\EpgProgramItem;
use Joiz\HardcoreBundle\Helper\ConfigParamsHelper;
use Joiz\EpgImporterBundle\Helpers\Logger;
use Joiz\HardcoreBundle\Helper\StringHelper;
use Joiz\HardcoreBundle\Model\RegionManagerInterface;
use Joiz\CmsBundle\Document\ShowInstance;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 4/4/17
 * Time: 15:32
 */

class ShowInstanceCrImporter {

    /**
     * @var ShowInstanceRepository
     */
    private $showInstanceRepository;

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
     * @var EpgProgramItem
     */
    private $programItem;

    /**
     * ShowInstanceImporter constructor.
     * @param ShowInstanceRepository $showInstanceRepository
     * @param RegionManagerInterface $regionManager
     * @param ImageImporter $imageImporter
     * @param ConfigParamsHelper $configParams
     * @param Logger $logger
     */
    public function __construct(ShowInstanceRepository $showInstanceRepository,
                                RegionManagerInterface $regionManager,
                                ImageImporter $imageImporter,
                                ConfigParamsHelper $configParams,
                                Logger $logger
    ){

        $this->showInstanceRepository   = $showInstanceRepository;
        $this->regionManager            = $regionManager;
        $this->imageImporter            = $imageImporter;
        $this->configParams             = $configParams;
        $this->logger                   = $logger;
    }

    /**
     * @param EpgProgramItem $programItem
     * @param Show $parentCRShow
     * @return ShowInstance
     */
    public function import(EpgProgramItem $programItem, Show $parentCRShow):ShowInstance {
        $this->programItem = $programItem;

        $instanceName = StringHelper::slugify($programItem->getTitle());

        $checkInstanceInCr = $this->showInstanceRepository->getShowInstanceByExtId($programItem->getProgramId());

        if (is_object($checkInstanceInCr) && $checkInstanceInCr instanceof ShowInstance) {
            $this->logger->log('CMS: Skip show-instance from Show = ' .$programItem->getShowName() . ' with  #ID = ' . $programItem->getProgramId() . ' Name = ' . $instanceName. ' do not update a existing cms page!' , true);
            return $checkInstanceInCr;
        }

        $instanceCR = $this->createInstanceCR($parentCRShow);

        $this->showInstanceRepository->persist($instanceCR);
        $this->showInstanceRepository->flush();

        $this->logger->log('CMS: Insert show-instance <b>'. $instanceCR->getId() .'</b> #ID = ' . $programItem->getProgramId() . ' Name = ' . $instanceName , true);

        return $instanceCR;
    }


    public function createInstanceCR(Show $parent) {
        /** @var ContentRepositoryInstance $instanceCR */
        $instanceCR = new ShowInstance();

        $instanceCR->setName($this->returnUniqueShowInstanceName());
        $instanceCR->setLocale($this->showInstanceRepository->getLocale());
        $instanceCR->setParentDocument($parent);
        $instanceCR->setExtid((string)$this->programItem->getProgramId());
        $instanceCR->setTitle($this->programItem->getFullTitle());
        $instanceCR->setTeaserText($this->programItem->getTeaserText());
        $instanceCR->setBody($this->programItem->getDescription());
        $instanceCR->setAuthor($this->configParams->get('online_email'));
        $instanceCR->setMetaTitle($this->programItem->getTitle());
        $instanceCR->setPublishable(false);
        $instanceCR->setMetaTitle($this->programItem->getTitle());
        $instanceCR->setTags($this->programItem->getTags());
        $instanceCR->setCreatedAt(new \DateTime('now'));
        $instanceCR->setEpisodeNumber($this->programItem->getEpisodeNumberTitle());

        $instanceCR->setPublishable($this->programItem->getIsPublished());
        $instanceCR->setPublishStartDate($this->programItem->getPublishStartDate());
        $instanceCR->setPublishEndDate($this->programItem->getPublishEndDate());

        $this->setDefaultRegion($instanceCR);
        $teaserImage = $this->getTeaserImage();
        $instanceCR->setTeaserImage($teaserImage);

        return $instanceCR;
    }

    public function returnUniqueShowInstanceName() {
        return sprintf("%s_%s",
            StringHelper::slugify($this->programItem->getTitle()),
            $this->programItem->getProgramId());
    }

    /**
     * @param $instance
     */
    public function setDefaultRegion($instance) {
        $defaultRegion = $this->regionManager->getDefaultRegion();
        $instance->setRegion([$defaultRegion->getId()]);
    }

    /**
     * @return \Symfony\Cmf\Bundle\MediaBundle\Doctrine\Phpcr\Image
     */
    public function getTeaserImage() {
        if(empty($this->programItem->getVideoImage())) {
            return $this->imageImporter->getDefaultImage($this->programItem->getShowTeaserImage());
        }

        return $this->imageImporter->createImage($this->programItem->getVideoImage());
    }
}