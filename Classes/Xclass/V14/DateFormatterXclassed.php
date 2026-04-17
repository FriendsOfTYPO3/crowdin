<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\Xclass\V14;

use TYPO3\CMS\Core\Localization\DateFormatter;
use TYPO3\CMS\Core\Localization\Locale;

readonly class DateFormatterXclassed extends DateFormatter
{
    public function format(mixed $date, string|int $format, string|Locale $locale): string
    {
        $locale = (string)$locale;
        return parent::format($date, $format, $locale === 't3' ? 'C' : $locale);
    }

    public function strftime(string $format, int|string|\DateTimeInterface|null $timestamp, string|Locale|null $locale = null, $useUtcTimeZone = false): string
    {
        $locale = (string)$locale;
        return parent::strftime($format, $timestamp, $locale === 't3' ? 'C' : $locale, $useUtcTimeZone);
    }
}
