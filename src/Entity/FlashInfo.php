<?php

namespace Pixel\FlashInfoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="flash_info")
 * @Serializer\ExclusionPolicy("all")
 * @ORM\Entity(repositoryClass="Pixel\FlashInfoBundle\Repository\FlashInfoRepository")
 */
class FlashInfo
{
    public const RESOURCE_KEY = 'flash_infos';

    public const FORM_KEY = "flash_info_details";

    public const LIST_KEY = "flash_infos";

    public const SECURITY_CONTEXT = "flash_infos";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=MediaInterface::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Serializer\Expose()
     */
    private ?MediaInterface $image = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Expose()
     * @var array<mixed>|null
     */
    private ?array $pdfs = null;

    /**
     * @ORM\Column(type="date_immutable")
     * @Serializer\Expose()
     */
    private \DateTimeImmutable $startDate;

    /**
     * @ORM\Column(type="date_immutable")
     * @Serializer\Expose()
     */
    private \DateTimeImmutable $endDate;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Expose()
     * @var array<mixed>|null
     */
    private ?array $link = null;

    /**
     * @var Collection<string, FlashInfoTranslation>
     * @ORM\OneToMany(targetEntity="Pixel\FlashInfoBundle\Entity\FlashInfoTranslation", mappedBy="flashInfo", cascade={"ALL"}, indexBy="locale")
     * @Serializer\Exclude()
     */
    private $translations;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $defaultLocale = null;

    private string $locale = "fr";

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?MediaInterface
    {
        return $this->image;
    }

    public function setImage(?MediaInterface $image): void
    {
        $this->image = $image;
    }

    /**
     * @return mixed[]|null
     */
    public function getPdfs(): ?array
    {
        return $this->pdfs;
    }

    /**
     * @param array<mixed>|null $pdfs
     */
    public function setPdfs(?array $pdfs): void
    {
        $this->pdfs = $pdfs;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return mixed[]|null
     */
    public function getLink(): ?array
    {
        return $this->link;
    }

    /**
     * @param array<mixed>|null $link
     */
    public function setLink(?array $link): void
    {
        $this->link = $link;
    }

    protected function getTranslation(string $locale = 'fr'): ?FlashInfoTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }
        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): FlashInfoTranslation
    {
        $translation = new FlashInfoTranslation($this, $locale);
        $this->translations->set($locale, $translation);
        return $translation;
    }

    /**
     * @return array<string, FlashInfoTranslation>
     */
    public function getTranslations()
    {
        return $this->translations->toArray();
    }

    public function getDefaultLocale(): ?string
    {
        return $this->defaultLocale;
    }

    public function setDefaultLocale(?string $defaultLocale): void
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @Serializer\VirtualProperty(name="title")
     */
    public function getTitle(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getTitle();
    }

    public function setTitle(?string $title): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setTitle($title);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="description")
     */
    public function getDescription(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getDescription();
    }

    public function setDescription(string $description): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setDescription($description);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="label_link_button")
     */
    public function getLabelLinkButton(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getLabelLinkButton();
    }

    public function setLabelLinkButton(?string $labelLinkButton): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $this->createTranslation($this->locale);
        }
        $translation->setLabelLinkButton($labelLinkButton);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="is_active")
     */
    public function getIsActive(): ?bool
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getIsActive();
    }

    public function setIsActive(?bool $isActive): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setIsActive($isActive);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="created")
     */
    public function getCreated(): ?\DateTime
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getCreated();
    }

    public function setCreated(?\DateTime $created): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setCreated($created);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="changed")
     */
    public function getChanged(): ?\DateTime
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getChanged();
    }

    public function setChanged(?\DateTime $changed): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setChanged($changed);
        return $this;
    }
}
