<?php

namespace Joiz\EpgImporterBundle\Importer;

use Joiz\CmsBundle\Document\ShowInstance;
use Joiz\CmsBundle\Document\Video;
use Joiz\CmsBundle\Repository\VideoRepository;
use Joiz\EpgImporterBundle\Helpers\Logger;
use Joiz\EpgImporterBundle\Models\EpgProgramItem;
use Joiz\HardcoreBundle\Helper\ConfigParamsHelper;
use Joiz\HardcoreBundle\Helper\StringHelper;
use Joiz\HardcoreBundle\Model\RegionManagerInterface;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 4/21/17
 * Time: 16:30
 */

class VideoImporter {

    /**
     * @var VideoRepository
     */
    private $videoRepository;

    /**
     * @var RegionManagerInterface
     */
    private $regionManager;

    /**
     * @var ConfigParamsHelper
     */
    private $configParams;

    /**
     * @var ImageImporter
     */
    private $imageImporter;

    /**
     * @var EpgProgramItem
     */
    private $programItem;

    public function __construct(VideoRepository $videoRepository,
                                RegionManagerInterface $regionManager,
                                ConfigParamsHelper $configParams,
                                ImageImporter $imageImporter,
                                Logger $logger)
    {
        $this->videoRepository = $videoRepository;
        $this->regionManager = $regionManager;
        $this->configParams = $configParams;
        $this->imageImporter = $imageImporter;
        $this->logger = $logger;
    }

    /**
     * @param EpgProgramItem $programItem
     * @param ShowInstance $instanceCr
     * @return Show|null|object
     */
    public function import(EpgProgramItem $programItem, ShowInstance $instanceCr) {
        $this->programItem = $programItem;

        if(!$videoId = $programItem->getVideoId()) {
            $this->logger->log('CMS: NO VIDEO IMPORT There is no video id for  instance=' . $programItem->getInstanceId(), true);
            return NULL;
        }

        if("Live" != $programItem->getVideoStatus()) {
            $this->logger->log('CMS: NO VIDEO IMPORT Video is still uploading for instance=' . $programItem->getInstanceId(), true);
            return NULL;
        }

        if(is_object($video = $this->videoRepository->getVideoByExtId($videoId, false))) {
            $this->logger->log('CMS: Skipping existing video #ID=' . $videoId . ' path: ' . $video->getId(), true);
            return $video;
        }

        $video = $this->createVideo($instanceCr);

        $this->videoRepository->persist($video);

        $this->logger->log('CMS: INSERT VIDEO <b>' . $video->getId() . '</b> with #id: ' . $programItem->getVideoId() .' for  instance=' . $programItem->getInstanceId(), true);
        return $video;
    }

    public function createVideo($instanceCr) {
        $defaultRegion = $this->regionManager->getDefaultRegion();

        $video = new Video();
        $video->setParentDocument($instanceCr);
        $video->setExtid((string)$this->programItem->getVideoId());
        $video->setLocale($this->videoRepository->getLocale());
        $video->setRegion([$defaultRegion->getId()]);
        $video->setName($this->programItem->getVideoName());
        $video->setTitle($this->programItem->getFullTitle());
        $video->setBody($this->programItem->getDescription());
        $video->setTags($this->programItem->getTags());
        $video->setAuthor($this->configParams->get('video_author'));
        $video->setPublishable($this->programItem->getIsPublished());

        $videoTeaserImage = $this->getTeaserImage();
        $video->setTeaserImage($videoTeaserImage);

        return $video;
    }

    /**
     * @return Image
     */
    public function getTeaserImage() {
        if(empty($this->programItem->getVideoImage())) {
            return $this->imageImporter->getDefaultImage($this->programItem->getShowTeaserImage());
        }

        return $this->imageImporter->createImage($this->programItem->getVideoImage());
    }
}
