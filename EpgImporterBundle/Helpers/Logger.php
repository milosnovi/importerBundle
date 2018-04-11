<?php

namespace Joiz\EpgImporterBundle\Helpers;

use Doctrine\ORM\EntityManager;
use Joiz\HardcoreBundle\Entity\ApiMessage;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Logger for API commands able to log messages on both the console and the database
 *
 * To actually persist the messages to the DB you have to call the flush method
 */
class Logger
{
    /**
     * @var: integer
     */
    protected $importId;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array(ApiMessage)
     */
    protected $messages = array();

    /**
     * @var bool
     */
    private $success = true;

    /**
     * @var OutputInterface
     */
    private  $output;

    /**
     * @param EntityManager $entity_manager The entity manager
     */
    public function __construct(EntityManager $entity_manager)
    {
        $this->em = $entity_manager;

        if (php_sapi_name() == "cli") {
            $this->output = new ConsoleOutput();
        }
    }

    public function setImportId($importId = null)
    {
        if ($importId == null){
            $now = new \DateTime();
            $patterns = array('/:/','/-/','/ /');
            $id = preg_replace($patterns, '', $now->format('Y-m-d H:i:s'));
            $this->importId = $id;
        }
        else{
            $this->importId = $importId;
        }
    }

    /**
     * Log a message
     *
     * @param string $msg The message to log
     * @param boolean $success If true, the message indicates a success of the action it logs.
     */
    public function log($msg, $success = true)
    {
        // once we have set success to false we  can't make it true again!
        $this->success = $this->success && $success;

        if (php_sapi_name() == "cli") {
            if ($success) {
                $this->output->writeln($msg);
            } else {
                $this->output->writeln("<error>" . $msg . "</error>");
            }
        }

        // save this so it can be later written to DB via stash
        $this->messages[] = $msg;
    }

    public function getMessages() {
        return $this->messages;
    }

    /**
     * Actually writes the logged messages to the DB.
     *
     * This is not done automatically in the log method so that the persistence
     * of the messages can be achieved even if they are logged inside a rolled back
     *
     * @param string $source The source component of the message (defaults to xml-import)
     *
     * transaction.
     */
    public function flush($source)
    {
        $content = '';
        foreach ($this->messages as $message){
            $content .= '<br />'.$message;
        }

        $log = new ApiMessage();
        $log->setSource($source);
        $log->setSuccess($this->success);
        $log->setMessage(utf8_encode($content));
        $log->setTargetId($this->importId);
        $this->em->persist($log);
        $this->em->flush();
    }

}
