<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\ArtefactRepository;
use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Entity\ImageContainerInterface;
use Nines\MediaBundle\Entity\ImageContainerTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=ArtefactRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="artefact_ft", columns={"title", "description"}, flags={"fulltext"})
 * })
 */
class Artefact extends AbstractEntity implements ImageContainerInterface {
    use ImageContainerTrait {
        ImageContainerTrait::__construct as private image_constructor;
    }

    /**
     * @ORM\Column(type="string")
     */
    private ?string $title = null;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $description = null;

    public function __construct() {
        parent::__construct();
        $this->image_constructor();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString() : string {
        return $this->title ?? '';
    }

    public function getTitle() : ?string {
        return $this->title;
    }

    public function setTitle(string $title) : self {
        $this->title = $title;

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
