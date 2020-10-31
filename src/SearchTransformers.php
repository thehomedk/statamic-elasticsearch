<?php

namespace TheHome\StatamicElasticsearch;

class SearchTransformers
{
  public static function resolve(): array
  {
    return [
      'bardToText' => function ($data) : string {
        $text = '';
        if (is_array($data)) {
          foreach (self::recursiveFind($data, 'text') as $value) {
            $text .= $value;
          }
        } else {
          $text = $data;
        }
        return $text;
      },
    ];
  }

  public static function recursiveFind(array $haystack, $needle)
  {
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
