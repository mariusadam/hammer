<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;

trait HasIntIdentifierTrait
{
    /**
     * @var null|int Resource id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true, readable=false)
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}