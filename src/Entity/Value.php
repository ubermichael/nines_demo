<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\DublinCoreBundle\Repository\ValueRepository;
use Nines\UtilBundle\Entity\AbstractEntity;
use Nines\UtilBundle\Entity\LinkedEntityInterface;
use Nines\UtilBundle\Entity\LinkedEntityTrait;

/**
 * @ORM\Entity(repositoryClass=ValueRepository::class)
 * @ORM\Table(name="nines_dc_value", indexes={
 *     @ORM\Index(columns={"data"}, flags={"fulltext"})
 * })
 */
class Value extends AbstractEntity implements LinkedEntityInterface {
    use LinkedEntityTrait;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $data;

    /**
     * @ORM\ManyToOne(targetEntity="Element", inversedBy="values")
     */
    private Element $element;

    public function __construct() {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString() : string {
        return $this->data;
    }

    public function getData() : ?string {
        return $this->data;
    }

    public function setData(string $data) : self {
        $this->data = $data;

        return $this;
    }

    public function getElement() : ?Element {
        return $this->element;
    }

    public function setElement(?Element $element) : self {
        $this->element = $element;

        return $this;
    }
}
