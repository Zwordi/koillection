<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Repository\CollectionRepository;
use App\Service\InventoryHandler;
use Symfony\Component\Form\DataTransformerInterface;

class StringToInventoryContentTransformer implements DataTransformerInterface
{
    public function __construct(
        private InventoryHandler $inventoryHandler,
        private CollectionRepository $collectionRepository
    ) {}

    public function transform($content): mixed
    {
        return '';
    }

    public function reverseTransform($string): mixed
    {
        if ($string === null) {
            return json_encode([]);
        }

        $ids = explode(',', $string);
        $collections = $this->collectionRepository->findAllWithItems();
        $content = $this->inventoryHandler->buildInventory($collections, $ids);

        return json_encode($content);
    }
}
