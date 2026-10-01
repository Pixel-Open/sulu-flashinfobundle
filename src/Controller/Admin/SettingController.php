<?php

namespace Pixel\FlashInfoBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Pixel\FlashInfoBundle\Entity\Setting;
use Pixel\FlashInfoBundle\Service\SettingsService;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @RouteResource("flash-info-settings")
 */
class SettingController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    private EntityManagerInterface $entityManager;
    private SettingsService $settingsService;

    public function __construct(
        EntityManagerInterface $entityManager,
        SettingsService $settingsService,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->settingsService = $settingsService;
        parent::__construct($viewHandler, $tokenStorage);
    }

    public function getAction(): Response
    {
        $applicationSetting = $this->settingsService->getOrCreateSettings();
        return $this->handleView($this->view($applicationSetting));
    }

    public function putAction(Request $request): Response
    {
        $applicationSetting = $this->entityManager->getRepository(Setting::class)->findOneBy([]);
        if (!$applicationSetting) {
            $applicationSetting = new Setting();
            $this->entityManager->persist($applicationSetting);
        }

        $this->mapDataToEntity($request->request->all(), $applicationSetting);
        $this->entityManager->flush();
        return $this->handleView($this->view($applicationSetting));
    }

    /**
     * @param array<mixed> $data
     */
    protected function mapDataToEntity(array $data, Setting $entity): void
    {
        $cookieDuration = $data['cookieDuration'] ?? null;

        $entity->setPopupPolicy($data['popupPolicy']);
        $entity->setCookieDuration($cookieDuration);
    }

    public function getSecurityContext(): string
    {
        return Setting::SECURITY_CONTEXT;
    }
}
