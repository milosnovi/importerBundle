<?php

namespace Joiz\EpgImporterBundle\Tests\Importer;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/21/17
 * Time: 11:47
 */

use Joiz\EpgImporterBundle\Importer\ShowInstanceCrImporter;
use Joiz\EpgImporterBundle\Models\EpgProgramItem;
use Joiz\HardcoreBundle\Entity\Region;
use Joiz\HardcoreBundle\Helper\StringHelper;
use Joiz\HardcoreBundle\Test\JoizWebTestCase;
use Symfony\Cmf\Bundle\MediaBundle\Doctrine\Phpcr\Image;

class ShowInstanceCrImporterTest extends JoizWebTestCase
{

    private $showInstanceRepository;

    /**
     * @var ShowInstanceCrImporter
     */
    private $showInstanceCrImporter;
    private $logger;
    private $imageImporter;
    private $configParams;
    private $regionManager;

    protected function setUp() {

        $this->logger = $this->getMockBuilder('Joiz\EpgImporterBundle\Helpers\Logger')
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();

        $this->showInstanceRepository = $this->getMockBuilder('Joiz\CmsBundle\Repository\ShowInstanceRepository')
            ->disableOriginalConstructor()
            ->setMethods(['getShowInstanceByExtId', 'findShowByPath', 'find', 'persist', 'getLocale', 'flush'])
            ->getMock();

        $this->imageImporter = $this->getMockBuilder('Joiz\EpgImporterBundle\Importer\ImageImporter')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->configParams = $this->getMockBuilder('Joiz\HardcoreBundle\Helper\ConfigParamsHelper')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->regionManager = $this->getMockBuilder('Joiz\HardcoreBundle\Model\RegionManager')
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultRegion'])
            ->getMock();

        $this->configParams->expects($this->any())->method('get')->will($this->returnValue('author@levuro.com'));
        $this->imageImporter->expects($this->any())->method('getDefaultImage')->will($this->returnValue($this->returnImage()));
        $this->regionManager->expects($this->any())->method('getDefaultRegion')->will($this->returnValue($this->returnRegion()));
        $this->showInstanceRepository->expects($this->any())->method('getLocale')->will($this->returnValue('en'));

        $this->showInstanceCrImporter = new ShowInstanceCrImporter(
            $this->showInstanceRepository,
            $this->regionManager,
            $this->imageImporter,
            $this->configParams,
            $this->logger
        );
    }
    private function returnShowInstance() {
        $instance = new \Joiz\CmsBundle\Document\ShowInstance();
        $instance->setId(1111);
        $instance->setTitle("Instance name");

        return $instance;
    }

    private function returnShow() {
        $show = new \Joiz\CmsBundle\Document\Show();
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

    private function returnImage() {
        $image = new Image();
        $image->setName('name');
        return $image;
    }

    private function returnRegion() {
        $region = new Region();
        $region->setId(1);
        $region->setName('region');
        $region->setCode('c_eur');

        return $region;
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceCrImporter::import
     */
    public function testImportExistingShowInstance() {
        $programItem = $this->returnProgramItem();
        $show = $this->returnShow();
        $showInstance = $this->returnShowInstance();

        $this->showInstanceRepository
            ->expects($this->any())
            ->method('getShowInstanceByExtId')
            ->will($this->returnValue($showInstance));

        $this->showInstanceRepository->expects($this->never())->method('persist');
        $this->logger->expects($this->once())->method('log');

        $showInstanceCR = $this->showInstanceCrImporter->import($programItem, $show);

        $this->assertInstanceOf(\Joiz\CmsBundle\Document\ShowInstance::class, $showInstanceCR);
        $this->assertEquals($showInstance->getId(), $showInstanceCR->getId());
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceCrImporter::import
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceCrImporter::createInstanceCR
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceCrImporter::returnUniqueShowInstanceName
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceCrImporter::setDefaultRegion
     * @covers Joiz\EpgImporterBundle\Importer\ShowInstanceCrImporter::getTeaserImage
     */
    public function testImportNewShowInstance() {
        $programItem = $this->returnProgramItem();
        $show = $this->returnShow();

        $this->showInstanceRepository
            ->expects($this->any())
            ->method('getShowInstanceByExtId')
            ->will($this->returnValue(NULL));

        $this->showInstanceRepository->expects($this->once())->method('persist');
        $this->logger->expects($this->once())->method('log');

        /** @var \Joiz\CmsBundle\Document\ShowInstance $showInstanceCR */
        $showInstanceCR = $this->showInstanceCrImporter->import($programItem, $show);

        $this->assertInstanceOf(\Joiz\CmsBundle\Document\ShowInstance::class, $showInstanceCR);

        $newShowInstanceName = sprintf("%s_%s", StringHelper::slugify($programItem->getTitle()), $programItem->getProgramId());
        $this->assertEquals($newShowInstanceName, $showInstanceCR->getName());
        $this->assertEquals((string)$programItem->getProgramId(), $showInstanceCR->getExtId());
        $this->assertEquals(false, $showInstanceCR->isPublishable());
        $this->assertEquals([1], $showInstanceCR->getRegion());
        $this->assertEquals(1, count($showInstanceCR->getTeaserImage()));
    }
}