<?php

declare(strict_types=1);

namespace Pixel\FlashInfoBundle\Content\Type;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\FlashInfoBundle\Entity\FlashInfo;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class SingleFlashInfoSelection extends SimpleContentType
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct('single_flash-info_selection', null);
    }

    public function getContentData(PropertyInterface $property): ?FlashInfo
    {
        $id = $property->getValue();

        if (empty($id)) {
            return null;
        }

        return $this->entityManager->getRepository(FlashInfo::class)->find($id);
    }

    /**
     * @return array<string, int|null>
     */
    public function getViewData(PropertyInterface $property): array
    {
        return [
            'id' => $property->getValue(),
        ];
    }
}
