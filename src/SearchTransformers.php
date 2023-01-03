<?php

namespace TheHome\StatamicElasticsearch;

use Statamic\Facades\Entry;
use Statamic\Facades\Asset;

class SearchTransformers
{
    public static function resolve(): array
    {
        return [
            'bardToText' => function ($data): string {
                $text = '';
                if (is_array($data)) {
                    foreach (self::recursiveFind($data, 'text') as $value) {
                        if (!is_array($value)) {
                            $text .= ' ' . $value;
                        }
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
            'subsidy' => function ($data, $item): string {
                $id = str_after($item, 'entry::');
                $entry = Entry::find($id);
                if ($entry !== null) {
                    $category = $entry->get('subsidy_categories');
                    $year = $data;
                    $asset = Asset::find(
                        sprintf("subsidies::%s/%s.txt", $year, $category),
                    );
                    if ($asset !== null) {
                        $html = file_get_contents($asset->resolvedPath());
                        return html_entity_decode(strip_tags($html));
                    }
                }
                return $data;
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
            \RecursiveIteratorIterator::SELF_FIRST,
        );
        foreach ($recursive as $key => $value) {
            if ($key === $needle) {
                yield $value;
            }
            if ($key == 'cells') {
                $values = [];
                foreach ($value as $cell) {
                    $cell = \Illuminate\Support\Str::remove([',', '.'], $cell);
                    if (!is_numeric($cell)) {
                        $values[] = $cell;
                    }
                };
                yield join(' ', $values);
            }
        }
    }
}
