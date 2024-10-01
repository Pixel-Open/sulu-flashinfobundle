<?php

namespace Pixel\FlashInfoBundle\Content\Type;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\FlashInfoBundle\Entity\FlashInfo;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class FlashInfoSelection extends SimpleContentType
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct("flash-info_selection", []);
    }

    /**
     * @return FlashInfo[]
     */
    public function getContentData(PropertyInterface $property)
    {
        $ids = $property->getValue();

        if (empty($ids)) {
            return [];
        }

        $flashInfos = $this->entityManager->getRepository(FlashInfo::class)->findBy([
            'id' => $ids,
        ]);

        $idPositions = array_flip($ids);
        usort($flashInfos, function (FlashInfo $a, FlashInfo $b) use ($idPositions) {
            return $idPositions[$a->getId()] - $idPositions[$b->getId()];
        });

        return $flashInfos;
    }

    /**
     * @return array<string, array<int>|null>
     */
    public function getViewData(PropertyInterface $property): array
    {
        return [
            'ids' => $property->getValue(),
        ];
    }
}
