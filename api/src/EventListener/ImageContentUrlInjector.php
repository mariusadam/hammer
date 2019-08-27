<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Image;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Injects the absolute content url of a loaded image object
 */
class ImageContentUrlInjector
{
    /** @var RequestStack */
    private $requestStack;

    /** @var StorageInterface */
    private $storage;

    public function __construct(RequestStack $requestStack, StorageInterface $storage)
    {
        $this->requestStack = $requestStack;
        $this->storage = $storage;
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof Image) {
            $entity->setContentUrl($this->getAbsoluteUrl($entity));
        }
    }

    private function getAbsoluteUrl(Image $image): string
    {
        $imageUri = $this->storage->resolveUri($image, 'imageFile');
        $currentRequest = $this->requestStack->getCurrentRequest();
        $schemeAndHttpHost = $currentRequest ? $currentRequest->getSchemeAndHttpHost() : '';

        return $schemeAndHttpHost.$imageUri;
    }
}