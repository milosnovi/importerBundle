<?php

namespace Joiz\EpgImporterBundle\ImporterRestClient;

use Buzz\Browser;
use Joiz\EpgImporterBundle\Helpers\Logger;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/16/17
 * Time: 12:01
 */

class HmsImporter implements EpgImporterInterface {

    // request timeout in seconds
    const timeout = 120;

    const authenticateURL = '/hmsWSBroadcast/api/login';

    const epgURL = '/hmsWSBroadcast/api/show/GermanHealthTV?GetMetadata=1&order=desc&GetClip=1&ChannelID=2';


    const AuthUsername = 'levuro';

    const AuthPassword = 'L3vUr0';

    /**
     * @var Browser $browser
     */
    protected $buzzBrowser;

    /**
     * @var Logger $logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $accessToken = null;

    /**
     * @var array
     */
    protected $metaDatTypes = [];

    /**
     * @var array
     */
    protected $showNames = [];

    /**
     * @var array
     */
    protected $episodeNumberNames = [];

    protected $host = '62.67.13.54';

    protected $protocol = 'https';

    protected $url;

    public function __construct(Browser $buzzBrowser, Logger $logger)
    {
        $this->buzzBrowser = $buzzBrowser;
        $this->logger = $logger;
        $this->buzzBrowser->getClient()->setVerifyPeer(false);
        $this->buzzBrowser->getClient()->setVerifyHost(false);
        $this->buzzBrowser->getClient()->setTimeout(self::timeout);

        $this->url = sprintf("%s://%s", $this->protocol, $this->host);
    }

    /**
     * @return mixed
     */
    public function getEpg()
    {
        try {
            return $this->fetchEPGdata();
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage(), false);
        }
    }

    public function getShowNames()
    {
        if(!empty($this->showNames)) {
            return $this->showNames;
        }

        $showIdentifier = $this->getFieldType("Format");

        try {
            $this->showNames = $this->fetchMetaDataValue($showIdentifier);
            return $this->showNames;
        }catch(\Exception $e){
            $this->logger->log($e->getMessage(), false);
        }
    }

    public function getEpisodeNumberName()
    {
        try {
            $episodeNumberType = $this->getFieldType("Episode");
            $this->episodeNumberNames = $this->fetchMetaDataValue($episodeNumberType);
            return $this->episodeNumberNames;
        }catch(\Exception $e){
            $this->logger->log($e->getMessage(), false);
        }
    }

    public function getMetaDataType()
    {
        try {
            $this->metaDatTypes = $this->fetchMetaDataType();
            return $this->metaDatTypes;
        }catch(\Exception $e){
            $this->logger->log($e->getMessage(), false);
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getAccessToken()
    {
        if(!empty($this->accessToken)) {
            return $this->accessToken;
        }

        $headers = ["Content-Type" => "application/json"];
        $content = json_encode([
            'Username' => self::AuthUsername,
            'Password' => self::AuthPassword
        ]);

        $url = sprintf("%s/%s", $this->url, self::authenticateURL);

        $jsonResult = $this->buzzBrowser->post(
            $url,
            $headers,
            $content
        );
        $results = json_decode($jsonResult->getContent());
        if($results->AccessToken) {
            $this->accessToken = $results->AccessToken;
            return $this->accessToken;
        }
        throw new \Exception("Cannot authenticate to HMS system. ". $jsonResult->getContent());
    }

    private function fetchEPGdata()
    {
        $accessToken = $this->getAccessToken();

        /** @var  \DateTime $date */
        $date = new \DateTime();
        $date->modify('-7 day');
        $timeFrame = $date->format('Y-m-d\TH:i:s\Z');

        $headers = ["Content-Type" => "application/json", "Access-Token" => $accessToken];

        $url = sprintf("%s/%s&UTCBegin=%s", $this->url, self::epgURL, $timeFrame);
        $jsonResult = $this->buzzBrowser->get(
            $url,
            $headers
        );
        $results = json_decode($jsonResult->getContent(), true);
        if($results['sources']){
            return $results['sources'];
        }

        throw new \Exception("Invalid EPG ponse . ". $jsonResult->getContent());
    }

    private function fetchMetaDataValue($type)
    {
        $accessToken = $this->getAccessToken();
        $headers = ["Content-Type" => "application/json", "Access-Token" => $accessToken];

        $metaDataUrl = "/hmsWSBroadcast/api/metadataenum/GermanHealthTV?GetMetadata=1&MetadataTypeID=";
        $jsonResult = $this->buzzBrowser->get(
            sprintf("%s%s%s", $this->url, $metaDataUrl, $type),
            $headers
        );
        $results = json_decode($jsonResult->getContent(), true);
        if($results['Sources']){
            return $results['Sources'];
        }

        throw new \Exception("Invalid  response . ". $jsonResult->getContent());
    }

    private function fetchMetaDataType()
    {
        $accessToken = $this->getAccessToken();
        $headers = ["Content-Type" => "application/json", "Access-Token" => $accessToken];

        $metaDataTypeUrl = "/hmsWSBroadcast/api/metadatatype/GermanHealthTV";
        $jsonResult = $this->buzzBrowser->get(
            sprintf("%s%s", $this->url, $metaDataTypeUrl),
            $headers
        );

        $results = json_decode($jsonResult->getContent(), true);
        if($results['Sources']){
            return $results['Sources'];
        }

        throw new \Exception("Invalid  response . ". $jsonResult->getContent());
    }

    private function getFieldType($type)
    {
        if(empty($this->metaDatTypes)) {
            $this->metaDatTypes = $this->fetchMetaDataType();
        }

        foreach($this->metaDatTypes as $metaDataType) {
            if($type == $metaDataType["Name"] &&  2000051 == $metaDataType["MetadataTypeID"]) {
                return $metaDataType["ID"];
            }
        }
    }
//        $jsonResult = '[{
//            "ID": 2000011,
//            "Name": "German Health",
//            "Slug": "German Health",
//            "UTCBegin": "2017-04-05T12:15:00.000Z",
//            "UTCEnd": "2017-04-05T13:15:00.000Z",
//            "ChannelID": 1,
//            "Created": "2017-04-05T14:12:17.000Z",
//            "CreatedBy": "GP @ HMS-HAM02-GH03, WINUSER: Administrator DBUSER: =disaadmin",
//            "Modified": "2017-04-05T14:12:17.000Z",
//            "ModifiedBy": "GP @ HMS-HAM02-GH03, WINUSER: Administrator DBUSER: =disaadmin",
//            "Category": "Online",
//            "ParentID": 2000011,
//            "SA_INGEST": false,
//            "SA_REGISTERINGEST": false,
//            "SA_RELEASED": true,
//            "ThirdPartyID": "",
//            "KeySets": [
//            {
//              "EPG": {
//                "Teaser": "German Health Show",
//                "Description": "German Health 2017",
//                "Episode": "German Health",
//                "Parental Code": "0",
//                "Category": "20",
//                "CharCode": "0",
//                "LanguageCode": "0",
//                "Time": "2017-04-05T12:15:00Z",
//                "Duration": "01:00:00"
//            },
//              "Online_Website": {
//                "Title": "Und immer lockt die Loipe",
//                "Teaser": "Begleiten sie Langlauflegende Georg Zipfel im Karwendel",
//                "Description": "Skilanglauf gehört zu den gesündesten Sportarten überhaupt. Und es gibt keine Altersgrenze. Wir begleiten den in der Langlaufwelt bekannten Georg Zipfel, der im Karwendel für den Nachwuchs eine Trainingsloipe präpariert. Zwei Hobbylangläufer, die alljährlich gemeinsamen die malerische Schneelandschaft im Harz mit ihren Skiern erwandern – und eine Gruppe, die in nur zwei Tagen lernen, durch unberührte Landschaften zu gleiten.",
//                "Publishing date": "2017-04-25T22:00:00Z",
//                "Exiry date": "2017-12-31T19:00:00Z",
//                "Format": "1",
//                "Episode": "1"
//            },
//              "Ooyala_Data_In": {
//                "Title": "German Health",
//                "Teaser": "German Health teasertext",
//                "Description": "German Health bodytext"
//              }
//            }
//            ],
//            "Color": 12623872,
//            "DownloadURL": null
//        }, {
//            "ID": 2000012,
//            "Name": "German Health",
//            "Slug": "German Health",
//            "UTCBegin": "2017-04-05T13:15:00.000Z",
//            "UTCEnd": "2017-04-05T14:15:00.000Z",
//            "ChannelID": 1,
//            "Created": "2017-04-05T14:12:17.000Z",
//            "CreatedBy": "GP @ HMS-HAM02-GH03, WINUSER: Administrator DBUSER: =disaadmin",
//            "Modified": "2017-04-05T14:12:17.000Z",
//            "ModifiedBy": "GP @ HMS-HAM02-GH03, WINUSER: Administrator DBUSER: =disaadmin",
//            "Category": "Online",
//            "ParentID": 2000012,
//            "SA_INGEST": false,
//            "SA_REGISTERINGEST": false,
//            "SA_RELEASED": true,
//            "ThirdPartyID": "",
//            "KeySets": [
//            {
//              "EPG": {
//                "Teaser": "German Health Show",
//                "Description": "German Health 2017",
//                "Episode": "German Health",
//                "Parental Code": "0",
//                "Category": "20",
//                "CharCode": "0",
//                "LanguageCode": "0",
//                "Time": "2017-04-05T12:15:00Z",
//                "Duration": "01:00:00"
//              }
//            ,
//              "Online_Website": {
//                "Title": "Und immer lockt die Loipe",
//                "Teaser": "Begleiten sie Langlauflegende Georg Zipfel im Karwendel",
//                "Description": "Skilanglauf gehört zu den gesündesten Sportarten überhaupt. Und es gibt keine Altersgrenze. Wir begleiten den in der Langlaufwelt bekannten Georg Zipfel, der im Karwendel für den Nachwuchs eine Trainingsloipe präpariert. Zwei Hobbylangläufer, die alljährlich gemeinsamen die malerische Schneelandschaft im Harz mit ihren Skiern erwandern – und eine Gruppe, die in nur zwei Tagen lernen, durch unberührte Landschaften zu gleiten.",
//                "Publishing date": "2017-04-25T22:00:00Z",
//                "Exiry date": "2017-12-31T19:00:00Z",
//                "Format": "1",
//                "Episode": "2"
//            },
//              "Ooyala_Data_In": {
//                "Title": "German Health",
//                "Teaser": "German Health teasertext",
//                "Description": "German Health bodytext"
//              }
//            }
//            ],
//            "Color": 12623872,
//            "DownloadURL": null
//        }]';
//        return json_decode($jsonResult, true);
}