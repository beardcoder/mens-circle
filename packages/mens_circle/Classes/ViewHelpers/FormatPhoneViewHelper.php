<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class FormatPhoneViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('phone', 'string', 'Phone number to format', true);
    }

    public function render(): string
    {
        /** @var string $phone */
        $phone = $this->arguments['phone'];

        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '+49') || str_starts_with($phone, '0049')) {
            $digits = str_starts_with($phone, '+49')
                ? substr($digits, 2)
                : substr($digits, 4);
            $digits = '0' . $digits;
        }

        if (\strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return \sprintf(
                '(%s) %s-%s',
                substr($digits, 0, 4),
                substr($digits, 4, 4),
                substr($digits, 8),
            );
        }

        if (\strlen($digits) === 10 && str_starts_with($digits, '0')) {
            return \sprintf(
                '(%s) %s-%s',
                substr($digits, 0, 3),
                substr($digits, 3, 4),
                substr($digits, 7),
            );
        }

        return $phone;
    }
}
