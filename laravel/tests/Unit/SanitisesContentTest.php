<?php

namespace Tests\Unit;

use App\Traits\SanitisesContent;
use Tests\TestCase;

class SanitisesContentTest extends TestCase
{
    private object $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new class {
            use SanitisesContent;

            public function sanitise(?string $html): ?string
            {
                return $this->sanitiseHtml($html);
            }
        };
    }

    public function test_script_tags_are_stripped(): void
    {
        // Arrange
        $dirty = '<p>Hello</p><script>alert("xss")</script>';

        // Act
        $clean = $this->subject->sanitise($dirty);

        // Assert
        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringContainsString('<p>Hello</p>', $clean);
    }

    public function test_event_handler_attributes_are_stripped(): void
    {
        // Arrange
        $dirty = '<p onmouseover="alert(1)">Hover me</p>';

        // Act
        $clean = $this->subject->sanitise($dirty);

        // Assert
        $this->assertStringNotContainsString('onmouseover', $clean);
        $this->assertStringContainsString('Hover me', $clean);
    }

    public function test_allowed_inline_formatting_tags_survive(): void
    {
        // Arrange
        $html = '<p><strong>Bold</strong> <em>Italic</em> <u>Underline</u></p>';

        // Act
        $clean = $this->subject->sanitise($html);

        // Assert
        $this->assertStringContainsString('<strong>Bold</strong>', $clean);
        $this->assertStringContainsString('<em>Italic</em>', $clean);
        $this->assertStringContainsString('<u>Underline</u>', $clean);
    }

    public function test_allowed_block_tags_survive(): void
    {
        // Arrange
        $html = '<h2>Heading</h2><ul><li>Item</li></ul><blockquote>Quote</blockquote>';

        // Act
        $clean = $this->subject->sanitise($html);

        // Assert
        $this->assertStringContainsString('<h2>Heading</h2>', $clean);
        $this->assertStringContainsString('<ul><li>Item</li></ul>', $clean);
        $this->assertStringContainsString('<blockquote>Quote</blockquote>', $clean);
    }

    public function test_anchor_tag_with_href_survives(): void
    {
        // Arrange
        $html = '<a href="https://example.com">Link</a>';

        // Act
        $clean = $this->subject->sanitise($html);

        // Assert
        $this->assertStringContainsString('href="https://example.com"', $clean);
        $this->assertStringContainsString('Link', $clean);
    }

    public function test_null_is_returned_unchanged(): void
    {
        // Act + Assert
        $this->assertNull($this->subject->sanitise(null));
    }

    public function test_empty_string_is_returned_unchanged(): void
    {
        // Act + Assert
        $this->assertSame('', $this->subject->sanitise(''));
    }
}
