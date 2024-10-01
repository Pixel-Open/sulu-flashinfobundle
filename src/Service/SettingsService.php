<?php

namespace Pixel\FlashInfoBundle\Service;

use Pixel\FlashInfoBundle\Entity\Setting;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettingsService
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
}
