<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\WorkRepository;
use Doctrine\ORM\Mapping as ORM;
use Nines\DublinCoreBundle\Entity\ValueInterface;
use Nines\DublinCoreBundle\Entity\ValueTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=WorkRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="work_ft", columns={"url"}, flags={"fulltext"}),
 * })
 */
class Work extends AbstractEntity implements ValueInterface {
    use ValueTrait;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $url = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString() : string {
        if ($this->url) {
            return $this->url;
        }

        return '';
    }

    public function getUrl() : ?string {
        return $this->url;
    }

    public function setUrl(string $url) : self {
        $this->url = $url;

        return $this;
    }
}
