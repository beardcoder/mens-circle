<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Utility;

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
        $connection = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Database\ConnectionPool::class,
        )->getConnectionForTable($table);

        $query = $connection->createQueryBuilder()
            ->select('uid')
            ->from($table)
            ->where($connection->createQueryBuilder()->expr()->eq('slug', $connection->quote($slug, \PDO::PARAM_STR)));

        if ($excludeUid !== null) {
            $query = $query->andWhere($connection->createQueryBuilder()->expr()->neq('uid', $excludeUid));
        }

        return $query->executeQuery()->rowCount() === 0;
    }
}
