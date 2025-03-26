<?php

require_once 'api/md2html.php'; // Test original version
// require_once 'api/md2html_fixes.php'; // Test fixed version


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
            ['This is a paragraf', '<p>This is a paragraf</p>'], // Normal text
            // ['# First', '<h1>First</h1>'], // Header one - FAILS because of space in front
            // ['## Second', '<h2>Second</h2>'], // Header two - FAILS because of space in front
            // ['### Third', '<h3>Third</h3>'], // Header three - FAILS because of space in front
            ['This is a **Bold** test', '<p>This is a <strong>Bold</strong> test</p>'], // Bold
            ['This a *Italic* test', '<p>This a <em>Italic</em> test</p>'], // Italic
            // ['> This a Blockquote', '<blockquote>This a Blockquote<br></blockquote>'], // Blockquote - FAILS because of character entities is being replaced before checking for > Blockquote
            // ['> This is a quote \n > multiple lines', '<blockquote>This is a quote<br>multiple lines<br></blockquote>'], // Multi lined Blockquote - FAILS because of character entities is being replaced before checking for > Blockquote
            // ['- PHP\n- MariaDB\n- jQuery', '<ul><li>PHP</li><li>MariaDB</li><li>jQuery</li></ul>'], // Unordered list - FAILS because of \n not being replaced and exploded correctly and missing </ul> closing tag
            // ['1. Create the database \n 2. Copy the files to your own server \n 3. Run the app at localhost', '<ol><li>Create the database</li><li>Copy the files to your own server</li><li>Run the app at localhost</li></ol>'], // Ordered list - FAILS because of \n fail and missing </ol> closing tag
            // ['```\n$server = "localhost";\n$user = "root";\n$pwd = "";\n```', '<code><br>$server = "localhost";<br>$user = "root";<br>$pwd = "";<br></code>'], // Code block multi lined - FAILS because of repeating starting and closing code tags in front and in the end (It does not support multi line coding blocks), and \n fails
            // ['`\n$server = "localhost";\n$user = "root";\n$pwd = "";\n`', '<code><br>$server = "localhost";<br>$user = "root";<br>$pwd = "";<br></code><br>'], // Code block - FAILS because of \n fails
            ['---','<hr>'], // Horizontal line
            ['Welcome to [KEA](https://www.kea.dk).','<p>Welcome to <a href="https://www.kea.dk" target="_blank" title="KEA">KEA</a>.</p>'], // Links
            ['First [KEA](https://www.kea.dk), second [KEA](KEA.DK)','<p>First <a href="https://www.kea.dk" target="_blank" title="KEA">KEA</a>, second <a href="KEA.DK" target="_blank" title="KEA">KEA</a></p>'], // Mulitple links
            ['[This] is not a URL','<p>[This] is not a URL</p>'], // Not a link - Paragraph almost like a link
            ['image: ![KEA](https://kea.dk/slir/w640-c100x60/images/efteruddannelser/webinarer-paa-kea-teaser.jpg.webp)','<p>image: <img src="https://kea.dk/slir/w640-c100x60/images/efteruddannelser/webinarer-paa-kea-teaser.jpg.webp" alt="KEA"></p>'], // Image
        ];
    }
}