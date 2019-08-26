<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class UpdateImageAction
{
    /** @var DenormalizerInterface */
    private $denormalizer;

    public function __construct(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }
}