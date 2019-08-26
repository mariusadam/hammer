<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Util\RequestAttributesExtractor;
use App\Entity\Image;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Vich\UploaderBundle\Storage\StorageInterface;

final class ResolveImageObjectContentUrlSubscriber implements EventSubscriberInterface
{
    private $storage;
    private $requestStack;

    public function __construct(StorageInterface $storage, RequestStack $requestStack)
    {
        $this->storage = $storage;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onPreSerialize', EventPriorities::PRE_SERIALIZE],
        ];
    }

    private function isImageResourceClassRequest(Request $request): bool
    {
        $attributes = RequestAttributesExtractor::extractAttributes($request);

        return $attributes && \is_a($attributes['resource_class'], Image::class, true);
    }

    public function onPreSerialize(ViewEvent $event): void
    {
        $controllerResult = $event->getControllerResult();
        $request = $event->getRequest();

        if ($controllerResult instanceof Response || !$request->attributes->getBoolean('_api_respond', true)) {
            return;
        }


        if (false === $this->isImageResourceClassRequest($request)) {
            return;
        }

        $images = is_iterable($controllerResult) ? $controllerResult : [$controllerResult];
        foreach ($images as $image) {
            if ($image instanceof Image) {
                $image->updateContentUrl($this->getAbsoluteUrl($image));
            }
        }
    }

    private function getAbsoluteUrl(Image $imageObject): string
    {
        $baseUrl = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();

        return $baseUrl.$this->storage->resolveUri($imageObject, 'file');
    }
}