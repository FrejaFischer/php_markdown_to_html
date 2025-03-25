<?php

require_once 'md2html.php';

$markdown = trim($_POST['markdown'] ?? '');
if ($markdown === '') {
    echo '<span class="warning">No code to convert.</span>';
} else {
    echo markdown2html($markdown);
}