<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use TheHome\StatamicElasticsearch\SearchTransformers;

final class SearchTransformersTest extends TestCase
{
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = SearchTransformers::resolve();
    }
       
    /**
     * testCanTransformBardToText
     * 
     * @covers SearchTransformers
     * @dataProvider prosemirrorProvider
     * 
     * @return void
     */
    public function testCanTransformBardToText($bard, $expected): void
    {
      $transformed = $this->transformer['bardToText']($bard);
      $this->assertEquals($expected, $transformed);
      $this->assertIsString($transformed);
    }

    public function prosemirrorProvider(): array
    {
        return [
            ['test', 'test'],
            [' test test ', 'test test'],
            [[
              'type' => 'doc',
              'content' => [
                  [
                      'type' => 'text',
                      'text' => 'Example text with ',
                  ],
                  [
                    'type' => 'text',
                    'text' => 'more text',
                ],
              ],
            ], 'Example text with more text'],
        ];
    }

}
