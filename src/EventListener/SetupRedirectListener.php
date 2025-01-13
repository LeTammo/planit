<?php

namespace App\EventListener;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SetupRedirectListener
{
    public function __construct(
        private UserRepository $userRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->getPathInfo() === '/setup' ||
            str_starts_with($request->getPathInfo(), '/_') ||  // _profiler, _wdt, ...
            str_starts_with($request->getPathInfo(), '/assets')) {
            return;
        }

        if (!$this->userRepository->hasAdminUser()) {
            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate('app_setup')
            ));
        }
    }
}