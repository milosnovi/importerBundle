<?php

namespace Joiz\EpgImporterBundle\Exceptions;

class ImporterApiResponseException extends \Exception
{
    /**
     * Constructor.
     *
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     */
    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct($message, 500, $previous);
    }
}
