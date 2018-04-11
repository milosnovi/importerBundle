<?php

namespace Joiz\EpgImporterBundle\Importer;

use Joiz\CmsBundle\Document\Show;
use Joiz\CmsBundle\Document\ShowInstance;
use Joiz\CmsBundle\Document\Video;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Doctrine\ODM\PHPCR\DocumentManager;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 4/4/17
 * Time: 15:36
 */

class RouteImporter {
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var string
     */
    private $baseRoutePath;


    /**
     * RouteImporter constructor.
     * @param DocumentManager $dm
     * @param $baseRoutePath string
     */
    public function __construct(DocumentManager $dm,
                                $baseRoutePath) {
        $this->dm = $dm;
        $this->baseRoutePath = $baseRoutePath;
    }

    /**
     * @param $content
     * @return null|string
     */
    public function getPosition($content) {
        if($content instanceof Show) {
            return sprintf("%s/show", $this->baseRoutePath);
        }

        if($content instanceof ShowInstance) {
            return sprintf("%s/show/%s", $this->baseRoutePath, $content->getParentDocument()->getName());
        }

        if($content instanceof Video) {
            /** @var ShowInstance $instance */
            $instance = $content->getParentDocument();
            $instanceName = $instance->getName();

            /** @var Show $show */
            $show = $instance->getParentDocument();
            $showName = $show->getName();

            return sprintf("%s/show/%s/%s", $this->baseRoutePath, $showName, $instanceName);
        }

        return NULL;
    }

    /**
     * @param $content
     * @return Route
     */
    public function setRoute($content) {
        $position = $this->getPosition($content);
        $name = $content->getName();

        $existingRoute = $this->dm->find(null, $position . '/' . $name);
        if(!empty($existingRoute)) {
            return $existingRoute;
        }

        $showRoute = new Route();
        $showRoute->setPosition($this->dm->find(null, $position), $name);
        $showRoute->setContent($content);
        $this->dm->persist($showRoute);
        return $showRoute;
    }
}