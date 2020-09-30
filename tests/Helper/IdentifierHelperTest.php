<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Helper;

use App\Helper\IdentifierHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IdentifierHelperTest extends WebTestCase
{
    public function testIdentifierGeneration()
    {
        $input = [
            'TheAlternative-Linux Days and more',
        ];
        $expected = [
            'the-alternative-linux-days-and-more',
        ];

        for ($i = 0; $i < count($input); ++$i) {
            $actual = IdentifierHelper::getHumanReadableIdentifier($input[$i]);
            $this->assertEquals($expected[$i], $actual);
        }
    }
}
