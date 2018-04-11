<?php

namespace Joiz\EpgImporterBundle\Tests\Importer;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/21/17
 * Time: 11:47
 */

use Joiz\CmsBundle\Document\Show as ShowCr;
use Joiz\CmsBundle\Document\ShowInstance;
use Joiz\EpgImporterBundle\Importer\RouteImporter;
use Joiz\HardcoreBundle\Test\JoizWebTestCase;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

class RouteImporterTest extends JoizWebTestCase
{

    private $baseRoutePath, $dm;

    /** @var  RouteImporter */
    private $routeImporter;

    protected function setUp() {

        $this->baseRoutePath = '/cms/content';

        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->setMethods(['find', 'persist'])
            ->getMock();

        $this->routeImporter = new RouteImporter(
            $this->dm,
            $this->baseRoutePath
        );
    }

    private function getShowCr() {
        $showCr = new ShowCr();
        $showCr->setName("show-name");
        return $showCr;
    }

    private function getShowInstanceCr() {
        $showCr = $this->getShowCr();
        $showInstanceCr = new ShowInstance();
        $showInstanceCr->setName("name");
        $showInstanceCr->setParentDocument($showCr);
        return $showInstanceCr;
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\RouteImporter::getPosition
     */
    public function testGetPositionNoValidInstance() {
        $position = $this->routeImporter->getPosition([]);
        $this->assertEquals($position, NULL);
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\RouteImporter::getPosition
     */
    public function testGetPositionShow() {
        $showCr = $this->getShowCr();
        $position = $this->routeImporter->getPosition($showCr);

        $this->assertEquals($position, "/cms/content/show");
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\RouteImporter::getPosition
     */
    public function testGetPositionShowInstance() {
        $showInstanceCr = $this->getShowInstanceCr();
        $position = $this->routeImporter->getPosition($showInstanceCr);

        $this->assertEquals($position, "/cms/content/show/show-name");
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\RouteImporter::setRoute
     */
    public function testSetRouteExistingRoute() {
        $show = $this->getShowCr();

        /** @var Route $existingRoute */
        $existingRoute = new Route();

        $this->dm->expects($this->any())
            ->method('find')
            ->will($this->returnValue($existingRoute));

        $this->dm->expects($this->never())->method('persist');
        $route = $this->routeImporter->setRoute($show);

        $this->assertInstanceOf("Symfony\\Cmf\\Bundle\\RoutingBundle\\Doctrine\\Phpcr\\Route", $route);
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\RouteImporter::setRoute
     */
    public function testSetRouCreateRoute() {
        $show = $this->getShowCr();

        $parentRoute = new Route();
        $parentRoute->setName('route-name');

        $this->dm->expects($this->at(0))
            ->method('find')
            ->will($this->returnValue(NULL));

        $this->dm->expects($this->at(1))
            ->method('find')
            ->will($this->returnValue($parentRoute));

        $this->dm->expects($this->once())->method('persist');
        $route = $this->routeImporter->setRoute($show);

        $this->assertInstanceOf("Symfony\\Cmf\\Bundle\\RoutingBundle\\Doctrine\\Phpcr\\Route", $route);
        $this->assertEquals($show, $route->getContent());

    }

}