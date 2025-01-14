<?php

namespace App\EventSubscriber;

use App\Service\SidebarService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class SidebarSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SidebarService $sidebarService,
        private Environment $twig,
        private Security $security
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user) {
            return;
        }

        $sidebarData = $this->sidebarService->getSidebarData($user);
        $this->twig->addGlobal('main_data', $sidebarData);
    }
}