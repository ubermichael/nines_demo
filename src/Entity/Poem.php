<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\PoemRepository;
use Doctrine\ORM\Mapping as ORM;
use Nines\DublinCoreBundle\Entity\ValueInterface;
use Nines\DublinCoreBundle\Entity\ValueTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=PoemRepository::class)
 */
class Poem extends AbstractEntity implements ValueInterface {
    use ValueTrait {
        ValueTrait::__construct as private value_constructor;
    }

    public function __construct() {
        parent::__construct();
        $this->value_constructor();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString() : string {
        $title = $this->getValues('label-1');
        if (count($title)) {
            return $title->first()->getData();
        }

        return 'untitled';
    }
}
