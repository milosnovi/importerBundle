<?php

namespace Joiz\EpgImporterBundle\Tests\Importer;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 3/21/17
 * Time: 11:47
 */

use Joiz\CmsBundle\Document\Standard;
use Joiz\EpgImporterBundle\Importer\TagsImporter;
use Joiz\HardcoreBundle\Entity\Categories;
use Joiz\HardcoreBundle\Entity\Region;
use Joiz\HardcoreBundle\Entity\Show;
use Joiz\HardcoreBundle\Test\JoizWebTestCase;
use Symfony\Cmf\Bundle\MediaBundle\Doctrine\Phpcr\Image;

class TagsImporterTest extends JoizWebTestCase
{

    private $categoriesRepository, $logger;

    /** @var TagsImporter  */
    private $tagsImporter;

    protected function setUp() {

        $this->logger = $this->getMockBuilder('Joiz\EpgImporterBundle\Helpers\Logger')
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();

        $this->categoriesRepository = $this->getMockBuilder('Joiz\HardcoreBundle\Repository\CategoriesRepository')
            ->disableOriginalConstructor()
            ->setMethods(['findBy', 'createNewEditorTag'])
            ->getMock();

        $this->tagsImporter = new TagsImporter(
            $this->categoriesRepository,
            $this->logger
        );
    }

    private function returnExistingCategories() {
        $tag1 = new Categories();
        $tag1->setCategoryName('Music');
        $tag1->setType(Categories::TYPE_EDITOR_TAG);

        $tag2 = new Categories();
        $tag2->setCategoryName('People');
        $tag2->setType(Categories::TYPE_EDITOR_TAG);

        return [$tag1, $tag2];
    }

    private function returnNewCategories() {
        $tag1 = new Categories();
        $tag1->setCategoryName('Sport');
        $tag1->setType(Categories::TYPE_EDITOR_TAG);

        $tag2 = new Categories();
        $tag2->setCategoryName('Movie');
        $tag2->setType(Categories::TYPE_EDITOR_TAG);

        return [$tag1, $tag2];
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\TagsImporter::import
     * @covers Joiz\EpgImporterBundle\Importer\TagsImporter::insertNewTag
     */
    public function testImportWitNewTags() {

        $this->categoriesRepository->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue([]));

        $this->categoriesRepository
            ->expects($this->exactly(2))
            ->method('createNewEditorTag');

        $this->tagsImporter->import(['Music', 'People']);
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\TagsImporter::import
     * @covers Joiz\EpgImporterBundle\Importer\TagsImporter::insertNewTag
     */
    public function testImportWitouthNewTags() {

        $this->categoriesRepository->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue($this->returnExistingCategories()));

        $this->categoriesRepository
            ->expects($this->never())
            ->method('createNewEditorTag');

        $this->tagsImporter->import(['Music', 'People']);
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\TagsImporter::import
     * @covers Joiz\EpgImporterBundle\Importer\TagsImporter::insertNewTag
     */
    public function testImportWitMixedTags() {

        $this->categoriesRepository->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue($this->returnExistingCategories()));

        $this->categoriesRepository
            ->expects($this->exactly(2))
            ->method('createNewEditorTag');

        $this->logger
            ->expects($this->exactly(2))
            ->method('log');

        $this->tagsImporter->import(['Music', 'Sport', 'Movie', 'People']);
    }

    /**
     * @covers Joiz\EpgImporterBundle\Importer\TagsImporter::import
     */
    public function testImportWitNoArgumentTags() {
        $this->categoriesRepository
            ->expects($this->never())
            ->method('createNewEditorTag');

        $this->logger
            ->expects($this->exactly(1))
            ->method('log');

        $this->tagsImporter->import([]);
    }

}