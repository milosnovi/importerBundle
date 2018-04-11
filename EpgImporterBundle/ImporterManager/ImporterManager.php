<?php

namespace Joiz\EpgImporterBundle\ImporterManager;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ORM\EntityManager;
use Joiz\CmsBundle\Document\ShowInstance;
use Joiz\EpgImporterBundle\Importer\ShowCrImporter;
use Joiz\EpgImporterBundle\Importer\ShowImporter;
use Joiz\EpgImporterBundle\Importer\ShowInstanceCrImporter;
use Joiz\EpgImporterBundle\Importer\ShowInstanceImporter;
use Joiz\EpgImporterBundle\Importer\TagsImporter;
use Joiz\EpgImporterBundle\Importer\VideoImporter;
use Joiz\EpgImporterBundle\Mappers\ClientMapperInterface;
use Joiz\EpgImporterBundle\Models\EpgProgram;
use Joiz\EpgImporterBundle\Models\EpgProgramItem;
use Joiz\EpgImporterBundle\Helpers\Logger;
use Joiz\HardcoreBundle\Notifications\Mailer;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/16/17
 * Time: 13:26
 */

class ImporterManager {

    private $doctrine;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var ShowImporter
     */
    private $showImporter;

    /**
     * @var ShowCrImporter
     */
    private $showCrImporter;

    /**
     * @var ShowInstanceCrImporter
     */
    private $showInstanceImporter;

    /**
     * @var ShowInstanceCrImporter
     */
    private $showInstanceCrImporter;

    /**
     * @var VideoImporter
     */
    private $videoImporter;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Logger
     */
    private $mailer;

    /**
     * @var ClientMapperInterface $importerMapper
     */
    private $mapper;

    /** @var  TagsImporter */
    private $tagsImporter;

    /**
     * @param $doctrine
     * @param $em EntityManager
     * @param $dm DocumentManager
     * @param ShowImporter $showImporter
     * @param ShowCrImporter $showCrImporter
     * @param ShowInstanceImporter $showInstanceImporter
     * @param ShowInstanceCrImporter $showInstanceCrImporter
     * @param VideoImporter $videoImporter
     * @param TagsImporter $tagsImporter
     * @param Logger $logger
     * @param Mailer $mailer
     * @param EpgImporter $epgImporter
     * @param ContentImporter $contentImporter
     */
    public function __construct($doctrine,
                                EntityManager $em,
                                DocumentManager $dm,
                                ShowImporter $showImporter,
                                ShowCrImporter $showCrImporter,
                                ShowInstanceImporter $showInstanceImporter,
                                ShowInstanceCrImporter $showInstanceCrImporter,
                                VideoImporter $videoImporter,
                                TagsImporter $tagsImporter,
                                Logger $logger,
                                Mailer $mailer,
                                EpgImporter $epgImporter,
                                ContentImporter $contentImporter
    ) {
        $this->doctrine                 = $doctrine;
        $this->em                       = $em;
        $this->dm                       = $dm;
        $this->showImporter             = $showImporter;
        $this->showCrImporter           = $showCrImporter;
        $this->showInstanceImporter     = $showInstanceImporter;
        $this->showInstanceCrImporter   = $showInstanceCrImporter;
        $this->videoImporter            = $videoImporter;
        $this->tagsImporter             = $tagsImporter;
        $this->logger                   = $logger;
        $this->mailer                   = $mailer;
        $this->epgImporter                   = $epgImporter;
        $this->contentImporter                   = $contentImporter;

    }

    public function setMapper($mapper) {
        $this->mapper = $mapper;
    }

    /**
     * Function check if show title has been changed and update CMS system accordingly
     */
    public function updateShows() {
        $this->em->getConnection()->beginTransaction();
        try {
            $showNames = $this->mapper->getShows();
            $this->showImporter->updateShow($showNames);
            $this->showCrImporter->updateShow($showNames);

            $this->em->getConnection()->commit();
            $this->dm->flush();
            $success = true;
        } catch (\Exception $ex) {
            $this->em->getConnection()->rollBack();
            $this->doctrine->resetManager();
            $this->logger->log('EXCEPTION: ' . $ex->getMessage() . '<br/> ' . $ex->getTraceAsString() . "\n" . $ex->getTraceAsString(), false);
            $success = false;

            $this->sendEmail($ex);
        }
        return $success;
    }


    /**
     * @return bool
     * @throws \Exception
     */
    public function importEpg() {
        $epgProgram = $this->mapper->generateEpg();
        $this->em->getConnection()->beginTransaction();

        try {
            $this->import($epgProgram);
            $epgProgram->importTags();

            $this->em->flush();
            $this->em->getConnection()->commit();
            $success = true;
        } catch (\Exception $ex) {
            $this->em->getConnection()->rollBack();
            $this->doctrine->resetManager();
            $this->logger->log('EXCEPTION: ' . $ex->getMessage() . '<br/> ' .$ex->getTraceAsString() ."\n".$ex->getTraceAsString(), false);
            $success = false;

            $this->sendEmail($ex, $epgProgram);
        }

        return $success;
    }

    /**
     * @param EpgProgram $epgProgram
     * @return bool
     */
    protected function import($epgProgram)
    {
        /** @var EpgProgramItem $programItem */
        foreach($epgProgram->getProgramItems() as $programItem) {
            $this->logger->log('~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~', true);
            $showDB = $this->showImporter->import($programItem);
            $showInstanceDB = $this->showInstanceImporter->import($programItem, $showDB);
            if($showDB) {
                $showCR = $this->showCrImporter->import($programItem);

                if ($programItem->getIsRerun()) {
                    $this->logger->log('CMS: Skip show-instance #ID= ' . $programItem->getInstanceId() . '.It is a RERUN but the instance with #ExtID='.$programItem->getProgramId().' is missing!', true);
                } else {
                    /** @var ShowInstance $instanceCR */
                    $instanceCR = $this->showInstanceCrImporter->import($programItem, $showCR);
                    if($videoCr = $this->videoImporter->import($programItem, $instanceCR)){
                        $instanceCR->setHasVideos(TRUE);
                    }
                }
                $this->dm->flush();
            }
        }
    }

    /**
     * @param $ex
     * @param $data
     */
    public function sendEmail($ex, $data = null) {
        /** @var \Swift_Mailer $mailer */
        $mailer = $this->mailer;

        $body = "Command failed with following error:" . $ex->getMessage() . "<pre>" . $ex->getTraceAsString() . "</pre><br/>Please, check data bellow:";
        if($data) {
            $epgProgramString = var_export($data, true);
            $body .= "<pre>$epgProgramString</pre>";
        }

        /** @var Swift_Message $mailerInst */
        $message = \Swift_Message::newInstance()
            ->setSubject("HMS importer fails")
            ->setFrom("noreply@levuro.com")
            ->setTo("tech@levuro.com")
            ->setBody($body, 'text/html');
        $mailer->send($message);
    }
}

