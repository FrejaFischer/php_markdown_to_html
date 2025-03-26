<?php

/**
 * This is the fixed version of the basic MarkDown to HTML converter.
 * Everything is fixed - EXCEPT multi lined coding blocks
 * 
 * @author  Freja Fischer Nielsen
 * 
 * @param   $code   The MarkDown text to convert to HTML
 * @return  string The text converted to HTML
 */
function markdown2html(string $code): string {
    $html = '';
    $ulOpen = false;
    $olOpen = false;
    $codeOpen = false;
    $blockOngoing = false;  // It will allow tracking the last iteration of a code block
    $blockquoteOpen = false;

    $code = str_replace("\n", '\n', $code);
    $code = explode('\n', $code);
    // $code = str_replace("\n", '\r\n', $code);
    // $code = explode('\r\n', $code);
    
    foreach($code as $line) {
        $line = trim($line);

        // Check for blockquote before replacing `<` and `>`
        if (substr($line, 0, 1) === '>') {

            if(!$blockquoteOpen) {
                $html .= '<blockquote>';
                $blockquoteOpen = true;
            }

            $html .= substr($line, 2) . '<br>';
            continue; // Move to the next line to avoid extra processing
        }

        // If new line is not a continue of a blockquote, then close the block quote
        if ($blockquoteOpen) {
            // If we encounter a non-blockquote line, close the blockquote
            $html .= '</blockquote>';
            $blockquoteOpen = false;
        }
        
        // "<" and ">" are replaced by their corresponding character entities (to avoid injection of JavaScript)
        $line = str_replace('<', '&lt;', $line);
        $line = str_replace('>', '&gt;', $line);
        
        // Unordered and ordered lists replacement
        if (substr($line, 0, 2) === '- ') {
            if (!$ulOpen) {
                $html .= '<ul>';                    
                $ulOpen = true;
            }
            $html .= '<li>' . substr($line, 2) . '</li>';
            continue;
        }

        if (isOL($line)) {
            if (!$olOpen) {
                $html .= '<ol>';                    
                $olOpen = true;
            }
            $html .= '<li>' . substr($line, 3) . '</li>';
            continue;
        }
        
        // If new line is not a list item, then close the unordered / ordered list
        if ($ulOpen) {
            $html .= '</ul>';
            $ulOpen = false;
        }
        if ($olOpen && (!isOL($line))) {
            $html .= '</ol>';
            $olOpen = false;
        }

        $pos = 0;
        // Code replacement
        $pos = strpos($line, "`", $pos);
        while ($pos !== false) {
            if ($codeOpen) {
                if ($pos === 0) {
                    $line = '</code>' . substr($line, $pos + 1);
                } else {
                    $line = substr($line, 0, $pos) . '</code>' . substr($line, $pos + 1);
                }
                $codeOpen = false;
            } else {
                if ($pos === 0) {
                    $line = '<code>';
                    // $line = '<code>' . substr($line, $pos + 1);
                } else {
                    $line = substr($line, 0, $pos) . '<code>' . substr($line, $pos + 1);
                }
                $blockOngoing = true;
                $codeOpen = true;
            }
            $pos = strpos($line, "`", $pos + 1);
        }
        
        // Bold and italic replacement
        $line = replaceByHTMLTags($line, '**', 'strong');
        $line = replaceByHTMLTags($line, '*', 'em');
        
        // Image and URL replacement
        $line = replaceImages($line);
        $line = replaceURLs($line);
        
        if (substr($line, 0, 3) === '###') {
            $html .= '<h3>' . substr($line, 4) . '</h3>';
        } elseif (substr($line, 0, 2) === '##') {
            $html .= '<h2>' . substr($line, 3) . '</h2>';
        } elseif (substr($line, 0, 1) === '#') {
            $html .= '<h1>' . substr($line, 2) . '</h1>';
        } elseif ($line === '---') {
            $html .= '<hr>';
        } elseif (!$blockOngoing && !$blockquoteOpen) {
            // Normal paragraf
            $html .= '<p>' . $line . '</p>';
        } else {
            // A block of code has just been closed in this iteration
            if (!$codeOpen) {
                $blockOngoing = false;
            }
            $html .= $line . '<br>';
        } 
    }

    // Close any open blockquote or unordered lists at the end
    if ($blockquoteOpen) {
        $html .= '</blockquote>';
    }
    if ($ulOpen) {
        $html .= '</ul>';
    }
    if ($olOpen) {
        $html .= '</ol>';
    }
    return $html;
}

/**
 * Checks if the HTML text it receives is an <ol> element
 * 
 * @param   $text   The text to check
 * @return  true if the text is an <ol> element, false otherwise
 */
function isOL(string $text): bool {
    return is_numeric(substr($text, 0, 1)) && substr($text, 1, 2) === '. ';
}

/**
 * It replaces the first two consecutive occurrences of a substring in a text by a pair of HTML tags
 * 
 * @param   $text       The text where to perform the replacement
 * @param   $replaced   The substring to replace
 * @param   $htmlTag    The HTML tag which will replace the substring
 * @return  string The text with the replacements done
 */
function replaceByHTMLTags(string $text, string $replaced, string $htmlTag): string {
    $replacedLength = strlen($replaced);

    $offset = 0;
    $tagOpen = false;
    while (($pos = strpos($text, $replaced, $offset)) !== false) {
        if ($tagOpen) {
            $text = substr_replace($text, "</$htmlTag>", $pos, $replacedLength);
            $tagOpen = false;
        } else {
            $text = substr_replace($text, "<$htmlTag>", $pos, $replacedLength);
            $tagOpen = true;
        }
        $offset = $pos;
    }
    return $text;
}

/**
 * It replaces markdown URLs by HTML URLs in a text
 * 
 * @param   @text   The text where to perform the replacements
 * @return  string The text with the replacements done
 */
function replaceURLs(string $text): string {
    $offset = 0;

    while (($titleStart = strpos($text, '[', $offset)) !== false) {
        if (($titleEnd = strpos($text, '](', $titleStart + 1)) !== false) {
            // This loop prevents any of several opening brackets from being understood as the start of a URL title
            while (($lastOpeningBracket = strpos(substr($text, $titleStart + 1, $titleEnd - $titleStart - 1), '[')) !== false) {
                $titleStart += ($lastOpeningBracket + 1);
            }

            if (($urlEnd = strpos($text, ')', $titleStart + 2)) !== false) {
                $title = substr($text, $titleStart + 1, $titleEnd - $titleStart - 1);
                $text = substr($text, 0, $titleStart) .
                    '<a href="' . substr($text, $titleEnd + 2, $urlEnd - $titleEnd - 2) . '" target="_blank" title="' . $title . '">' .
                    $title . '</a>' . substr($text, $urlEnd + 1);
            }
        }
        $offset = $titleStart + 1;
    }

    return $text;
}

/**
 * It replaces markdown images by HTML images in a text
 * 
 * @param   @text   The text where to perform the replacements
 * @return  string The text with the replacements done
 */
function replaceImages(string $text): string {
    $offset = 0;

    while (($titleStart = strpos($text, '![', $offset)) !== false) {
        if (($titleEnd = strpos($text, '](', $titleStart + 2)) !== false) {
            // This loop prevents any of several opening brackets from being understood as the start of a URL title
            while (($lastOpeningBracket = strpos(substr($text, $titleStart + 2, $titleEnd - $titleStart - 1), '![')) !== false) {
                $titleStart += ($lastOpeningBracket + 1);
            }

            if (($urlEnd = strpos($text, ')', $titleStart + 2)) !== false) {
                $title = substr($text, $titleStart + 2, $titleEnd - $titleStart - 2);
                $text = substr($text, 0, $titleStart) .
                    '<img src="' . substr($text, $titleEnd + 2, $urlEnd - $titleEnd - 2) . '" alt="' . $title . '">' .
                    substr($text, $urlEnd + 1);
            }
        }
        $offset = $titleStart + 2;
    }

    return $text;
}