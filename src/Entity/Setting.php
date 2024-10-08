<?php

namespace Pixel\FlashInfoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="flash_info_settings")
 * @Serializer\ExclusionPolicy("all")
 */
class Setting implements AuditableInterface
{
    use AuditableTrait;

    public const RESOURCE_KEY = "flash_infos_settings";

    public const FORM_KEY = "flash_infos_settings";

    public const SECURITY_CONTEXT = "flash_infos_settings.settings";

    public const DO_NOT_OPEN = 1;

    public const OPEN_ONCE = 2;

    public const OPEN_EVERY_TIME = 3;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private int $popupPolicy;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Expose()
     */
    private ?int $cookieDuration = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPopupPolicy(): int
    {
        return $this->popupPolicy;
    }

    public function setPopupPolicy(int $popupPolicy): void
    {
        $this->popupPolicy = $popupPolicy;
    }

    public function getCookieDuration(): ?int
    {
        return $this->cookieDuration;
    }

    public function setCookieDuration(?int $cookieDuration): void
    {
        $this->cookieDuration = $cookieDuration;
    }
}
