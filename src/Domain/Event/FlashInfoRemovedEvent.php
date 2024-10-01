<?php

declare(strict_types=1);

namespace Pixel\FlashInfoBundle\Domain\Event;

use Pixel\FlashInfoBundle\Entity\FlashInfo;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

class FlashInfoRemovedEvent extends DomainEvent
{
    private int $id;

    private string $title;

    public function __construct(int $id, string $title)
    {
        parent::__construct();
        $this->id = $id;
        $this->title = $title;
    }

    public function getEventType(): string
    {
        return 'removed';
    }

    public function getResourceKey(): string
    {
        return FlashInfo::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string) $this->id;
    }

    public function getResourceTitle(): ?string
    {
        return $this->title;
    }

    public function getResourceSecurityContext(): ?string
    {
        return FlashInfo::SECURITY_CONTEXT;
    }
}
