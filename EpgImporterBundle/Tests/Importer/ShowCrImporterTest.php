<?php

namespace Joiz\EpgImporterBundle\Tests\Importer;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/21/17
 * Time: 11:47
 */

use Joiz\CmsBundle\Document\Standard;
use Joiz\EpgImporterBundle\Importer\ShowCrImporter;
use Joiz\EpgImporterBundle\Models\EpgProgramItem;
use Joiz\HardcoreBundle\Entity\Region;
use Joiz\HardcoreBundle\Entity\Show;
use Joiz\HardcoreBundle\Test\JoizWebTestCase;
use Symfony\Cmf\Bundle\MediaBundle\Doctrine\Phpcr\Image;

class ShowCrImporterTest extends JoizWebTestCase
{

    private $showRepository, $logger, $imageImporter, $configParams, $regionManager;

    private $showCrImporter;

    protected function setUp() {

        $this->logger = $this->getMockBuilder('Joiz\EpgImporterBundle\Helpers\Logger')
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();

        $this->showRepository = $this->getMockBuilder('Joiz\CmsBundle\Repository\ShowRepository')
            ->disableOriginalConstructor()
            ->setMethods(['getShowByExtId', 'findShowByPath', 'find', 'persist', 'getLocale', 'flush'])
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
        $this->showRepository->expects($this->any())->method('getLocale')->will($this->returnValue('en'));

        $this->showCrImporter = new ShowCrImporter(
            $this->showRepository,
            $this->regionManager,
            $this->imageImporter,
            $this->configParams,
            $this->logger
        );
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

    private function returnProgramItem() {
        $programItem = new EpgProgramItem();
        $programItem->setTitle('new show 1');
        $programItem->setShowId(100000);
        $programItem->setShowName('Show name');
        $programItem->setInstanceId(10000);
        $programItem->setProgramId(10000);
        $programItem->setIsRerun(false);
        $programItem->setShowTeaserImage("app/main/dev/PHPCRFixtures/data/health.tv/logo.jpg");
        $programItem->setIsPublished(true);

        return $programItem;
    }

    private function returnShowCR() {
        $showCr = new \Joiz\CmsBundle\Document\Show();
        $showCr->setId(111);
        $showCr->setTitle('title');
        $showCr->setName('title');
        $showCr->setAuthor('author');
        $showCr->setLocale('en');

        return $showCr;

    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\ShowCrImporter::import
     */
    public function testImportShowExistingShow() {
        $programItem = $this->returnProgramItem();
        /** @var \Joiz\CmsBundle\Document\Show $existingShowCr */
        $existingShowCr = $this->returnShowCR();

        $this->showRepository->expects($this->any())
            ->method('getShowByExtId')
            ->with($programItem->getShowId())
            ->will($this->returnValue($existingShowCr));

        $this->showRepository->expects($this->never())->method('persist');
        $this->logger->expects($this->once())->method('log');

        $showCr = $this->showCrImporter->import($programItem);

        $this->assertEquals($programItem->getShowName(), $showCr->getTitle());
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\ShowCrImporter::import
     * @covers Joiz\EpgImporterBundle\Importer\ShowCrImporter::createShowCR
     * @covers Joiz\EpgImporterBundle\Importer\ShowCrImporter::returnUniqueShowName
     * @covers Joiz\EpgImporterBundle\Importer\ShowCrImporter::createShowName
     * @covers Joiz\EpgImporterBundle\Importer\ShowCrImporter::setDefaultRegion
     * @covers Joiz\EpgImporterBundle\Importer\ShowCrImporter::setTeaserImage
     */
    public function testImportShowCreatingShow() {
        /** @var EpgProgramItem $programItem */
        $programItem = $this->returnProgramItem();

        $this->showRepository->expects($this->any())
            ->method('getShowByExtId')
            ->with($programItem->getShowId())
            ->will($this->returnValue(NULL));

        $this->showRepository->expects($this->once())->method('persist');
        $this->logger->expects($this->once())->method('log');

        /** @var \Joiz\CmsBundle\Document\Show $show */
        $show = $this->showCrImporter->import($programItem);
        $this->assertEquals('showname', $show->getName());
        $this->assertEquals((string)$programItem->getShowId(), $show->getExtid());
        $this->assertEquals($programItem->getShowName(), $show->getBody());
        $this->assertEquals($programItem->getShowName(), $show->getTitle());
        $this->assertEquals([1], $show->getRegion());
        $this->assertEquals(1, count($show->getTeaserImage()));
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\ShowCrImporter::returnUniqueShowName
     */
    public function testImportShowWithExitingShowName() {

        $showCr = $this->returnShowCR();
        $this->showRepository
            ->expects($this->exactly(2))
            ->method('findShowByPath')
            ->withConsecutive(['title'], ['title_1'])
            ->will($this->onConsecutiveCalls($showCr, NULL));

        /** @var \Joiz\CmsBundle\Document\Show $show */
        $showName = $this->showCrImporter->returnUniqueShowName("title");
        $this->assertEquals("title_1", $showName);
    }

}