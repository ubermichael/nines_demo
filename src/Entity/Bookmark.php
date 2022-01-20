<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\BookmarkRepository;
use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Entity\LinkableInterface;
use Nines\MediaBundle\Entity\LinkableTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=BookmarkRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="bookmark_ft", columns={"title"}, flags={"fulltext"})
 * })
 */
class Bookmark extends AbstractEntity implements LinkableInterface {
    use LinkableTrait {
        LinkableTrait::__construct as private linkable_constructor;
    }

    /**
     * @ORM\Column(type="string")
     */
    private ?string $title = null;

    public function __construct() {
        parent::__construct();
        $this->linkable_constructor();
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
}
