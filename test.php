<?php
$ctx = stream_context_create(['http' => ['ignore_errors' => true]]);
$html = file_get_contents('https://mundabit-api.vercel.app/', false, $ctx);
if (preg_match('/"message":"(.*?)"/', $html, $matches)) {
    echo "Message: " . $matches[1] . "\n";
}
if (preg_match('/"class":"(.*?)"/', $html, $matches)) {
    echo "Class: " . $matches[1] . "\n";
}
