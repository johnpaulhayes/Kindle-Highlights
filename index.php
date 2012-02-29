<?php
/**
 * Name: John Paul Hayes
 * Email: johnpaul@webdeveloper.cx
 * Date: 15/02/2012
 * 
 * Description: 
 * A routine to login to amazon kindle site, 
 * parse and store your highlights from your kindle books. 
 */



/**
 * Put your login details in this file and keep it out of your git repo for security reasons.
 * $email = 'your@emailaddress.com';
 * $password = 'yourpassword';
 */
require_once '../configs/kindle_highlights_config.php';

$amazonLoginUrl='https://www.amazon.com/ap/signin?openid.return_to=https%3A%2F%2Fkindle.amazon.com%3A443%2Fauthenticate%2Flogin_callback%3Fwctx%3D%252F&openid.pape.max_auth_age=0&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.mode=checkid_setup&pageId=amzn_kindle&openid.assoc_handle=amzn_kindle&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0';

$arBooks = array(
    "Beginning Objective C Programming - Tutorials for the beginner"=>"B006OGB8ZK",
    "clean-code-handbook-craftsmanship"=>"B001GSTOAM",
    "code-complete"=>"B004OR1XGK",
    "debt-first-000-years" =>"B00513DGIO",
    "does-it-matter"=>"B0042FZX4W",
    "drunkards-walk-randomness-rules"=>"B002RI9E0K",
    "filter-bubble-internet-hiding"=>"B004Y4WMH2",
    "mythical-man-month-engineering-anniversary"=>"B000OZ0N6M",
    "pragmatic-programmer-journeyman-master"=>"B000SEGEKI",
    "objective-c-absolute-beginners-programming"=>"B006G3B3DI",
    "thinking-fast-and-slow"=>"B005MJFA2W",
    "new-oxford-american-dictionary"=>"B003ODIZL6"
);


$ch  = curl_init();
curl_setopt($ch, CURLOPT_URL, $amazonLoginUrl);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.10 (maverick) Firefox/3.6.13');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);        
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

$page = curl_exec($ch);

// try to find the actual login form
if (!preg_match('/<form name="signIn".*?<\/form>/is', $page, $form)) {
    die('Failed to find log in form!');
}

$form = $form[0];

// find the action of the login form
if (!preg_match('/action="([^"]+)"/i', $form, $action)) {
    die('Failed to find login form url');
}

$URL2 = $action[1]; // this is our new post url

// find all hidden fields which we need to send with our login, this includes security tokens 
$count = preg_match_all('/<input type="hidden"\s*name="([^"]*)"\s*value="([^"]*)"/i', $form, $hiddenFields);

$postFields = array();

// turn the hidden fields into an array
for ($i = 0; $i < $count; ++$i) {
    $postFields[$hiddenFields[1][$i]] = $hiddenFields[2][$i];
}

// add our login values
$postFields['email']    = $email;
$postFields['create']   = 0;
$postFields['password'] = $password;

$post = '';

// convert to string, this won't work as an array, form will not accept multipart/form-data, 
// only application/x-www-form-urlencoded
foreach($postFields as $key => $value) {
    $post .= $key . '=' . urlencode($value) . '&';
}

$post = substr($post, 0, -1);

curl_setopt($ch, CURLOPT_URL, $URL2);
curl_setopt($ch, CURLOPT_REFERER, $URL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

$page = curl_exec($ch);


$bookUrl = 'https://kindle.amazon.com/your_reading';
curl_setopt($ch, CURLOPT_URL, $bookUrl);
curl_setopt($ch, CURLOPT_REFERER, $URL2);

$your_highlights = curl_exec($ch);

$doc = new DOMDocument();
$doc->loadHTML($your_highlights);

$xpath = new DOMXPath($doc);
$arTableRows = $xpath->query('td[@class="titleAndAuthor"]a/@href');

$count=0;
foreach($arTableRows as $row) {
    $count++;
        echo '<pre>Highlight: ';
        print_r($row->nodeValue);
        echo '</pre>';
}
echo 'Number of highlights: ' . $count . '<br/>';

//highlights($ch, 'https://kindle.amazon.com/work/pragmatic-programmer-journeyman-master-ebook/B000ACWUG0/B000SEGEKI');
//
//function highlights($ch, $bookUrl) {
//    curl_setopt($ch, CURLOPT_URL, $bookUrl);
//    curl_setopt($ch, CURLOPT_REFERER, 'https://kindle.amazon.com/your_reading');
//    $highlightsPage = curl_exec($ch);
//
//    $doc = new DOMDocument();
//    $doc->loadHTML($highlightsPage);
//
//    $xpath = new DOMXPath($doc);
//    $arHighlights = $xpath->query('//span[@class="highlight"]');
//
//    foreach($arHighlights as $highlight) {
//        echo '<pre>highlight: ';
//        print_r($highlight->nodeValue);
//        echo '</pre>';
//    }
//}