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

use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditType extends AbstractContactInformationTraitType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('givenName', TextType::class);
        $builder->add('familyName', TextType::class);
        $builder->add('email', EmailType::class);
        $builder->add('phone', TextType::class);

        $builder->add('streetAddress', TextType::class);
        $builder->add('postalCode', NumberType::class);
        $builder->add('locality', TextType::class);
        $builder->add('canton', TextType::class);
        $builder->add('country', CountryType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'block_prefix' => 'contact_information_trait',
        ]);
        parent::configureOptions($resolver);
    }
}
