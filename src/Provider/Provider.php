<?php

namespace Fakturoid\Provider;

abstract class Provider
{
    /**
     * @param array<string, mixed> $options
     * @param array<string> $allowedKeys
     * @return array<string, mixed>
     */
    protected function filterOptions(array $options, array $allowedKeys = [], bool $caseSensitive = true): array
    {
        if ($options === []) {
            return [];
        }

        $unknownKeys = [];

        foreach ($options as $key => $value) {
            if (!$caseSensitive) {
                $key = strtolower($key);
            }

            if (!in_array($key, $allowedKeys)) {
                unset($options[$key]);
                $unknownKeys[] = $key;
            }
        }

        if (!empty($unknownKeys)) {
            trigger_error('Unknown option keys: ' . implode(', ', $unknownKeys));
        }

        return $options;
    }
}
