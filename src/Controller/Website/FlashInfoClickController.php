<?php

namespace Pixel\FlashInfoBundle\Controller\Website;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\FlashInfoBundle\Entity\FlashInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class FlashInfoClickController extends AbstractController
{
    /**
     * @Route("flash-infos-click", name="flash_infos_click")
     */
    public function flashInfosClick(EntityManagerInterface $entityManager): JsonResponse
    {
        $flashInfos = $entityManager->getRepository(FlashInfo::class)->findPublishedFlashInfo();
        $json = [
            'displayModal' => true,
        ];

        if (!empty($flashInfos)) {
            $json['template'] = $this->renderView("@FlashInfo/twig/flash_info_modal_content.html.twig", [
                'flashInfos' => $flashInfos,
            ]);
        } else {
            $json['template'] = $this->renderView("@FlashInfo/twig/flash_info_modal_empty.html.twig");
        }

        return new JsonResponse($json);
    }
}
