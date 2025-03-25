<?php

require_once 'api/md2html.php';


use PHPUnit\Framework\TestCase;

Use PHPUnit\Framework\Attributes\DataProvider;

class md2htmlTest extends TestCase
{
    /*
        Test for passes
    */
    #[DataProvider('ConvertPasses')]

    public function testConvertPasses(string $code, string $expected): void
    {
        $html = markdown2html($code);

        $this->assertEquals($expected, $html);
    }

    public static function ConvertPasses(): array
    {
        return [
            ['# First', '<h1>First</h1>'], // Header one
            ['## Second', '<h2>Second</h2>'], // Header two
            ['### Third', '<h3>Third</h3>'], // Header three
            ['This a **Bold** test', '<p>This a <strong>Bold</strong> test</p>'], // Bold
            ['This a *Italic* test', '<p>This a <em>Italic</em> test</p>'], // Italic
            // ['> This a Blockquote', '<blockquote>This a Blockquote<br></blockquote>'], // Blockquote
            // ['> This is a quote \n multiple lines', '<blockquote>This is a quote<br>multiple lines<br></blockquote>'], // Multi lined Blockquote
        ];
    }
}