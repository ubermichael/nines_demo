<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Entity\PdfContainerInterface;
use Nines\MediaBundle\Entity\PdfContainerTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="document_ft", columns={"title", "description"}, flags={"fulltext"})
 * })
 */
class Document extends AbstractEntity implements PdfContainerInterface {
    use PdfContainerTrait {
        PdfContainerTrait::__construct as private pdf_constructor;
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
        $this->pdf_constructor();
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
