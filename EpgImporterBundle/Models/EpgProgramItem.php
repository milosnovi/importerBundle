<?php

namespace Joiz\EpgImporterBundle\Models;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/14/17
 * Time: 16:44
 */
class EpgProgramItem
{

    /**
     * @Assert\NotBlank(message="Title should not be blank")
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $teaserText;

    /**
     * @var string
     */
    private $showTeaserImage;

    /**
     * @Assert\NotBlank(message="Show id should not be blank")
     * @Assert\Type("integer", message="Show id must be integer")
     * @var
     */
    private $showId;

    /**
     * @Assert\NotBlank(message="Show Name should not be blank")
     * @Assert\Length(max=128, maxMessage = "Show name must be at least {{ limit }} characters long")
     * @var string
     */
    private $showName;

    /**
     * @Assert\NotBlank(message="Instance id should not be blank")
     * @Assert\Type("integer")
     * @var int
     */
    private $instanceId;

    /**
     * @Assert\Type("integer")
     *
     */
    private $programId;

    /**
     * @Assert\Type("bool")
     * @var
     */
    private $onAir;

    /**
     * @Assert\Type("bool")
     * @var
     */
    private $isRerun;

    //* @Assert\GreaterThan("today UTC", message="EpgStartTime must be greater than now")
    /**
     * @Assert\NotBlank(message="EpgStartTime value should not be blank")

     * @var \DateTime
     */
    private $startTime;

    /**
     * @Assert\NotBlank(message="EpgStartTime value should not be blank")
     * @var \DateTime
     */
    private $endTime;

    //* @Assert\NotBlank(message="Duration value should not be blank")
    /**
     * @var
     */
    private $duration;

    /**
     * @Assert\Type("bool")
     * @var
     */
    private $isPublished;

    /**
     * @var \DateTime
     */
    private $publishStartDate;

    /**
     * @var \DateTime
     */
    private $publishEndDate;

    /**
     * @var string
     */
    private $episodeNumberTitle;

    /**
     * @var string
     */
    private $videoStatus;

    /**
     * @Assert\Type("bool")
     * @var
     */
    private $showEpg;

    /**
     * @var string
     */
    private $tags;

    /**
     * @var string
     */
    private $videoId;

    /**
     * @var string
     */
    private $videoName;

    /**
     * @var string
     */
    private $videoImage;


    public function getTitle() {
        return substr($this->title,0, 100);
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getFullTitle()
    {
        return $this->title;
    }

    public function getDescription() {
        return $this->description ?? $this->title;
    }
    /**
     * @return string
     */
    public function getVideoId()
    {
        return $this->videoId;
    }

    /**
     * @param string $videoId
     */
    public function setVideoId($videoId)
    {
        $this->videoId = $videoId;
    }

    /**
     * @return string
     */
    public function getVideoName()
    {
        return $this->videoName;
    }

    /**
     * @param string $videoName
     */
    public function setVideoName($videoName)
    {
        $this->videoName = $videoName;
    }

    /**
     * @return string
     */
    public function getVideoImage()
    {
        return $this->videoImage;
    }

    /**
     * @param string $videoImage
     */
    public function setVideoImage($videoImage)
    {
        $this->videoImage = $videoImage;
    }

    /**
     * @return string
     */
    public function getTeaserText()
    {
        return $this->teaserText;
    }

    /**
     * @param string $teaserText
     */
    public function setTeaserText($teaserText)
    {
        $this->teaserText = $teaserText;
    }

    /**
     * @return mixed
     */
    public function getShowId()
    {
        return $this->showId;
    }

    /**
     * @param mixed $showId
     */
    public function setShowId($showId)
    {
        $this->showId = $showId;
    }

    /**
     * @return string
     */
    public function getShowName()
    {
        return $this->showName;
    }

    /**
     * @param string $showName
     */
    public function setShowName($showName)
    {
        $this->showName = $showName;
    }

    /**
     * @return int
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * @param int $instanceId
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;
    }

    /**
     * @return mixed
     */
    public function getProgramId()
    {
        return $this->programId;
    }

    /**
     * @param mixed $programId
     */
    public function setProgramId($programId)
    {
        $this->programId = $programId;
    }

    /**
     * @return mixed
     */
    public function getOnAir()
    {
        return $this->onAir;
    }

    /**
     * @param mixed $onAir
     */
    public function setOnAir($onAir)
    {
        $this->onAir = $onAir;
    }

    /**
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param \DateTime $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * @return mixed
     */
    public function getShowEpg()
    {
        return $this->showEpg;
    }

    /**
     * @param mixed $showEpg
     */
    public function setShowEpg($showEpg)
    {
        $this->showEpg = $showEpg;
    }

    /**
     * @return mixed
     */
    public function getIsRerun()
    {
        return $this->isRerun;
    }

    /**
     * @param mixed $isRerun
     */
    public function setIsRerun($isRerun)
    {
        $this->isRerun = $isRerun;
    }

    /**
     * @return \DateTime
     */
    public function getPublishStartDate()
    {
        return $this->publishStartDate;
    }

    /**
     * @param \DateTime $publishStartDate
     */
    public function setPublishStartDate($publishStartDate)
    {
        $this->publishStartDate = $publishStartDate;
    }

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return mixed
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * @param mixed $isPublished
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;
    }

    /**
     * @return \DateTime
     */
    public function getPublishEndDate()
    {
        return $this->publishEndDate;
    }

    /**
     * @param \DateTime $publishEndDate
     */
    public function setPublishEndDate($publishEndDate)
    {
        $this->publishEndDate = $publishEndDate;
    }

    /**
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param \DateTime $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getEpisodeNumberTitle()
    {
        return $this->episodeNumberTitle;
    }

    /**
     * @param string $episodeNumberTitle
     */
    public function setEpisodeNumberTitle($episodeNumberTitle)
    {
        $this->episodeNumberTitle = $episodeNumberTitle;
    }

    /**
     * @param string $videoStatus
     */
    public function setVideoStatus($videoStatus)
    {
        $this->videoStatus = $videoStatus;
    }

    /**
     * @param string
     */
    public function getVideoStatus()
    {
        return $this->videoStatus;
    }

    /**
     * @return mixed
     */
    public function getShowTeaserImage()
    {
        return $this->showTeaserImage;
    }

    /**
     * @param mixed $showTeaserImage
     */
    public function setShowTeaserImage($showTeaserImage)
    {
        $this->showTeaserImage = $showTeaserImage;
    }
}