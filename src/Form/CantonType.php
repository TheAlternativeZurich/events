<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CantonType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'Aargau' => 'AG',
                'Appenzell Ausserrhoden' => 'AR',
                'Appenzell Innerrhoden' => 'AI',
                'Basel-Landschaft' => 'BL',
                'Basel-Stadt' => 'BS',
                'Bern' => 'BE',
                'Fribourg' => 'FR',
                'Genève' => 'GF',
                'Glarus' => 'GL',
                'Graubünden' => 'GR',
                'Jura' => 'JU',
                'Luzern' => 'LU',
                'Neuchâtel' => 'NE',
                'Nidwalden' => 'NW',
                'Obwalden' => 'OW',
                'Schaffhausen' => 'SH',
                'Schwyz' => 'SZ',
                'Solothurn' => 'SO',
                'St. Gallen' => 'SG',
                'Ticino' => 'TI',
                'Thurgau' => 'TG',
                'Uri' => 'UR',
                'Vaud' => 'VD',
                'Valais' => 'VS',
                'Zug' => 'ZG',
            ],
            'choice_translation_domain' => false,
            'choice_translation_locale' => null,
            'alpha3' => false,
        ]);

        $resolver->setAllowedTypes('choice_translation_locale', ['null', 'string']);
        $resolver->setAllowedTypes('alpha3', 'bool');
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'country';
    }
}
