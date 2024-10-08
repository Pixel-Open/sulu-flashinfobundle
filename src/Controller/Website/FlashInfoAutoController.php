<?php

namespace Pixel\FlashInfoBundle\Controller\Website;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\FlashInfoBundle\Entity\FlashInfo;
use Pixel\FlashInfoBundle\Entity\Setting;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FlashInfoAutoController extends AbstractController
{
    /**
     * @Route("flash-infos-auto", name="flash_infos_auto")
     */
    public function flashInfosAuto(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $flashInfos = $entityManager->getRepository(FlashInfo::class)->findPublishedFlashInfo();
        $settings = $entityManager->getRepository(Setting::class)->findOneBy([]);
        $createCookie = false;
        $lastFlashInfoTimestamp = (!empty($flashInfos)) ? $flashInfos[0]->getChanged()->getTimestamp() : false;
        $json = [];

        if (empty($flashInfos)) {
            $json['displayModal'] = false;
        } else {
            $popupPolicy = $settings->getPopupPolicy();
            switch ($popupPolicy) {
                case Setting::DO_NOT_OPEN:
                    $json['displayModal'] = false;
                    break;
                case Setting::OPEN_ONCE:
                    $popupPolicyCookie = (bool) $request->cookies->get('flashInfoModalOpened-' . $lastFlashInfoTimestamp);
                    if (!$popupPolicyCookie) {
                        $createCookie = true;

                        $json['displayModal'] = true;
                        $json['template'] = $this->renderView("@FlashInfo/twig/flash_info_modal_content.html.twig", [
                            'flashInfos' => $flashInfos,
                        ]);
                    } else {
                        $json['displayModal'] = false;
                    }
                    break;
                case Setting::OPEN_EVERY_TIME:
                    $json['displayModal'] = true;
                    $json['template'] = $this->renderView("@FlashInfo/twig/flash_info_modal_content.html.twig", [
                        'flashInfos' => $flashInfos,
                    ]);
                    break;
            }
        }

        $jsonResponse = new JsonResponse($json);
        if ($createCookie) {
            $cookieDuration = $settings->getCookieDuration();
            $jsonResponse->headers->setCookie($this->setCookie($lastFlashInfoTimestamp, $cookieDuration));
        }
        return $jsonResponse;
    }

    private function setCookie(int $timestamp, int $cookieDuration = null): Cookie
    {
        $duration = $cookieDuration ?? 30;
        return Cookie::create('flashInfoModalOpened-' . $timestamp)
                ->withValue('true')
                ->withExpires((new \DateTime())->modify("+$duration day"));
    }
}
