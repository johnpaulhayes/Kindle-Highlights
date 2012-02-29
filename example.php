<?php
// Load in your configs. For sanity I've place mine in a director
// Above here and have not included them in this repo.
// This file contains your kindle login details:
// $email = 'your@emailaddress.com';
// $password = 'somepassword';

require_once '../configs/kindle_highlights_config.php';
require_once 'KindleHighlights.php';

$kindle = new KindleHighlights($email, $password);
$kindle->buildLoginStructure();
$kindle->Login();

$kindle->getHighlightsForBook('https://kindle.amazon.com/work/pragmatic-programmer-journeyman-master-ebook/B000ACWUG0/B000SEGEKI');

foreach($kindle->arHighlights as $highlight) {
    echo '<pre>';
    print_r($highlight);
    echo '</pre>';
}