<?php

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 4/6/17
 * Time: 14:04
 */

namespace Joiz\EpgImporterBundle\Command;

use Joiz\EpgImporterBundle\ImporterManager\HealthtvImporterManager;
use Joiz\HardcoreBundle\Entity\ApiMessage;
use Joiz\HardcoreBundle\Command\DomainContainerAwareCommand;
use Joiz\EpgImporterBundle\Helpers\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Import a schedule xml into content repository and database
 *
 * Class HmsXmlImportCommand
 * @package Joiz\HmsBundle\Command
 */
class EpgImporterCommand extends DomainContainerAwareCommand
{

    const COMMAND_NAME = 'api:epg-import';

    /** @var $logger Logger */
    protected $logger = null;

    /** @var string */
    protected $clientName;

    protected function configure()
    {
        parent::configure();
        $this
            ->addOption('clientName', null, InputOption::VALUE_REQUIRED, 'ClientName is required')
            ->addOption('id', null, InputOption::VALUE_OPTIONAL, 'Unique ID of the import')
            ->setName(self::COMMAND_NAME)
            ->setDescription('Parse and import XML');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();

        $patterns = array('/:/','/-/','/ /');
        $now = new \DateTime();
        $targetId = preg_replace($patterns, '', $now->format('Y-m-d H:i:s'));

        /** @var Logger $logger */
        $logger = $container->get("epg_bundle.helper.logger");
        $logger->setImportId($targetId);

//        //@TODO manage clients
//        $this->clientName = "healthTvV3"; //$input->getOption('clientName');
//        /** @var ClientMapperInterface $importerMapper */
//        $importerMapper = $container->get($this->clientName."Mapper");

        /** @var  HealthtvImporterManager $importerManager */
        $importerManager = $container->get('epg_bundle.importerManager.healthtvde');
        $importerManager->import();
        $importerManager->updateShows();

        // Write the logger data to the DB
        $logger->flush(ApiMessage::SOURCE_HMS_IMPORTER);
    }
}