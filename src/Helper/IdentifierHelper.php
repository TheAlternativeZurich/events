<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Helper;

class IdentifierHelper
{
    /**
     * transforms text to human readable URL
     * only outputs lowercase alphanummeric string, invalid characters are replaced by -.
     *
     * min length 10, max length 100
     */
    public static function getHumanReadableIdentifier(string $text): string
    {
        $lowercase = strtolower($text);

        $result = '';
        for ($i = 0; $i < strlen($text); ++$i) {
            $character = $lowercase[$i];
            //0-9, a-z
            if (($character >= 48 && $character <= 57) ||
                ($character >= 97 && $character <= 122)) {
                $result .= $character;
            } else {
                $result .= '-';
            }
        }

        if (strlen($result) > 100) {
            $result = substr($result, 0, 100); // make max length
            $result = substr($result, 0, strrpos($result, '-')); // cut off last word
        }

        if (strlen($result) < 10) {
            $result .= RandomHelper::generateHumanReadableRandom(10, '-');
        }

        return $result;
    }
}
