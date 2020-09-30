<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Registration;

use Symfony\Component\Form\FormBuilderInterface;

class EditType extends AbstractRegistrationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('contactInformation', \App\Form\ContactInformationTrait\EditType::class, ['inherit_data' => true]);
    }
}
