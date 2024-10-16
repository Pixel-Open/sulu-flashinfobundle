<?php

namespace Pixel\FlashInfoBundle\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlashInfoExtension extends AbstractExtension
{
    private Environment $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('auto_display_flash_info_modal', [$this, 'autoDisplayFlashInfoModal'], [
                'is_safe' => ['html'],
            ]),
            new TwigFunction('display_flash_info_modal_on_click', [$this, 'displayFlashInfoMessageOnClick'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function autoDisplayFlashInfoModal(): string
    {
        return $this->environment->render("@FlashInfo/twig/flash_info_modal_auto.html.twig");
    }

    public function displayFlashInfoMessageOnClick(string $elementClass): string
    {
        return $this->environment->render('@FlashInfo/twig/flash_info_modal_click.html.twig', [
            'elementClass' => $elementClass,
        ]);
    }
}
