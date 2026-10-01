<?php

namespace Pixel\FlashInfoBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\FlashInfoBundle\Entity\Setting;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettingsService
{
    private TranslatorInterface $translator;
    private EntityManagerInterface $entityManager;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    /**
     * @return array<mixed>
     */
    public function getPopupPolicies(string $locale): array
    {
        return [
            [
                'name' => Setting::DO_NOT_OPEN,
                'title' => $this->translator->trans("flash_info.settings.doNotOpen", [], "admin", $locale),
            ],
            [
                'name' => Setting::OPEN_ONCE,
                'title' => $this->translator->trans("flash_info.settings.openOnce", [], "admin", $locale),
            ],
            [
                'name' => Setting::OPEN_EVERY_TIME,
                'title' => $this->translator->trans("flash_info.settings.openEveryTime", [], "admin", $locale),
            ],
        ];
    }

    public function getOrCreateSettings(): Setting
    {
        $settings = $this->entityManager->getRepository(Setting::class)->findOneBy([]);

        if (!$settings) {
            $settings = new Setting();
            $settings->setPopupPolicy(Setting::OPEN_ONCE);
            $this->entityManager->persist($settings);
            $this->entityManager->flush();
        }

        return $settings;
    }
}
