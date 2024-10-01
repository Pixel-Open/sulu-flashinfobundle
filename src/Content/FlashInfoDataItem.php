<?php

declare(strict_types=1);

namespace Pixel\FlashInfoBundle\Content;

use JMS\Serializer\Annotation as Serializer;
use Pixel\FlashInfoBundle\Entity\FlashInfo;
use Sulu\Component\SmartContent\ItemInterface;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class FlashInfoDataItem implements ItemInterface
{
    private FlashInfo $entity;

    public function __construct(FlashInfo $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getId(): string
    {
        return (string) $this->entity->getId();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getTitle(): string
    {
        return (string) $this->entity->getTitle();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getImage(): ?string
    {
        return null;
    }

    public function getResource(): FlashInfo
    {
        return $this->entity;
    }
}
