<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EditorJsRenderer;

class EditorJsListRenderingTest extends TestCase
{
    private EditorJsRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new EditorJsRenderer();
    }

    /** @test */
    public function it_handles_mixed_list_item_types_without_errors()
    {
        // Test data that would previously cause the error
        $testData = [
            'blocks' => [
                [
                    'type' => 'list',
                    'data' => [
                        'style' => 'unordered',
                        'items' => [
                            'Simple string item',
                            ['Complex', 'array', 'item'],
                            (object) ['text' => 'Object with text property'],
                            (object) ['content' => 'Object with content property'],
                            null,
                            '',
                            123,
                            true
                        ]
                    ]
                ]
            ]
        ];

        // This should not throw any errors
        $html = $this->renderer->render($testData);

        // Verify HTML is generated
        $this->assertNotEmpty($html);
        $this->assertStringContainsString('<ul>', $html);
        $this->assertStringContainsString('</ul>', $html);
    }

    /** @test */
    public function it_renders_string_list_items_correctly()
    {
        $testData = [
            'blocks' => [
                [
                    'type' => 'list',
                    'data' => [
                        'style' => 'unordered',
                        'items' => ['First item', 'Second item']
                    ]
                ]
            ]
        ];

        $html = $this->renderer->render($testData);

        $this->assertStringContainsString('First item', $html);
        $this->assertStringContainsString('Second item', $html);
        $this->assertStringContainsString('<li>First item</li>', $html);
        $this->assertStringContainsString('<li>Second item</li>', $html);
    }

    /** @test */
    public function it_renders_array_list_items_correctly()
    {
        $testData = [
            'blocks' => [
                [
                    'type' => 'list',
                    'data' => [
                        'style' => 'unordered',
                        'items' => [
                            ['First', 'part'],
                            ['Second', 'part', 'with', 'more']
                        ]
                    ]
                ]
            ]
        ];

        $html = $this->renderer->render($testData);

        $this->assertStringContainsString('First part', $html);
        $this->assertStringContainsString('Second part with more', $html);
    }

    /** @test */
    public function it_renders_object_list_items_correctly()
    {
        $testData = [
            'blocks' => [
                [
                    'type' => 'list',
                    'data' => [
                        'style' => 'unordered',
                        'items' => [
                            (object) ['text' => 'Object with text'],
                            (object) ['content' => 'Object with content']
                        ]
                    ]
                ]
            ]
        ];

        $html = $this->renderer->render($testData);

        $this->assertStringContainsString('Object with text', $html);
        $this->assertStringContainsString('Object with content', $html);
    }

    /** @test */
    public function it_skips_empty_list_items()
    {
        $testData = [
            'blocks' => [
                [
                    'type' => 'list',
                    'data' => [
                        'style' => 'unordered',
                        'items' => [
                            'Valid item',
                            null,
                            '',
                            [],
                            'Another valid item'
                        ]
                    ]
                ]
            ]
        ];

        $html = $this->renderer->render($testData);

        $this->assertStringContainsString('Valid item', $html);
        $this->assertStringContainsString('Another valid item', $html);
        
        // Should only have 2 list items, not 5
        $this->assertEquals(2, substr_count($html, '<li>'));
    }

    /** @test */
    public function it_handles_ordered_lists()
    {
        $testData = [
            'blocks' => [
                [
                    'type' => 'list',
                    'data' => [
                        'style' => 'ordered',
                        'items' => ['First', 'Second', 'Third']
                    ]
                ]
            ]
        ];

        $html = $this->renderer->render($testData);

        $this->assertStringContainsString('<ol>', $html);
        $this->assertStringContainsString('</ol>', $html);
        $this->assertStringContainsString('First', $html);
        $this->assertStringContainsString('Second', $html);
        $this->assertStringContainsString('Third', $html);
    }

    /** @test */
    public function it_returns_empty_string_for_empty_list()
    {
        $testData = [
            'blocks' => [
                [
                    'type' => 'list',
                    'data' => [
                        'style' => 'unordered',
                        'items' => []
                    ]
                ]
            ]
        ];

        $html = $this->renderer->render($testData);

        $this->assertEmpty($html);
    }

    /** @test */
    public function it_returns_empty_string_for_list_with_only_empty_items()
    {
        $testData = [
            'blocks' => [
                [
                    'type' => 'list',
                    'data' => [
                        'style' => 'unordered',
                        'items' => [null, '', [], (object) []]
                    ]
                ]
            ]
        ];

        $html = $this->renderer->render($testData);

        $this->assertEmpty($html);
    }
}