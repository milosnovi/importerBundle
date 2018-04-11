<?php

namespace Joiz\EpgImporterBundle\Importer;

use Joiz\EpgImporterBundle\Helpers\Logger;
use Joiz\HardcoreBundle\Entity\Categories;
use Joiz\HardcoreBundle\Repository\CategoriesRepository;

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 4/4/17
 * Time: 17:16
 */

class TagsImporter {

    /**
     * @var CategoriesRepository
     */
    private $categoriesRepository;

    /**
     * @var Logger
     */
    private $logger;


    /**
     * @param CategoriesRepository $categoriesRepository
     * @param Logger $logger
     */
    public function __construct(CategoriesRepository $categoriesRepository,
                                Logger $logger)
    {
        $this->categoriesRepository = $categoriesRepository;
        $this->logger = $logger;
    }

    /**
     * @param array $tags
     *
     */
    public function import($tags) {
        if(empty($tags)) {
            $this->logger->log('TAGS: There are no new tags', true);
            return;
        }

        $existingCategories = $this->categoriesRepository->findBy(['categoryName' => $tags]);

        foreach($tags as $tag) {
            if($this->insertNewTag($existingCategories, $tag)) {
                $this->logger->log('TAGS: New tag:' . $tag . 'has been added', true);
                $this->categoriesRepository->createNewEditorTag($tag);
            }

        }
    }

    public function insertNewTag($existingCategories, $tag) {
        /** @var Categories $category */
        foreach($existingCategories as $category) {
            if($category->getCategoryName() == $tag) {
                return false;
            }
        }
        return true;
    }

}