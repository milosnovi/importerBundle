<?php

namespace Joiz\EpgImporterBundle\Tests\ImporterManager;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/21/17
 * Time: 11:47
 */

use Joiz\EpgImporterBundle\ImporterManager\HealthtvImporterManager;
use Joiz\EpgImporterBundle\ImporterManager\ImporterManager;
use Joiz\EpgImporterBundle\Mappers\ClientMapperInterface;
use Joiz\EpgImporterBundle\Models\EpgProgram;
use Joiz\EpgImporterBundle\Models\EpgProgramItem;
use Joiz\HardcoreBundle\Entity\Show;
use Joiz\HardcoreBundle\Entity\ShowInstance;
use Joiz\HardcoreBundle\Test\JoizWebTestCase;

class HealthtvImporterManagerTest extends JoizWebTestCase
{

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $em, $logger, $mapper, $egpProgram, $contentImporter;

    /** @var  ClientMapperInterface */
    private $healthTvMapper;

    /**
     * @var HealthtvImporterManager
     */
    protected $importManager;

    protected function setUp() {

        $this->loadFixtures(array(
            'Joiz\HardcoreBundle\Tests\Functional\Fixtures\LoadUserData',
            'Joiz\HardcoreBundle\Tests\Functional\Fixtures\LoadRegionData',
            'Joiz\HardcoreBundle\Tests\Functional\Fixtures\LoadCategoriesData'
        ));

        $container = $this->getContainer();
        $this->doctrine         = $container->get('doctrine');
        $this->em               = $container->get('doctrine.orm.entity_manager');
        $this->logger           = $container->get('epg_bundle.helper.logger');
        $this->mailer           = $container->get('joizhardcore.mailer');
        $this->showUpdater      = $container->get('epg_bundle.show_updater');
        $this->mapper           = $container->get("epg_bundle.healthTvV3Mapper");
        $this->egpProgram       = $container->get('epg_bundle.epgImporter');
        $this->contentImporter  = $container->get('epg_bundle.contentImporter');

        $this->getRegionHandlerMock();

        $this->importManager = new HealthtvImporterManager(
            $this->mapper,
            $this->doctrine,
            $this->em,
            $this->logger,
            $this->mailer,
            $this->egpProgram,
            $this->contentImporter,
            $this->showUpdater
        );
    }

    public function getEpgProgram() {
        $programItem = new EpgProgramItem();
        $programItem->setTitle('new show 1');
        $programItem->setShowId(100000);
        $programItem->setShowName('Show name');
        $programItem->setInstanceId(2000011);
        $programItem->setProgramId(2000011);
        $programItem->setIsRerun(false);
        $programItem->setIsPublished(true);
    }

    public function getHealtTvMapperData() {
        $this->tagsImporter = $this->getMockBuilder('Joiz\EpgImporterBundle\Importer\TagsImporter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->epgProgram = new EpgProgram($this->tagsImporter);

        $date = new \DateTime('now');
        $date->modify('+1 day');

        $endDate = new \DateTime('now');
        $endDate->modify("+1 day +30 minutes");

        $programItem = new EpgProgramItem();
        $programItem->setTitle('Show name episode 1');
        $programItem->setShowId(7777777777777);
        $programItem->setShowName('Importer show name');
        $programItem->setInstanceId(2000012);
        $programItem->setProgramId(2000012);
        $programItem->setIsRerun(false);
        $programItem->setTeaserText('teaser text 1');
        $programItem->setStartTime($date);
        $programItem->setEndTime($endDate);
        $programItem->setDuration(new \DateTime('2017-4-11 00:30:00'));
        $this->epgProgram->addProgramItem($programItem);

        $date1 = clone $date;
        $date1->modify('+1 day');
        $endDate1 = clone $endDate;
        $endDate1->modify('+1 day');

        $programItem = new EpgProgramItem();
        $programItem->setTitle('Show name episode 2');
        $programItem->setShowId(7777777777777);
        $programItem->setShowName('importer show name');
        $programItem->setInstanceId(2000011);
        $programItem->setProgramId(2000011);
        $programItem->setTeaserText('teaser text 2');
        $programItem->setIsRerun(true);
        $programItem->setStartTime($date1);
        $programItem->setEndTime($endDate1);
        $programItem->setDuration(new \DateTime('2017-4-11 00:30:00'));

        $this->epgProgram->addProgramItem($programItem);

        return $this->epgProgram;

    }

    /**
     * @covers Joiz\EpgImporterBundle\Manager\ImporterManager::import
     */
    public function testImport() {
//        /** @var HealthtvImporterManager $healttvImporterManager */
//        $healttvImporterManager = $this->getContainer()->get('epg_bundle.importerManager.healthtvde');
//        $healttvImporterManager->import();

//        /** @var ShowInstance $instanceNotRerun */
//        $instanceNotRerun = $showInstanceRepository->findOneBy(array('programId' => 2000011, 'isRerun' => false));
//        /** @var ShowInstance $instanceRerun */
//        $instanceRerun = $showInstanceRepository->findOneBy(array('programId' => 2000011, 'isRerun' => true));
//
//        $this->assertInstanceOf("Joiz\\HardcoreBundle\\Entity\\ShowInstance", $instanceRerun);
//        $this->assertInstanceOf("Joiz\\HardcoreBundle\\Entity\\ShowInstance", $instanceNotRerun);
    }

    /**
     * @covers Joiz\EpgImporterBundle\Manager\HealthtvImporterManager::import
     */
//    public function testHealtvImporter() {
//        $epgProgramItem = $this->getHealtTvMapperData();
//        $this->healthTvMapper = $this->getMockBuilder('Joiz\EpgImporterBundle\Mapper\HealthTvMapperV3')
//            ->disableOriginalConstructor()
//            ->setMethods(['generateEpg'])
//            ->getMock();
//
//        $this->healthTvMapper->expects($this->any())
//            ->method('generateEpg')
//            ->will($this->returnValue($epgProgramItem));
//
//        $this->importManager->import();

//        /** @var ShowInstanceRepository $showInstanceRepository */
//        $showInstanceRepository = $this->em->getRepository(ShowInstance::_CLASS);
//
//        /** @var ShowInstance $instanceNotRerun */
//        $instanceNotRerun = $showInstanceRepository->findOneBy(array('programId' => 2000011, 'isRerun' => false));
//        /** @var ShowInstance $instanceRerun */
//        $instanceRerun = $showInstanceRepository->findOneBy(array('programId' => 2000011, 'isRerun' => true));
//
//        $this->assertInstanceOf("Joiz\\HardcoreBundle\\Entity\\ShowInstance", $instanceRerun);
//        $this->assertInstanceOf("Joiz\\HardcoreBundle\\Entity\\ShowInstance", $instanceNotRerun);
//
//        $docManager = $this->getDocumentManager();
//        $contentBasePath = $this->getContentBasePath();
//
//        $showForDelete = $docManager->find(null, $contentBasePath . '/show/importer-show-name');
//        if(null != $showForDelete) {
//            $docManager->remove($showForDelete);
//        }
//        $docManager->flush();

//    }

    /**
     * @covers Joiz\EpgImporterBundle\Manager\ImporterManager::import
     */
    public function testUpdateShow() {
        $this->mapper = $this->getMockBuilder('Joiz\EpgImporterBundle\Mappers\HealthTvMapperV3')
            ->disableOriginalConstructor()
            ->setMethods(['getShows'])
            ->getMock();

        $this->mapper->expects($this->any())
            ->method('getShows')
            ->will($this->returnValue([
                '501000054' => 'milos 111',
                '501000042' => 'milos-test 5433',
            ]));

        $this->importManager->updateShows();

//        /** @var ShowInstanceRepository $showInstanceRepository */
//        $showInstanceRepository = $this->em->getRepository(ShowInstance::_CLASS);
//
//        /** @var ShowInstance $instanceNotRerun */
//        $instanceNotRerun = $showInstanceRepository->findOneBy(array('programId' => 2000011, 'isRerun' => false));
//        /** @var ShowInstance $instanceRerun */
//        $instanceRerun = $showInstanceRepository->findOneBy(array('programId' => 2000011, 'isRerun' => true));
//
//        $this->assertInstanceOf("Joiz\\HardcoreBundle\\Entity\\ShowInstance", $instanceRerun);
//        $this->assertInstanceOf("Joiz\\HardcoreBundle\\Entity\\ShowInstance", $instanceNotRerun);
    }
}