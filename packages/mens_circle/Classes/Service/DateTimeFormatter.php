<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Service;

use DateTimeImmutable;
use Throwable;

final class DateTimeFormatter
{
    public function formatDate(string $dateValue): string
    {
        $dateValue = trim($dateValue);
        if ($dateValue === '' || $dateValue === '0000-00-00 00:00:00') {
            return '';
        }

        try {
            return (new DateTimeImmutable($dateValue))->format('d.m.Y');
        } catch (Throwable) {
            return '';
        }
    }

    public function formatTime(string $timeValue): string
    {
        $timeValue = trim($timeValue);
        if ($timeValue === '' || $timeValue === '00:00:00') {
            return '';
        }

        try {
            return (new DateTimeImmutable($timeValue))->format('H:i');
        } catch (Throwable) {
            return '';
        }
    }
}
