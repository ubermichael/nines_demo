<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Form;

use App\Entity\Element;
use App\Entity\Value;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Value form.
 */
class ValueType extends AbstractType {
    /**
     * Add form fields to $builder.
     *
     * @param array<string,mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('data', TextType::class, [
            'label' => 'Data',
            'required' => true,
            'attr' => [
                'help_block' => '',
            ],
        ]);
        $builder->add('entity', TextType::class, [
            'label' => 'Entity',
            'required' => true,
            'attr' => [
                'help_block' => '',
            ],
        ]);

        $builder->add('element', Select2EntityType::class, [
            'label' => 'Element',
            'class' => Element::class,
            'remote_route' => 'element_typeahead',
            'allow_clear' => true,
            'attr' => [
                'help_block' => '',
                'add_path' => 'element_new_popup',
                'add_label' => 'Add Element',
            ],
        ]);
    }

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     */
    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => Value::class,
        ]);
    }
}
