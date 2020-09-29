<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\EventTrait;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditType extends AbstractEventTraitType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class);
        $builder->add('organizer', TextType::class);
        $builder->add('description', TextareaType::class);

        $builder->add('startDate', DateTimeType::class, ['date_widget' => 'single_text', 'time_widget' => 'single_text']);
        $builder->add('endDate', DateTimeType::class, ['date_widget' => 'single_text', 'time_widget' => 'single_text', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'block_prefix' => 'event_trait',
        ]);
        parent::configureOptions($resolver);
    }
}
