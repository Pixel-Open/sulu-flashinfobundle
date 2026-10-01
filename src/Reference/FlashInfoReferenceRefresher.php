<?php

declare(strict_types=1);

namespace Pixel\FlashInfoBundle\Reference;

use Pixel\FlashInfoBundle\Entity\FlashInfo;
use Pixel\FlashInfoBundle\Repository\FlashInfoRepository;
use Sulu\Bundle\ReferenceBundle\Application\Refresh\ReferenceRefresherInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class FlashInfoReferenceRefresher implements ReferenceRefresherInterface
{
    public function __construct(
        private FlashInfoReferenceProvider $flashInfoReferenceProvider,
        private FlashInfoRepository $flashInfoRepository,
        private WebspaceManagerInterface $webspaceManager,
        private string $suluContext,
    ) {
    }

    public static function getResourceKey(): string
    {
        return FlashInfo::RESOURCE_KEY;
    }

    public function refresh(): \Generator
    {
        $locales = $this->webspaceManager->getAllLocales();

        foreach ($this->flashInfoRepository->findAll() as $flashInfo) {
            foreach ($locales as $locale) {
                $flashInfo->setLocale($locale);
                $this->flashInfoReferenceProvider->updateReferences($flashInfo, $locale, $this->suluContext);
            }
            yield $flashInfo;
        }
    }
}
