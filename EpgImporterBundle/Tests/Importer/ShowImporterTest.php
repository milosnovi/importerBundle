<?php

namespace Joiz\EpgImporterBundle\Tests\Importer;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/21/17
 * Time: 11:47
 */

use Joiz\EpgImporterBundle\Importer\ShowImporter;
use Joiz\EpgImporterBundle\Models\EpgProgramItem;
use Joiz\HardcoreBundle\Entity\Show;
use Joiz\HardcoreBundle\Test\JoizWebTestCase;

class ShowImporterTest extends JoizWebTestCase
{

    private $showRepository, $showImporter, $logger;

    protected function setUp() {

        $this->logger = $this->getMockBuilder('Joiz\EpgImporterBundle\Helpers\Logger')
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();

        $this->showRepository = $this->getMockBuilder('Joiz\HardcoreBundle\Repository\ShowRepository')
            ->disableOriginalConstructor()
            ->setMethods(['find', 'persist'])
            ->getMock();

        $this->showImporter = new ShowImporter(
            $this->showRepository,
            $this->logger
        );
    }

    private function returnProgramItem() {
        $programItem = new EpgProgramItem();
        $programItem->setTitle('new show 1');
        $programItem->setShowId(100000);
        $programItem->setShowName('Show name');
        $programItem->setInstanceId(10000);
        $programItem->setProgramId(10000);
        $programItem->setIsRerun(false);
        $programItem->setIsPublished(true);

        return $programItem;
    }


    /**
     * @covers Joiz\EpgImporterBundle\Importer\ShowImporter::import
     * @covers Joiz\EpgImporterBundle\Importer\ShowImporter::createShow
     */
    public function testSetShowCreateShow() {
        $programItem = $this->returnProgramItem();

        $this->showRepository->expects($this->any())
            ->method('find')
            ->with($programItem->getShowId())
            ->will($this->returnValue(NULL));

        $this->showRepository->expects($this->once())->method('persist');
        $this->logger->expects($this->once())->method('log');

        $show = $this->showImporter->import($programItem);

        $this->assertEquals($programItem->getShowId(), $show->getId());
        $this->assertEquals($programItem->getShowName(), $show->getName());
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\ShowImporter::import
     * @covers Joiz\EpgImporterBundle\importer\ShowImporter::import
     */
    public function testSetShowExistingShow() {
        $existingShow = new Show();
        $existingShow->setId(100000);
        $existingShow->setName('tested show name');

        $programItem = $this->returnProgramItem();

        $this->showRepository->expects($this->any())
            ->method('find')
            ->with($programItem->getShowId())
            ->will($this->returnValue($existingShow));

        $this->showRepository->expects($this->never())->method('persist');
        $this->logger->expects($this->once())->method('log');

        $show = $this->showImporter->import($programItem);

        $this->assertEquals($existingShow->getId(), $show->getId());
        $this->assertEquals($existingShow->getName(), $show->getName());
    }
}