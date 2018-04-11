<?php

namespace Joiz\EpgImporterBundle\Tests\Models;

use Joiz\EpgImporterBundle\Models\EpgProgramItem;
use Joiz\HardcoreBundle\Test\JoizWebTestCase;

class EpgProgramItemTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var EpgProgramItem
     */
    protected $epgProgramItem;

    protected function setUp()
    {
        $this->epgProgramItem = new EpgProgramItem();
    }

    /**
     * @covers Joiz\EpgImporterBundle\Models\EpgProgramItem::getTitle
     */
    public function testGetTitle()
    {
        $this->epgProgramItem->setTitle('title');
        $this->assertEquals('title', $this->epgProgramItem->getTitle());
    }

    /**
     * @covers Joiz\EpgImporterBundle\Models\EpgProgramItem::getShowId
     */
    public function testGetShowId()
    {
        $this->epgProgramItem->setShowId(11111);
        $this->assertEquals(11111, $this->epgProgramItem->getShowId());
    }

    /**
     * @covers Joiz\EpgImporterBundle\Models\EpgProgramItem::getInstanceId
     */
    public function testGetInstanceId()
    {
        $this->epgProgramItem->setInstanceId(11111);
        $this->assertEquals(11111, $this->epgProgramItem->getInstanceId());
    }

    /**
     * @covers Joiz\EpgImporterBundle\Models\EpgProgramItem::getProgramId
     */
    public function testGetProgramId()
    {
        $this->epgProgramItem->setProgramId(11111);
        $this->assertEquals(11111, $this->epgProgramItem->getProgramId());
    }
    /**
     * @covers Joiz\EpgImporterBundle\Models\EpgProgramItem::getIsRerun
     */
    public function testGetIsRerun()
    {
        $this->epgProgramItem->setIsRerun(true);
        $this->assertEquals(true, $this->epgProgramItem->getIsRerun());
    }
    /**
     * @covers Joiz\EpgImporterBundle\Models\EpgProgramItem::getDuration
     */
    public function testGetDuration()
    {
        $this->epgProgramItem->setDuration('30:00');
        $this->assertEquals('30:00', $this->epgProgramItem->getDuration());
    }

    /**
     * @covers Joiz\EpgImporterBundle\Models\EpgProgramItem::getShowEpg
     */
    public function testGetShowEpg()
    {
        $this->epgProgramItem->setShowEpg(false);
        $this->assertEquals(false, $this->epgProgramItem->getShowEpg());
    }

    /**
     * @covers Joiz\EpgImporterBundle\Models\EpgProgramItem::getShowName
     */
    public function testGetShowName()
    {
        $this->epgProgramItem->setShowName("name");
        $this->assertEquals("name", $this->epgProgramItem->getShowName());
    }
}
