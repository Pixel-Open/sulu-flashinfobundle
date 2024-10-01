<?php

declare(strict_types=1);

namespace Pixel\FlashInfoBundle\Trash;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\FlashInfoBundle\Admin\FlashInfoAdmin;
use Pixel\FlashInfoBundle\Domain\Event\FlashInfoRestoredEvent;
use Pixel\FlashInfoBundle\Entity\FlashInfo;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TrashBundle\Application\DoctrineRestoreHelper\DoctrineRestoreHelperInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;

class FlashInfoTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    private TrashItemRepositoryInterface $trashItemRepository;

    private EntityManagerInterface $entityManager;

    private DoctrineRestoreHelperInterface $doctrineRestoreHelper;

    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        TrashItemRepositoryInterface $trashItemRepository,
        EntityManagerInterface $entityManager,
        DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        DomainEventCollectorInterface $domainEventCollector
    ) {
        $this->trashItemRepository = $trashItemRepository;
        $this->entityManager = $entityManager;
        $this->doctrineRestoreHelper = $doctrineRestoreHelper;
        $this->domainEventCollector = $domainEventCollector;
    }

    public static function getResourceKey(): string
    {
        return FlashInfo::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        $image = $resource->getImage();

        $data = [
            'title' => $resource->getTitle(),
            'imageId' => $image ? $image->getId() : null,
            'documents' => $resource->getPdfs(),
            'startDate' => $resource->getStartDate(),
            'endDate' => $resource->getEndDate(),
            'description' => $resource->getDescription(),
            'labelLinkButton' => $resource->getLabelLinkButton(),
            'link' => $resource->getLink(),
            'isActive' => $resource->getIsActive(),
        ];

        return $this->trashItemRepository->create(
            FlashInfo::RESOURCE_KEY,
            (string) $resource->getId(),
            $resource->getTitle(),
            $data,
            null,
            $options,
            FlashInfo::SECURITY_CONTEXT,
            null,
            null
        );
    }

    public function restore(TrashItemInterface $trashItem, array $options = []): object
    {
        $data = $trashItem->getRestoreData();
        $flashInfoId = (int) $trashItem->getResourceId();

        $flashInfo = new FlashInfo();
        $flashInfo->setTitle($data['title']);
        if (isset($data['imageId'])) {
            $flashInfo->setImage($this->entityManager->find(MediaInterface::class, $data['imageId']));
        }
        if (isset($data['documents'])) {
            $flashInfo->setPdfs($data['documents']);
        }
        $flashInfo->setStartDate(new \DateTimeImmutable($data['startDate']['date']));
        $flashInfo->setEndDate(new \DateTimeImmutable($data['endDate']['date']));
        $flashInfo->setDescription($data['description']);
        if (isset($data['labelLinkButton'])) {
            $flashInfo->setLabelLinkButton($data['labelLinkButton']);
        }
        if (isset($data['link'])) {
            $flashInfo->setLink($data['link']);
        }
        $flashInfo->setIsActive($data['isActive']);

        $this->domainEventCollector->collect(
            new FlashInfoRestoredEvent($flashInfo, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($flashInfo, $flashInfoId);
        return $flashInfo;
    }

    public function getConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration(null, FlashInfoAdmin::EDIT_FORM_VIEW, [
            'id' => "id",
        ]);
    }
}
