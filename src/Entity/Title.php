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
use Nines\SolrBundle\Annotation as Solr;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=TitleRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="title_ft", columns={"main", "sub", "description"}, flags={"fulltext"})
 * })
 * @Solr\Document(
 *     copyField=@Solr\CopyField(from={"main", "sub"}, to="title", type="texts"),
 *     computedFields=@Solr\ComputedField(name="tax_price", getter="getPriceWithTax", type="float")
 * )
 */
class Title extends AbstractEntity {
    /**
     * @ORM\Column(type="string")
     * @Solr\Field(type="text")
     */
    private ?string $main = null;

    /**
     * @ORM\Column(type="string")
     * @Solr\Field(type="text")
     */
    private ?string $sub = null;

    /**
     * @ORM\Column(type="integer")
     * @Solr\Field(type="float")
     */
    private ?float $price = null;

    /**
     * @ORM\Column(type="text")
     * @Solr\Field(type="text", boost=0.5, filters={"strip_tags", "html_entity_decode(51, 'UTF-8')"})
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

    public function getPriceWithTax() : float {
        return $this->price * 1.05;
    }
}
