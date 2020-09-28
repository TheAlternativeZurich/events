<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\ContactInformationTrait;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractContactInformationTraitType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'trait_contact_information',
        ]);
        parent::configureOptions($resolver);
    }
}
