<?php
/**
 * Pseudo Code.
 * Author: John Paul Hayes  - t: @johnpaulhayes
 * 
 * Description
 * I using the Amazon Kindle apps. I have them installed on my iPhone and all
 * my other devices and naturally, I highlight portions of text. Sometimes I like to
 * tweet a quote or two. However, the Kindle app share function only shares a link to a 
 * highlighted text. I don't like this so hence this class.
 * 
 * This class logs you into kindle.amazon.com obtains your list of books and their corresponding URLs
 * 
 */

class KindleHighlights {
    public $yourReadingUrl = 'https://kindle.amazon.com/your_reading';
    public $amazonLoginUrl='https://www.amazon.com/ap/signin?openid.return_to=https%3A%2F%2Fkindle.amazon.com%3A443%2Fauthenticate%2Flogin_callback%3Fwctx%3D%252F&openid.pape.max_auth_age=0&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.mode=checkid_setup&pageId=amzn_kindle&openid.assoc_handle=amzn_kindle&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0';
    public $loginPage = '';
    public $afterLoginPage = '';
    
    public $bookTitleQuery = '//td[@class="titleAndAuthor"]//a';
    public $bookLinkQuery = '//td[@class="titleAndAuthor"]//a/@href';
    public $bookHighlightQuery = '//div[@class="text"]//span[@class="hightlight"]';
    public $bookActionQuery = '//div[@class="text"]//span[@class="highlight"]//span[@class="noteContent"]';
    
    public $bookCount = 0;
    public $books = array(); // Will contain the list of books obtained.
    public $arHighlights = array();
    public $error = '';
    
    public $ch = '';
    
    
    protected $username;
    protected $password;
    
    
    /**
     * Set the username and password
     * @param string $username
     * @param string $password 
     */
    public function setLoginDetails($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }
    
    public function loadLoginPage() {
        $this->ch  = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $this->amazonLoginUrl);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, 'cookie.txt');
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.10 (maverick) Firefox/3.6.13');
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);        
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);

        $this->loginPage = curl_exec($this->ch);
    }
    
    public function loginToKindle($email, $password) {
        // try to find the actual login form
        if (!preg_match('/<form name="signIn".*?<\/form>/is', $this->loginPage, $form)) {
            $this->error = 'Failed to find log in form!';
        }

        $form = $form[0];

        // find the action of the login form
        if (!preg_match('/action="([^"]+)"/i', $form, $action)) {
            $this->error = 'Failed to find login form url';
        }

        $loginAction = $action[1]; // this is our new post url

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

        curl_setopt($this->ch, CURLOPT_URL, $loginAction);
        curl_setopt($this->ch, CURLOPT_REFERER, 'https://kindle.amazon.com/your_reading');
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post);

        $this->afterLoginPage = curl_exec($this->ch);
    }
    
    public function getListOfBooks() {
        curl_setopt($this->ch, CURLOPT_URL, $this->yourReadingUrl);
        curl_setopt($this->ch, CURLOPT_REFERER, 'https://kindle.amazon.com/your_reading');

        $listOfBooks = curl_exec($this->ch);
        
        $doc = new DOMDocument();
        $doc->loadHTML($listOfBooks);

        $xpath = new DOMXPath($doc);
        $arBookTitles = $xpath->query($this->bookTitleQuery);

        $this->bookCount = 0;
        foreach($arBookTitles as $title) {
            $this->bookCount++;
            $this->books[$this->bookCount] = $title->nodeValue;
        }
    }
    
    public function getHighlightsForBook ($bookUrl) {
        curl_setopt($this->ch, CURLOPT_URL, $bookUrl);
        curl_setopt($this->ch, CURLOPT_REFERER, 'https://kindle.amazon.com/your_reading');
        
        $highlightsPage = curl_exec($this->ch);
        
        $doc = new DOMDocument();
        $doc->loadHTML($highlightsPage);
        
        $xPath = new DOMXPath($doc);
        
        $highlights = $xPath->query('//div[@class="text"]//span[@class="highlight"]');
        $notes = $xPath->query('//div[@class="text"]//p[@class="editNote "]//span[@class="noteContent"]');
        
        foreach($highlights as $key=>$value){
            $myNote = trim($notes->item($key)->nodeValue);
            
            if($myNote == 'tweet') {
                $this->arHighlights[] = $highlights->item($key-2)->nodeValue; // not sure yet why the -2 works
            }
        }
    }
    
    public function getHighlightAction($bookUrl){
        curl_setopt($this->ch, CURLOPT_URL, $bookUrl);
        curl_setopt($ch, CURLOPT_REFERER, 'https://kindle.amazon.com/your_reading');
        $highlightsPage = curl_exec($this->ch);
        
        $doc = new DOMDocument();
        $doc->loadHTML($highlightsPage);
        
        $xpath = new DOMXPath($doc);
        $arBookTitles = $xpath->query('//div[@class="text"]//span[@class=highlight]//span[@class="noteContent]');
    }
}