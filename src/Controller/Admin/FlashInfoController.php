<?php

declare(strict_types=1);

namespace Pixel\FlashInfoBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Pixel\FlashInfoBundle\Common\DoctrineListRepresentationFactory;
use Pixel\FlashInfoBundle\Domain\Event\FlashInfoCreatedEvent;
use Pixel\FlashInfoBundle\Domain\Event\FlashInfoModifiedEvent;
use Pixel\FlashInfoBundle\Domain\Event\FlashInfoRemovedEvent;
use Pixel\FlashInfoBundle\Entity\FlashInfo;
use Pixel\FlashInfoBundle\Repository\FlashInfoRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @RouteResource("flash-info")
 */
class FlashInfoController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    use RequestParametersTrait;

    private DoctrineListRepresentationFactory $doctrineListRepresentationFactory;

    private EntityManagerInterface $entityManager;

    private MediaManagerInterface $mediaManager;

    private FlashInfoRepository $flashInfoRepository;

    private DomainEventCollectorInterface $domainEventCollector;

    private TrashManagerInterface $trashManager;

    public function __construct(
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        EntityManagerInterface $entityManager,
        MediaManagerInterface $mediaManager,
        FlashInfoRepository $flashInfoRepository,
        DomainEventCollectorInterface $domainEventCollector,
        TrashManagerInterface $trashManager,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
        $this->entityManager = $entityManager;
        $this->mediaManager = $mediaManager;
        $this->flashInfoRepository = $flashInfoRepository;
        $this->domainEventCollector = $domainEventCollector;
        $this->trashManager = $trashManager;
        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(Request $request): Response
    {
        $locale = $request->query->get('locale');
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            FlashInfo::RESOURCE_KEY,
            [],
            [
                'locale' => $locale,
            ]
        );

        return $this->handleView($this->view($listRepresentation));
    }

    protected function load(int $id, Request $request, string $defaultLocale = null): ?FlashInfo
    {
        return $this->flashInfoRepository->findById($id, ($defaultLocale) ? $defaultLocale : (string) $this->getLocale($request));
    }

    protected function save(FlashInfo $flashInfo): void
    {
        $this->flashInfoRepository->save($flashInfo);
    }

    protected function create(Request $request): FlashInfo
    {
        return $this->flashInfoRepository->create((string) $this->getLocale($request));
    }

    public function getAction(int $id, Request $request): Response
    {
        $flashInfo = $this->load($id, $request);
        if (!$flashInfo) {
            throw new NotFoundHttpException();
        }

        if ($flashInfo->getTitle() === null && $flashInfo->getDefaultLocale()) {
            $request->setMethod($flashInfo->getDefaultLocale());
            $flashInfo = $this->load($id, $request, $flashInfo->getDefaultLocale());
        }

        return $this->handleView($this->view($flashInfo));
    }

    public function putAction(Request $request, int $id): Response
    {
        $flashInfo = $this->load($id, $request);
        if (!$flashInfo) {
            throw new NotFoundHttpException();
        }

        $data = $request->request->all();
        $this->mapDataToEntity($data, $flashInfo);
        $this->domainEventCollector->collect(
            new FlashInfoModifiedEvent($flashInfo, $data)
        );
        $this->entityManager->flush();
        $this->save($flashInfo);
        return $this->handleView($this->view($flashInfo));
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function mapDataToEntity(array $data, FlashInfo $entity): void
    {
        $imageId = $data['image']['id'] ?? null;
        $pdfs = $data['pdfs'] ?? null;
        $isActive = $data['isActive'] ?? null;
        $labelLinkButton = $data['labelLinkButton'] ?? null;
        $link = $data['link'] ?? null;

        $entity->setTitle($data['title']);
        $entity->setImage($imageId ? $this->mediaManager->getEntityById($imageId) : null);
        $entity->setDescription($data['description']);
        $entity->setPdfs($pdfs);
        $entity->setIsActive($isActive);
        $entity->setStartDate(new \DateTimeImmutable($data['startDate']));
        $entity->setEndDate(new \DateTimeImmutable($data['endDate']));
        $entity->setLabelLinkButton($labelLinkButton);
        $entity->setLink($link);
    }

    public function postAction(Request $request): Response
    {
        $flashInfo = $this->create($request);
        $data = $request->request->all();
        $this->mapDataToEntity($data, $flashInfo);
        $this->save($flashInfo);
        $this->domainEventCollector->collect(
            new FlashInfoCreatedEvent($flashInfo, $data)
        );
        $this->entityManager->flush();

        return $this->handleView($this->view($flashInfo, 201));
    }

    public function deleteAction(int $id): Response
    {
        /** @var FlashInfo $flashInfo */
        $flashInfo = $this->entityManager->getRepository(FlashInfo::class)->find($id);
        $flashInfoTitle = $flashInfo->getTitle();
        if ($flashInfo) {
            $this->trashManager->store(FlashInfo::RESOURCE_KEY, $flashInfo);
            $this->entityManager->remove($flashInfo);
            $this->domainEventCollector->collect(
                new FlashInfoRemovedEvent($id, $flashInfoTitle)
            );
        }
        $this->entityManager->flush();

        return $this->handleView($this->view(null, 204));
    }

    /**
     * @Rest\Post("/flash-infos/{id}")
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws EntityNotFoundException
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $action = $this->getRequestParameter($request, 'action', true);
        $locale = $this->getRequestParameter($request, 'locale', true);

        try {
            switch ($action) {
                case 'enable':
                    $item = $this->entityManager->getRepository(FlashInfo::class)->find($id);
                    $item->setLocale($locale);
                    $item->setIsActive(true);
                    $this->entityManager->persist($item);
                    $this->entityManager->flush();
                    break;
                case 'disable':
                    $item = $this->entityManager->getRepository(FlashInfo::class)->find($id);
                    $item->setLocale($locale);
                    $item->setIsActive(false);
                    $this->entityManager->persist($item);
                    $this->entityManager->flush();
                    break;
                default:
                    throw new BadRequestHttpException(sprintf('Unknown action "%s".', $action));
            }
        } catch (RestException $exc) {
            $view = $this->view($exc->toArray(), 400);
            return $this->handleView($view);
        }

        return $this->handleView($this->view($item));
    }

    public function getSecurityContext(): string
    {
        return FlashInfo::SECURITY_CONTEXT;
    }
}
