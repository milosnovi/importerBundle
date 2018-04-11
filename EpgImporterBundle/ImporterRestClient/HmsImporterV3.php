<?php

namespace Joiz\EpgImporterBundle\ImporterRestClient;

use Buzz\Browser;
use Joiz\EpgImporterBundle\Exceptions\ImporterApiResponseException;
use Joiz\EpgImporterBundle\Helpers\Logger;
use Joiz\EpgImporterBundle\Models\EpgProgram;

class HmsImporterV3 extends HmsImporter implements EpgImporterInterface {

    const timeout = 120;

//    const epgURL = '/hmsWSBroadcast/api/v3/show/GermanHealthTV?Part=Instances&Part=Metadata&OriginalID=0&ChannelID=2';
    const epgURL = '/hmsWSBroadcast/api/v3/show/GermanHealthTV?Part=Instances&Part=Metadata&OriginalID=0';

    const contentURL = '/hmsWSBroadcast/api/v3/show/GermanHealthTV?Part=Metadata';

    protected $host = '89.1.5.230';

    protected $protocol = 'http';

    public function __construct(Browser $buzzBrowser, Logger $logger)
    {
        parent::__construct($buzzBrowser, $logger);
    }

    /**
     * @return EpgProgram
     * @throws \Exception
     */
    public function getEpg()
    {
        try {
            return $this->fetchEPGdata();
        } catch(\Exception $e){
            throw new ImporterApiResponseException($e->getMessage());
        }
    }

    public function getContent()
    {
        try {
            return $this->fetchContent();
        } catch(\Exception $e){
            throw new ImporterApiResponseException($e->getMessage());
        }
    }


    private function fetchEPGdata()
    {
        $accessToken = $this->getAccessToken();

        /** @var  \DateTime $date */
        $date = new \DateTime();
        $date->modify('-7 day');
        $timeFrame = $date->format('Y-m-d\TH:i:s\Z');

        $headers = ["Content-Type" => "application/json", "Access-Token" => $accessToken];
        $url = sprintf("%s%s&UTCBegin=%s", $this->url, self::epgURL, $timeFrame);

        $jsonResult = $this->buzzBrowser->get(
            $url,
            $headers
        );
        $results = json_decode($jsonResult->getContent(), true);

        if(isset($results['Sources'])) {
            return $results['Sources'];
        }

        throw new \Exception("Invalid EPG response . " . $jsonResult->getContent());
//        throw new \Exception("Invalid EPG response . ");
    }

    private function fetchContent()
    {
        $accessToken = $this->getAccessToken();

        $headers = ["Content-Type" => "application/json", "Access-Token" => $accessToken];
        $url = sprintf("%s%s", $this->url,self::contentURL);

        $jsonResult = $this->buzzBrowser->get(
            $url,
            $headers
        );
        $results = json_decode($jsonResult->getContent(), true);

        if(isset($results['Sources'])) {
            return $results['Sources'];
        }

        throw new \Exception("Invalid EPG response . " . $jsonResult->getContent());
    }
}