<?php

namespace Joiz\EpgImporterBundle\Models;

use \Doctrine\Common\Collections\ArrayCollection;
use Joiz\EpgImporterBundle\Importer\TagsImporter;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/14/17
 * Time: 16:44
 */
class EpgProgram
{
    /** @var  $tagsImporter TagsImporter */
    private $tagsImporter;

    /** @var ArrayCollection */
    private $programItems;

    /**
     * @var array
     */
    private $tags;

    /**
     * @var array
     */
    private $showNames;

    public function __construct(TagsImporter $tagsImporter) {
        $this->tagsImporter         = $tagsImporter;

        $this->programItems = new ArrayCollection();
    }

    public function counter() {
        return $this->programItems->count();
    }

    public function save() {
        foreach ($this->programItems as $programItem) {
            $programItem->save();
        }
    }

    /**
     * @param ArrayCollection $programItems
     */
    public function setProgramItems($programItems) {
        $this->programItems = $programItems;
    }

    public function getProgramItems() {
        return $this->programItems;
    }

    public function addProgramItem(EpgProgramItem $programItem) {
        $this->programItems[] = $programItem;
    }

    public function removeProgramItem(EpgProgramItem $programItem) {
        $this->programItems->removeElement($programItem);
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        if(0 >= $this->counter()) {
            return [];
        }
        $tags = [];
        /** @var EpgProgramItem $epgProgramItem */
        foreach($this->programItems as $epgProgramItem) {
            $tags = array_merge($tags, explode(',', $epgProgramItem->getTags()));
        }
        $this->tags = array_unique($tags);
        return $this->tags;
    }

    /**
     * @return array
     */
    public function getShowNames(): array
    {
        return $this->showNames;
    }

    /**
     * @param array $showNames
     */
    public function setShowNames(array $showNames)
    {
        $this->showNames = $showNames;
    }

    public function importTags() {
        $this->tagsImporter->import($this->getTags());
    }
}