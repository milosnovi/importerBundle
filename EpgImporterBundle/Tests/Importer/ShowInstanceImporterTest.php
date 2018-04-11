<?php

namespace Joiz\EpgImporterBundle\Tests\Importer;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/21/17
 * Time: 11:47
 */

use Joiz\EpgImporterBundle\Importer\ShowInstanceImporter;
use Joiz\EpgImporterBundle\Models\EpgProgramItem;
use Joiz\HardcoreBundle\Entity\Show;
use Joiz\HardcoreBundle\Entity\ShowInstance;
use Joiz\HardcoreBundle\Test\JoizWebTestCase;

class ShowInstanceImporterTest extends JoizWebTestCase
{


    private $showInstanceRepository;

    /**
     * @var ShowInstanceImporter
     */
    private $showInstanceImporter;
    private $logger;

    protected function setUp() {

        $this->logger = $this->getMockBuilder('Joiz\EpgImporterBundle\Helpers\Logger')
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();

        $this->showInstanceRepository = $this->getMockBuilder('Joiz\HardcoreBundle\Repository\ShowInstanceRepository')
            ->disableOriginalConstructor()
            ->setMethods(['find', 'persist'])
            ->getMock();

        $this->showInstanceImporter = new ShowInstanceImporter(
            $this->showInstanceRepository,
            $this->logger
        );
    }
    private function returnShowInstance() {
        $instance = new ShowInstance();
        $instance->setId(1111);
        $instance->setTitle("Instance name");

        return $instance;
    }

    private function returnShow() {
        $show = new Show();
        $show->setId(11);
        $show->setName("show name");

        return $show;
    }

    private function returnProgramItem() {
        $programItem = new EpgProgramItem();
        $programItem->setTitle('new show 1');
        $programItem->setShowId(100000);
        $programItem->setShowName('Show name');
        $programItem->setInstanceId(10000);
        $programItem->setIsRerun(TRUE);
        $programItem->setProgramId(10000);
        $programItem->setStartTime(new \DateTime('now'));
        $programItem->setEndTime(new \DateTime('now'));

        return $programItem;
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceImporter::import
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceImporter::getInstance
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceImporter::createShowInstance
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceImporter::setShowInstanceData
     */
    public function testImportingNewInstanceWithoutParentShow() {
        $programItem = $this->returnProgramItem();

        $this->showInstanceRepository->expects($this->any())
            ->method('find')->with($programItem->getInstanceId())
            ->will($this->returnValue(NULL));

        $this->showInstanceRepository->expects($this->once())->method('persist');
        $this->logger->expects($this->once())->method('log');

        $showInstance = $this->showInstanceImporter->import($programItem);

        $this->assertInstanceOf(ShowInstance::class, $showInstance);
        $this->assertEquals($showInstance->getId(), $programItem->getInstanceId());
        $this->assertEquals($showInstance->getTitle(), $programItem->getTitle());
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceImporter::import
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceImporter::getInstance
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceImporter::updateShowInstance
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceImporter::setShowInstanceData
     */
    public function testImportingExistingInstanceWithoutParentShow() {
        $programItem = $this->returnProgramItem();

        $this->showInstanceRepository->expects($this->any())
            ->method('find')->with($programItem->getInstanceId())
            ->will($this->returnValue($this->returnShowInstance()));

        $this->showInstanceRepository->expects($this->never())->method('persist');
        $this->logger->expects($this->once())->method('log');

        $showInstance = $this->showInstanceImporter->import($programItem);

        $this->assertInstanceOf(ShowInstance::class, $showInstance);
        $this->assertEquals($showInstance->getTitle(), $programItem->getTitle());
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceImporter::import
     */
    public function testImportWithParentShow() {
        $programItem = $this->returnProgramItem();
        $show = $this->returnShow();

        $showInstance = $this->showInstanceImporter->import($programItem, $show);

        $this->assertEquals($showInstance->getShow(), $show);
    }

}