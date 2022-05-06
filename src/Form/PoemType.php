<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Form;

use App\Entity\Poem;
use Nines\DublinCoreBundle\Form\Mapper\DublinCoreMapper;
use Nines\DublinCoreBundle\Form\ValueType;
use Nines\DublinCoreBundle\Repository\ElementRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Poem form.
 */
class PoemType extends AbstractType {
    private ?ElementRepository $repo = null;

    private ?DublinCoreMapper $mapper = null;

    /**
     * Add form fields to $builder.
     *
     * @param array<string,mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        ValueType::add($builder, array_merge($options, ['repo' => $this->repo]));
        $builder->setDataMapper($this->mapper);
    }

    /**
     * @required
     */
    public function setElementRepository(ElementRepository $repo) : void {
        $this->repo = $repo;
    }

    /**
     * @required
     */
    public function setDublinCoreMapper(DublinCoreMapper $mapper) : void {
        $this->mapper = $mapper;
    }

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     */
    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => Poem::class,
        ]);
    }
}
