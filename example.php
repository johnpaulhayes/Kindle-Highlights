<?php

require_once '../configs/kindle_highlights_config.php';
require_once 'KindleHighlights.php';

$kindle = new KindleHighlights($email, $password);
$kindle->buildLoginStructure();
$kindle->Login();

//$arBooks = $kindle->getListOfBooks();


$kindle->getHighlightsForBook('https://kindle.amazon.com/work/pragmatic-programmer-journeyman-master-ebook/B000ACWUG0/B000SEGEKI');

foreach($kindle->arHighlights as $highlight) {
    echo '<pre>';
    print_r($highlight);
    echo '</pre>';
}