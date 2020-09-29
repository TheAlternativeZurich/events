<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Event;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditType extends AbstractEventType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('event', \App\Form\EventTrait\EditType::class, ['inherit_data' => true]);

        $builder->add('maximumAttendeeCapacity', NumberType::class, ['required' => false]);
        $builder->add('registrationOpen', DateTimeType::class, ['required' => false, 'date_widget' => 'single_text', 'time_widget' => 'single_text']);
        $builder->add('registrationClose', DateTimeType::class, ['required' => false, 'date_widget' => 'single_text', 'time_widget' => 'single_text']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'block_prefix' => 'event',
        ]);
        parent::configureOptions($resolver);
    }
}
