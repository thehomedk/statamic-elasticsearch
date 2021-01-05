<?php

namespace TheHome\StatamicElasticsearch;
use Statamic\Facades\Entry;

class SearchTransformers
{
    public static function resolve(): array
    {
        return [
            'bardToText' => function ($data): string {
                $text = '';
                if (is_array($data)) {
                    foreach (self::recursiveFind($data, 'text') as $value) {
                        $text .= ' ' . $value;
                    }
                } else {
                    $text = $data;
                }
                return trim(preg_replace('/\s+/', ' ', $text));
            },
            'handle' => function ($data): string {
                if (is_object($data)) {
                    return $data->handle();
                } else {
                    return $data;
                }
            },
            'duplicate' => function ($data, $item): string {
                $id = str_after($item, 'entry::');

                $hasShadow =
                    Entry::query()
                        ->where('blueprint', 'shadow')
                        ->where('shadowing', $id)
                        ->count() > 0;

                if ($hasShadow) {
                    return 'shadow';
                }

                if (is_object($data)) {
                    return $data->handle();
                } else {
                    return $data;
                }
            },
        ];
    }

    protected static function recursiveFind(
        array $haystack,
        string $needle
    ): \Iterator {
        $iterator = new \RecursiveArrayIterator($haystack);
        $recursive = new \RecursiveIteratorIterator(
            $iterator,
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($recursive as $key => $value) {
            if ($key === $needle) {
                yield $value;
            }
        }
    }
}
