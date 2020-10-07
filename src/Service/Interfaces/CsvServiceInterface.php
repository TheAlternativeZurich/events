<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use Symfony\Component\HttpFoundation\Response;

interface CsvServiceInterface
{
    public function streamCsv(string $filename, array $data, array $header): Response;
}
