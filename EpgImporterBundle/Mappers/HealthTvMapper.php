<?php

namespace Joiz\EpgImporterBundle\Mappers;

use Joiz\EpgImporterBundle\ImporterRestClient\EpgImporterInterface;
use Joiz\EpgImporterBundle\Models\EpgProgram;
use Joiz\EpgImporterBundle\Models\EpgProgramItem;
use Joiz\EpgImporterBundle\Helpers\Logger;
use Symfony\Component\Validator\Validator\RecursiveValidator;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/16/17
 * Time: 11:33
 */

class HealthTvMapper implements ClientMapperInterface {

    CONST SHOW_INSTANCE_NAME  = 'Name';

    CONST SHOW_INSTANCE_ID  = "ID";
    CONST PROGRAM_ID  = "ParentID";

    CONST SHOW_ID  = "ThirdPartyID";

    CONST START_TIME =  "UTCBegin";
    CONST END_TIME = "UTCEnd";

    CONST TAGS = "Category";

    CONST EPG_DURATION = "Duration";
    CONST EPG_EPISODE = "Episode";
    CONST EPG_DESCRIPTION = "Description";
    CONST EPG_TEASER_TEXT = "Teaser";


    /**
     * @var EpgProgram
     */
    protected $epgProgram;

    /**
     * @var EpgImporterInterface
     */
    protected $importer;


    /**
     * @var RecursiveValidator
     */
    protected $validator;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var
     */
    protected $programItem;

    /**
     * @var mixed
     */
    protected $epgData;

    /**
     * @var
     */
    protected $showNames = [];

    /**
     * @var
     */
    protected $episodeNUmberNames = [];

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
        $this->epgProgram = $epgProgram;

        $this->importer = $importer;

        $this->validator = $validator;

        $this->logger = $logger;
    }

    /**
     * @return array of shows fetched from api in form exttd=>title
     */
    public function getShows(): array {
        $this->showNames = $this->importer->getShowNames();
        $shows = [];
        foreach($this->showNames as $showName) {
            if(!empty($showName['Name'])) {
                $shows[$showName['Value']] = $showName['Name'];
            }
        }

        return $shows;
    }

    /**
     * @return EpgProgram
     */
    public function generateEpg():EpgProgram {
        $this->epgData = $this->importer->getEpg();

        $this->showNames = $this->importer->getShowNames();
        $this->episodeNUmberNames = $this->importer->getEpisodeNumberName();

        foreach($this->epgData as $program) {
            $this->programItem = $program;

            $epgProgramItem = $this->generateProgramItem();
            if($this->validate($epgProgramItem)) {
                $this->epgProgram->addProgramItem($epgProgramItem);
            }
        }
        return $this->epgProgram;
    }

    /**
     * @return EpgProgramItem
     */
    public function generateProgramItem() {
        $epgProgramItem = new EpgProgramItem();

        $epgProgramItem->setShowId($this->getShowId());
        $epgProgramItem->setShowName($this->getShowName());

        $epgProgramItem->setTitle($this->getTitle());
        $epgProgramItem->setInstanceId($this->getInstanceId());
        $epgProgramItem->setStartTime($this->getStartTime());
        $epgProgramItem->setEndTime($this->getEndTime());
        $epgProgramItem->setDuration($this->getDuration());
        $epgProgramItem->setIsRerun($this->getIsRerun());
        $epgProgramItem->setProgramId($this->getProgramId());
        $epgProgramItem->setTags($this->getTags());
        $epgProgramItem->setTeaserText($this->getTeaserText());
        $epgProgramItem->setEpisodeNumberTitle($this->getEpisodeNumberTitle());
        $epgProgramItem->setDescription($this->getDescription());
        $epgProgramItem->setShowTeaserImage("app/main/dev/PHPCRFixtures/data/health.tv/logo.jpg");

        $epgProgramItem->setPublishStartDate($this->getPublishStartDate());
        $epgProgramItem->setPublishEndDate($this->getPublishEndDate());
        // editor approves content manually
        $epgProgramItem->setIsPublished(false);

        $epgProgramItem->setVideoId($this->getVideoId());
        $epgProgramItem->setVideoName($this->getVideoName());
        $epgProgramItem->setVideoImage($this->getVideoImage());
        $epgProgramItem->setVideoStatus($this->getVideoStatus());

        return $epgProgramItem;
    }

    /**
     * @param EpgProgramItem $epgProgramItem
     * @return EpgProgram
     */
    public function validate(EpgProgramItem $epgProgramItem) {
        $validationErrors = $this->validator->validate($epgProgramItem);
        if (count($validationErrors) <= 0) {
            return true;
        }

        $validationMessage = "Error for show_instance #ID=" . $epgProgramItem->getInstanceId() . ': ';
        foreach ($validationErrors as $key => $error) {
            $validationMessage .= "<br/><b>" . $error->getPropertyPath() . "</b> => " . $error->getMessage() . ', ';
        }
        $this->logger->log("<pre>".$validationMessage."</pre>", TRUE);
        return false;
    }

    /**
     * @return int
     */
    public function getEpisodeNumberTitle() {
        $onlineWebsiteData = $this->getKeySetData("Online_Website");
        $episodeIndex = (int)$onlineWebsiteData["Episode"];

        foreach($this->episodeNUmberNames as $episodeNumberName) {
            if($episodeIndex == $episodeNumberName["Value"]) {
                return $episodeNumberName["Name"];
            }
        }

        return NULL;
    }

    /**
     * @return int
     */
    public function getShowId() {
        $onlineWebsiteData = $this->getKeySetData("Online_Website");
        return  !empty($onlineWebsiteData['Format']) ? (int)$onlineWebsiteData['Format'] : NULL;
    }

    /**
     * @return int
     */
    public function getShowName() {
        $showId = $this->getShowId();
        foreach($this->showNames as $show) {
            if($showId == $show["Value"]) {
                return $show["Name"];
            }
        }
        return  NULL;
    }

    /**
     * @return int
     */
    public function getInstanceId() {
        return $this->programItem[self::SHOW_INSTANCE_ID] ?? NULL;
    }

    /**
     * @return \DateTime
     */
    public function getDate() {
        if(!isset($this->programItem[self::START_TIME])) {
            return NULL;
        }
        return new \DateTime($this->programItem[self::START_TIME]);
    }

    /**
     * @return \DateTime
     */
    public function getStartTime() {
        if(!isset($this->programItem[self::START_TIME])) {
            return NULL;
        }
        return new \DateTime($this->programItem[self::START_TIME]);
    }


    /**
     * @return \DateTime
     */
    public function getEndTime() {
        if(!isset($this->programItem[self::END_TIME])) {
            return NULL;
        }
        return new \DateTime($this->programItem[self::END_TIME]);
    }

    /**
     * @return string
     */
    public function getDuration() {
        $epgData = $this->getKeySetData("EPG");
        if(empty($epgData[self::EPG_DURATION])) {
            return NULL;
        }
        return new \DateTime($epgData[self::EPG_DURATION]);
    }

    /**
     * @return string
     */
    public function getDescription() {
        $epgData = $this->getKeySetData("Online_Website");
        return $epgData[self::EPG_DESCRIPTION] ?? NULL;
    }

    /**
     * @return string
     */
    public function getTitle():string {
        $onlineWebsiteData = $this->getKeySetData("Online_Website");

        return $onlineWebsiteData["Title"] ?? '';
    }

    /**
     * @return string
     */
    public function getTags() {
        return $this->programItem[self::TAGS] ?? '';
    }

    /**
     * @return bool
     */
    public function getIsRerun() {
        return isset($this->programItem[self::PROGRAM_ID]) && $this->getInstanceId() != $this->programItem[self::PROGRAM_ID];
    }

    /**
     * @return int
     */
    public function getProgramId() {
        if($this->getIsRerun()) {
            return $this->programItem[self::PROGRAM_ID];
        }

        return $this->getInstanceId();
    }

    /**
     * @return string
     */
    public function getTeaserText() {
        $epgData = $this->getKeySetData("Online_Website");
        return $epgData[self::EPG_TEASER_TEXT] ?? $this->getTitle();
    }

    public function getPublishStartDate () {
        $onlineWebsite = $this->getKeySetData("Online_Website");
        if(!isset($onlineWebsite["Publishing date"])) {
            return NULL;
        }
        return new \DateTime($onlineWebsite["Publishing date"]);
    }

    public function getPublishEndDate () {
        $onlineWebsite = $this->getKeySetData("Online_Website");
        if(!isset($onlineWebsite["Exiry date"])) {
            return NULL;
        }
        return new \DateTime($onlineWebsite["Exiry date"]);
    }

    public function getVideoId() {
        $videoData = $this->getKeySetData("Ooyala_Data");
        return $videoData["Ooyala_Data_embed_code"] ?? NULL;
    }

    public function getVideoStatus() {
        $videoData = $this->getKeySetData("Ooyala_Data");
        return $videoData["Ooyala_Data_status"] ?? NULL;
    }

    public function getVideoName() {
        return 'video';
//        $videoData = $this->getKeySetData("Ooyala_Data");
//        return $videoData["Ooyala_Data_item_name"] ?? NULL;
    }

    public function getVideoImage() {
        $videoData = $this->getKeySetData("Ooyala_Data");
        return $videoData["Ooyala_Data_preview_image_url"] ?? NULL;
    }

    /**
     * @return bool
     */
    public function isPublished() {
        return TRUE;
    }

    private function getKeySetData($index) {
        $keySets = $this->programItem["KeySets"] ?? NULL;
        if(!$keySets) {
            return NULL;
        }
        foreach($keySets as $keySet) {
            if(array_key_exists($index, $keySet)) {
                return $keySet[$index];
            }
        }
        return NULL;
    }

    /**
     * @return mixed
     */
    private function getClips() {
        $clips = $this->programItem["Clips"] ?? NULL;
        if(!$clips) {
            return NULL;
        }

        foreach($clips as $clip) {
            if(!empty($clip)) {
                return $clip;
            }
        }
        return NULL;
    }
}