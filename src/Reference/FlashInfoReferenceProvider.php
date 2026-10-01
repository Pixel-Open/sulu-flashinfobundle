<?php

declare(strict_types=1);

namespace Pixel\FlashInfoBundle\Reference;

use Pixel\FlashInfoBundle\Entity\FlashInfo;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\ReferenceBundle\Application\Collector\ReferenceCollector;
use Sulu\Bundle\ReferenceBundle\Domain\Repository\ReferenceRepositoryInterface;

class FlashInfoReferenceProvider
{
    public function __construct(
        private ReferenceRepositoryInterface $referenceRepository,
    ) {
    }

    public function updateReferences(FlashInfo $flashInfo, string $locale, string $context): void
    {
        $referenceCollector = new ReferenceCollector(
            $this->referenceRepository,
            FlashInfo::RESOURCE_KEY,
            (string) $flashInfo->getId(),
            $locale,
            mb_substr($flashInfo->getTitle() ?? '', 0, 191),
            $context,
            ['id' => $flashInfo->getId(), 'locale' => $locale],
        );

        if ($image = $flashInfo->getImage()) {
            $referenceCollector->addReference(MediaInterface::RESOURCE_KEY, (string) $image->getId(), 'image');
        }

        foreach ($flashInfo->getPdfs()['ids'] ?? [] as $id) {
            $referenceCollector->addReference(MediaInterface::RESOURCE_KEY, (string) $id, 'pdfs');
        }

        $referenceCollector->persistReferences();
        $this->referenceRepository->flush();
    }
}
