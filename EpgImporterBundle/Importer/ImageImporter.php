<?php

namespace Joiz\EpgImporterBundle\Importer;

use Symfony\Cmf\Bundle\MediaBundle\Doctrine\Phpcr\Image;
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 4/4/17
 * Time: 16:19
 */

class ImageImporter {

    /**
     * @var string
     */
    private $rootDir;

    public function __construct($rootDir) {
        $this->rootDir = $rootDir;
    }

    /**
     * @param $show
     * @return \Symfony\Cmf\Bundle\MediaBundle\Doctrine\Phpcr\Image
     */
    public function getShowInstanceTeaserImage($show) {
        $teaserImage = new Image();
        if(empty($show->getTeaserImage())) {
            return $this->getDefaultImage();
        }

        $teaserImage->setContentType('image/jpeg');
        $teaserImage->setFileContent($show->getTeaserImage()->getContentAsString());
        return $teaserImage;
    }

    /**
     * @param null $imageName
     * @return Image
     */
    public function getDefaultImage($imageName = null) {
        $imageName = $imageName ?? 'app/main/dev/PHPCRFixtures/data/logo.png';

        $teaserImage = new Image();
        $teaserImage->setFileContentFromFilesystem(sprintf("%s/../../%s", $this->rootDir, $imageName));
        return $teaserImage;
    }

    /**
     * @param string $imageUrl
     * @return Image
     */
    public function createImage($imageUrl) {
        $image = null;
        if (empty($imageUrl)) {
            return $image;
        }

        $image = file_get_contents($imageUrl);

        $teaserImage = new Image();
        $teaserImage->setFileContent($image);
        $teaserImage->setName('teaserimage.jpg');
        return $teaserImage;
    }

}