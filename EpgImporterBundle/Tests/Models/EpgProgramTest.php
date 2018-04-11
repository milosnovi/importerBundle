<?php

namespace Joiz\EpgImporterBundle\Tests\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Joiz\EpgImporterBundle\Manager\TagsImporter;
use Joiz\EpgImporterBundle\Models\EpgProgram;
use Joiz\EpgImporterBundle\Models\EpgProgramItem;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/22/17
 * Time: 16:28
 */

class EpgProgramTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var EpgProgram
     */
    protected $epgProgram;
    /**
     * @var EpgProgram
     */
    protected $epgProgramItem;

    /** @var  TagsImporter */
    protected $tagsImporter;

    protected function setUp()
    {
        $this->tagsImporter = $this->getMockBuilder('Joiz\EpgImporterBundle\Importer\TagsImporter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->epgProgram = new EpgProgram($this->tagsImporter);

        $this->epgProgramItem = new EpgProgramItem();
        $this->epgProgramItem->setTitle('title');

        $this->epgProgram->addProgramItem($this->epgProgramItem);

    }

    public function testGetProgramItem() {
        $epgProgramItem1 = new EpgProgramItem();
        $epgProgramItem1->setTitle('title 2');

        $programItems = new ArrayCollection([$this->epgProgramItem, $epgProgramItem1]);
        $this->epgProgram->setProgramItems($programItems);

        $this->assertEquals($programItems, $this->epgProgram->getProgramItems());
    }

    public function testCounter() {
        $epgProgramItem1 = new EpgProgramItem();
        $epgProgramItem1->setTitle('title 2');

        $programItems = new ArrayCollection([$this->epgProgramItem, $epgProgramItem1]);
        $this->epgProgram->setProgramItems($programItems);

        $this->assertEquals(2, $this->epgProgram->counter());
    }

    public function testAddProgramItem() {
        $epgProgramItem = new EpgProgramItem();
        $epgProgramItem->setTitle('title11');
        $this->epgProgram->addProgramItem($epgProgramItem);

        $this->assertEquals(2, $this->epgProgram->counter());
    }

    public function testRemoveProgramItem() {
        $this->epgProgram->removeProgramItem($this->epgProgramItem);

        $this->assertEquals(0, $this->epgProgram->counter());
    }

}