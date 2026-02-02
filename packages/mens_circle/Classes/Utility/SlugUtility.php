<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Utility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class SlugUtility
{
    public static function generate(string $text): string
    {
        $slug = mb_strtolower($text, 'UTF-8');

        $replacements = [
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'ß' => 'ss',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'à' => 'a',
            'â' => 'a',
            'î' => 'i',
            'ï' => 'i',
            'ô' => 'o',
            'ù' => 'u',
            'û' => 'u',
            'ç' => 'c',
        ];

        $slug = str_replace(array_keys($replacements), array_values($replacements), $slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }

    public static function isUnique(string $slug, string $table, ?int $excludeUid = null): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);

        $queryBuilder
            ->count('uid')
            ->from($table)
            ->where($queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($slug)));

        if ($excludeUid !== null) {
            $queryBuilder->andWhere($queryBuilder->expr()->neq('uid', $queryBuilder->createNamedParameter($excludeUid, \Doctrine\DBAL\ParameterType::INTEGER)));
        }

        return (int) $queryBuilder->executeQuery()->fetchOne() === 0;
    }
}
