<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\TitleRepository;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=TitleRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="title_ft", columns={"main", "sub", "description"}, flags={"fulltext"})
 * })
 */
class Title extends AbstractEntity {
    /**
     * @ORM\Column(type="string")
     */
    private ?string $main = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $sub = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?float $price = null;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $description = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString() : string {
        return $this->main ?? '';
    }

    public function getMain() : ?string {
        return $this->main;
    }

    public function setMain(string $main) : self {
        $this->main = $main;

        return $this;
    }

    public function getSub() : ?string {
        return $this->sub;
    }

    public function setSub(string $sub) : self {
        $this->sub = $sub;

        return $this;
    }

    public function getPrice() : ?float {
        return $this->price;
    }

    public function setPrice(?float $price) : self {
        $this->price = $price;

        return $this;
    }

    public function getDescription() : ?string {
        return $this->description;
    }

    public function setDescription(string $description) : self {
        $this->description = $description;

        return $this;
    }
}
