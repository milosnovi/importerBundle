<?php

namespace Joiz\EpgImporterBundle\Tests\Mappers;

use Joiz\EpgImporterBundle\Mappers\HealthTvMapper;
use Joiz\EpgImporterBundle\Mappers\HealthTvMapperV3;
use Joiz\HardcoreBundle\Test\JoizWebTestCase;

class HealthTvMapperTest extends JoizWebTestCase
{

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $epgProgram, $importer, $validator;

    /**
     * @var HealthTvMapper
     */
    private $healthTvMapper;

    protected function setUp() {

        $this->epgProgram = $this->getMockBuilder('Joiz\EpgImporterBundle\Models\EpgProgram')
            ->disableOriginalConstructor()
            ->getMock();

        $this->importer = $this->getMockBuilder('Joiz\EpgImporterBundle\ImporterRestClient\HmsImporterV3')
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = $this->getMockBuilder('Symfony\Component\Validator\Validator\RecursiveValidator')
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();

        $this->logger = $this->getMockBuilder('Joiz\EpgImporterBundle\Helpers\Logger')
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();


        $this->healthTvMapper = new HealthTvMapperV3(
                $this->epgProgram,
                $this->importer,
                $this->validator,
                $this->logger
        );

        $reflector = new \ReflectionClass( 'Joiz\EpgImporterBundle\Mappers\HealthTvMapperV3' );
        $prop = $reflector->getProperty( 'programItem' );
        $prop->setAccessible(true);
        $prop->setValue($this->healthTvMapper, $this->programItemData );

    }

    private $programItemData = [
        "ID" => 2000011,
        "Name" => "German Health",
        "Slug" => "German Health",
        "UTCBegin" => "2017-04-05T12:15:00.000Z",
        "UTCEnd" => "2017-04-05T13:15:00.000Z",
        "ChannelID" => 1,
        "Created" => "2017-04-05T14:12:17.000Z",
        "CreatedBy" => "GP @ HMS-HAM02-GH03, WINUSER: Administrator DBUSER: =disaadmin",
        "Modified" => "2017-04-05T14:12:17.000Z",
        "ModifiedBy" => "GP @ HMS-HAM02-GH03, WINUSER: Administrator DBUSER: =disaadmin",
        "Category" => "Online",
        "ParentID" => 2000011,
        "SA_INGEST" => false,
        "SA_REGISTERINGEST" => false,
        "SA_RELEASED" => 1,
        "ThirdPartyID" => "100",
        "KeySets" => [
            [
                "EPG" => [
                    "Teaser" => "German Health Show",
                    "Description" => "German Health 2017",
                    "Episode" => "German Health",
                    "Parental Code" => 0,
                    "Category" => 20,
                    "CharCode" => 0,
                    "LanguageCode" => 0,
                    "Time" => "2017-04-05T12:15:00Z",
                    "Duration" => "01:00:00"
                ],
                "Online_Website" => [
                    "Title" => "Title",
                    "Format" => "1"
                ],
                "Ooyala_Data" => [
                    "Ooyala_Data_status" => "Live",
                    "Ooyala_Data_embed_code" => "XXXXXXXX",
                    "Ooyala_Data_item_name" => "Video name",
                    "Ooyala_Data_preview_image_url" => "Video image"
                ]
            ]
        ],
        "Color" => "12623872",
        "DownloadURL" => ""
    ];


    /**
     * @covers Joiz\EpgImporterBundle\Mappers\HealthTvMapper::getVideoId
     */
    public function testGetVideoId()
    {
        /** @var \DateTime $duration */
        $videoId = $this->healthTvMapper->getVideoId();
        $this->assertEquals("XXXXXXXX", $videoId);
    }

    /**
     * @covers Joiz\EpgImporterBundle\Mappers\HealthTvMapper::getVideoId
     */
    public function testGetVideoName()
    {
        /** @var \DateTime $duration */
        $videoId = $this->healthTvMapper->getVideoName();
        $this->assertEquals("video", $videoId);
    }

    /**
     * @covers Joiz\EpgImporterBundle\Mappers\HealthTvMapper::getDuration
     */
    public function testGetterDuration()
    {
        /** @var \DateTime $duration */
        $duration = $this->healthTvMapper->getDuration();
        $this->assertInstanceOf('\DateTime', $duration);
        $this->assertEquals("01:00:00", $duration->format('H:i:s'));
    }

    /**
     * @covers Joiz\EpgImporterBundle\Mappers\HealthTvMapper::getStartTime
     */
    public function testGetterStarTime()
    {
        /** @var \DateTime $starTime */
        $tartTime = $this->healthTvMapper->getStartTime();
        $this->assertInstanceOf('\DateTime', $tartTime);
        $this->assertEquals(new \DateTime("2017-04-05T12:15:00.000Z"), $tartTime);
    }

    /**
     * @covers Joiz\EpgImporterBundle\Mappers\HealthTvMapper::getEndTime
     */
    public function testGetterEndTime()
    {
        /** @var \DateTime $endTime */
        $endTime = $this->healthTvMapper->getEndTime();
        $this->assertInstanceOf('\DateTime', $endTime);
        $this->assertEquals(new \DateTime("2017-04-05T13:15:00.000Z"), $endTime);
    }

    /**
     * @covers Joiz\EpgImporterBundle\Mappers\HealthTvMapper::getTitle
     */
    public function testGetTitle()
    {
        /** @var string $title */
        $title = $this->healthTvMapper->getTitle();
        $this->assertEquals('Title', $title);
    }

    /**
     * @covers Joiz\EpgImporterBundle\Mappers\HealthTvMapper::getShowId
     */
    public function testGetShowId()
    {
        /** @var int $showId */
        $showId = $this->healthTvMapper->getShowId();
        $this->assertEquals(1, $showId);
    }


    /**
     * @covers Joiz\EpgImporterBundle\Mappers\HealthTvMapper::getInstanceId
     */
    public function testGetInstanceId()
    {
        $instanceId = $this->healthTvMapper->getInstanceId();
        $this->assertEquals(2000011, $instanceId);
    }

    /**
     * @covers Joiz\EpgImporterBundle\Mappers\HealthTvMapper::getTitle
     * @covers Joiz\EpgImporterBundle\Mappers\HealthTvMapper::getShowId
     * @covers Joiz\EpgImporterBundle\Mappers\HealthTvMapper::getInstanceId
     * @covers Joiz\EpgImporterBundle\Mappers\HealthTvMapper::getStartTime
     * @covers Joiz\EpgImporterBundle\Mappers\HealthTvMapper::getEndTime
     */
    public function testGetterWithWrongValue() {
        $reflector = new \ReflectionClass( 'Joiz\EpgImporterBundle\Mappers\HealthTvMapperV3' );
        $prop = $reflector->getProperty( 'programItem' );
        $prop->setAccessible( true );
        $prop->setValue( $this->healthTvMapper, []);

        $this->assertEquals("", $this->healthTvMapper->getTitle());
        $this->assertEquals(NULL , $this->healthTvMapper->getShowId());
        $this->assertEquals(NULL, $this->healthTvMapper->getInstanceId());

        $this->assertEquals(NULL, $this->healthTvMapper->getStartTime());
        $this->assertEquals(NULL, $this->healthTvMapper->getEndTime());

    }

}
