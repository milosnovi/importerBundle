<?php

namespace Joiz\EpgImporterBundle\ImporterManager;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ORM\EntityManager;
use Joiz\CmsBundle\Document\ShowInstance;
use Joiz\EpgImporterBundle\Exceptions\ImporterApiResponseException;
use Joiz\EpgImporterBundle\Importer\ShowUpdater;
use Joiz\EpgImporterBundle\ImporterManager\ContentImporter;
use Joiz\EpgImporterBundle\ImporterManager\EpgImporter;
use Joiz\EpgImporterBundle\Mappers\ClientMapperInterface;
use Joiz\EpgImporterBundle\Mappers\HealthTvMapperV3;
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

class HealthtvImporterManager {

    private $doctrine;

    /**
     * @var EntityManager
     */
    private $em;

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

    /**
     * @var  EpgImporter
     */
    private $epgImporter;

    /**
     * @var  ContentImporter
     */
    private $contentImporter;

    /**
     * @var ShowUpdater
     */
    private $showUpdater;
    /**
     * @param HealthTvMapperV3 $mapper
     * @param $doctrine
     * @param $em EntityManager
     * @param Logger $logger
     * @param Mailer $mailer
     * @param EpgImporter $epgImporter
     * @param ContentImporter $contentImporter
     * @param ShowUpdater $showUpdater
     */
    public function __construct(HealthTvMapperV3 $mapper,
                                $doctrine,
                                EntityManager $em,
                                Logger $logger,
                                Mailer $mailer,
                                EpgImporter $epgImporter,
                                ContentImporter $contentImporter,
                                ShowUpdater $showUpdater
    ) {
        $this->mapper                   = $mapper;
        $this->doctrine                 = $doctrine;
        $this->em                       = $em;
        $this->epgImporter              = $epgImporter;
        $this->contentImporter          = $contentImporter;
        $this->logger                   = $logger;
        $this->mailer                   = $mailer;
        $this->showUpdater              = $showUpdater;
    }

    /**
     * Function check if show title has been changed and update CMS system accordingly
     */
    public function updateShows() {
        $showNames = $this->mapper->getShows();
        $this->em->getConnection()->beginTransaction();
        try {
            $this->showUpdater->update($showNames);
            $this->em->getConnection()->commit();
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


    public function import() {
        $this->importEpg();
        $this->importContent();
    }

    private function importEpg() {
        $this->em->getConnection()->beginTransaction();
        try {
            $this->logger->log("<h1>IMPORTING EPG DATA</h1>", true);
            $epgProgram = $this->mapper->generateEpg();

            $this->epgImporter->import($epgProgram);

            $this->logger->log("<h1>IMPORTING TAGS</h1>", true);
            $epgProgram->importTags();

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ImporterApiResponseException $exImporter) {
            $this->logger->log('EXCEPTION: THERE IS NO DATA FROM IMPORTER ' . $exImporter->getMessage(), false);
        } catch (\Exception $ex) {
            $this->em->getConnection()->rollBack();
            $this->doctrine->resetManager();
            $this->logger->log('EXCEPTION: ' . $ex->getMessage() . '<br/> ' .$ex->getTraceAsString() ."\n".$ex->getTraceAsString(), false);

            $this->sendEmail($ex, $epgProgram);
        }
    }

    private function importContent() {
        try {
            $this->logger->log("<h1>IMPORTING CONTENT</h1>", true);
            $content = $this->mapper->generateContent();
            $this->contentImporter->import($content);
        } catch (ImporterApiResponseException $exImporter) {
            $this->logger->log('EXCEPTION: THERE IS NO DATA FROM IMPORTER ' . $exImporter->getMessage(), false);
        } catch (\Exception $ex) {
            $this->logger->log('EXCEPTION WHILE INSERTING CONTENT: ' . $ex->getMessage() . '<br/> ' .$ex->getTraceAsString() ."\n".$ex->getTraceAsString(), false);
            $this->sendEmail($ex, $content);
        }
    }

    /**
     * @param $ex
     * @param $data
     */
    private function sendEmail($ex, $data = null) {
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

