<?php
/**
 * Author:  John Paul Hayes  - t: @johnpaulhayes
 * Date:    29/02/2012
 * 
 * Disclaimer:
 * Please read the Amazon Kindle Terms and conditions before integrating this into your app.
 * 
 * Description:
 * I using the Amazon Kindle apps. I have them installed on my iPhone and all
 * my other devices and naturally, I highlight portions of text. Sometimes I like to
 * tweet a quote or two. However, the Kindle app share function only shares a link to a 
 * highlighted text. I don't like this so hence this class.
 * 
 * This class logs you into kindle.amazon.com obtains your list of books and their corresponding URLs
 */

class KindleHighlights {
    public $yourReadingUrl = 'https://kindle.amazon.com/your_reading';
    public $amazonLoginUrl='https://www.amazon.com/ap/signin?openid.return_to=https%3A%2F%2Fkindle.amazon.com%3A443%2Fauthenticate%2Flogin_callback%3Fwctx%3D%252F&openid.pape.max_auth_age=0&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.mode=checkid_setup&pageId=amzn_kindle&openid.assoc_handle=amzn_kindle&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0';
    public $loginPage = '';
    public $afterLoginPage = '';
    public $bookTitleXpathQuery = '//td[@class="titleAndAuthor"]//a';
    public $highlightXpathQuery = '//div[@class="text"]//span[@class="highlight"]';
    public $noteContentXpathQuery = '//div[@class="text"]//span[@class=highlight]//span[@class="noteContent]';
    public $highlights;
    public $notes;
    public $error = '';
    public $ch = '';
    public $form;
    public $numberOfHiddenFields;
    public $loginAction;
    public $hiddenFields;
    public $postFields;
    public $post;
    
    protected $username;
    protected $password;
    
    /**
     * Set us up.
     * @param string $username
     * @param string $password 
     */
    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }
    
    
    /**
     * Get the Kindle login page.
     */
    private function getKindleLoginPage() {
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
    
    /**
     * Find the login form for the login page. 
     */
    private function findLoginForm() {
        if (!preg_match('/<form name="signIn".*?<\/form>/is', $this->loginPage, $form)) {
            $this->error = 'Failed to find log in form!';
        }
        $this->form = $form[0];
    }
    
    /**
     *  Find the action of the login form.
     */
    private function getAction() {
        if (!preg_match('/action="([^"]+)"/i', $this->form, $action)) {
            $this->error = 'Failed to find login form url';
        }
        $this->loginAction = $action[1];
    }
    
    /**
     * Get the hidden fields in the form. 
     */
    private function getHiddenFields() {
        // find all hidden fields which we need to send with our login, this includes security tokens 
        $count = preg_match_all('/<input type="hidden"\s*name="([^"]*)"\s*value="([^"]*)"/i', $this->form, $hiddenFields);
        $this->numberOfHiddenFields = $count;
        $this->hiddenFields = $hiddenFields;
    }
    
    /**
     * Create the post fields from the hidden fields found in the form.
     */
    private function createPostFields(){
        $postFields = array();
        for ($i = 0; $i < $this->numberOfHiddenFields; ++$i) {
            $postFields[$this->hiddenFields[1][$i]] = $this->hiddenFields[2][$i];
        }
        $this->postFields = $postFields;
    }
    
    /**
     * Convert the post fields to a url encoded string 
     */
    private function convertPostFieldsToUrlString() {
        $post = '';
        foreach($this->postFields as $key => $value) {
            $post .= $key . '=' . urlencode($value) . '&';
        }

        $this->post = substr($post, 0, -1);
    }
    
    /**
     * Add the users login details to the post fields structure. 
     */
    private function addLoginDetailsToPostFields(){
        // add our login values to the postFields structure
        $this->postFields['email']    = $this->username;
        $this->postFields['create']   = 0;
        $this->postFields['password'] = $this->password;
    }
    
    /**
     * Build the login structure by finding the form on the login page,
     * extracting the form action, form hidden fields, creating post structure,
     * adding the users login details and finally converting the post structure to a 
     * url encoded string. 
     */
    public function buildLoginStructure() {
        $this->getKindleLoginPage();
        $this->findLoginForm();
        $this->getAction();
        $this->getHiddenFields();
           
        $this->createPostFields();
        $this->addLoginDetailsToPostFields();
        $this->convertPostFieldsToUrlString();
    }
    
    /**
     * Login to kindle.amazon.com with the pre-constructed post structure
     */
    public function Login() {
        curl_setopt($this->ch, CURLOPT_URL, $this->loginAction);
        curl_setopt($this->ch, CURLOPT_REFERER, $this->yourReadingUrl);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->post);
        $this->afterLoginPage = curl_exec($this->ch);
    }
    
    /**
     * Get the list of books you have in Kindle 
     */
    public function getListOfBooks() {
        curl_setopt($this->ch, CURLOPT_URL, $this->yourReadingUrl);
        curl_setopt($this->ch, CURLOPT_REFERER, $this->yourReadingUrl);

        $listOfBooks = curl_exec($this->ch);
        
        $doc = new DOMDocument();
        $doc->loadHTML($listOfBooks);

        $xpath = new DOMXPath($doc);
        $arBookTitles = $xpath->query($this->bookTitleXpathQuery);

        $this->bookCount = 0;
        foreach($arBookTitles as $title) {
            $this->bookCount++;
            $this->books[$this->bookCount] = $title->nodeValue;
        }
    }
    
    /** 
     * Get the list of highlights for a given book url.
     * @param String $bookUrl 
     */
    public function getHighlightsForBook ($bookUrl) {
        curl_setopt($this->ch, CURLOPT_URL, $bookUrl);
        curl_setopt($this->ch, CURLOPT_REFERER, 'https://kindle.amazon.com/your_reading');
        
        $highlightsPage = curl_exec($this->ch);
        
        $doc = new DOMDocument();
        $doc->loadHTML($highlightsPage);
        
        $xPath = new DOMXPath($doc);
        
        $this->highlights = $xPath->query($this->highlightXpathQuery);
        $this->notes = $xPath->query($this->noteContentXpathQuery);
        
        // Get sharable highlights
        $this->getHighlightAction();
    }
    
    /** 
     * Get the higlight action
     * @param type $bookUrl 
     */
    public function getHighlightAction(){
        foreach($this->highlights as $key=>$value){
            $myNote = trim($this->notes->item($key)->nodeValue);
            if($myNote == 'tweet') {
                $this->arHighlights[] = $highlights->item($key-2)->nodeValue; // not sure yet why the -2 works
            }
        }
    }
}