<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\RecordingRepository;
use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Entity\AudioContainerInterface;
use Nines\MediaBundle\Entity\AudioContainerTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=RecordingRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="recording_ft", columns={"title"}, flags={"fulltext"})
 * })
 */
class Recording extends AbstractEntity implements AudioContainerInterface {
    use AudioContainerTrait {
        AudioContainerTrait::__construct as private audio_constructor;
    }

    /**
     * @ORM\Column(type="string")
     */
    private ?string $title = null;

    public function __construct() {
        parent::__construct();
        $this->audio_constructor();
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
