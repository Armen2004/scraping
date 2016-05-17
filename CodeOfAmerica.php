<?php

require_once("simple_html_dom.php");
require_once("AbstractWebGsScraper2.php");

class CodeOfAmerica extends AbstractWebGsScraper2
{
    private $key = 1;

    function __construct($url, $username = "", $password = "")
    {
        parent::__construct($url, $username, $password); //initialize parent constructor
        $this->setScrapperName(__CLASS__); //set ScraperName
    }

    //your code here....
    function parseJobList()
    {
        $cookie_jar = tempnam('/', 'cookie');
        $this->curl->setOpt(CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        $this->curl->setOpt(CURLOPT_FRESH_CONNECT, false);
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $this->curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
        $this->curl->setOpt(CURLOPT_HEADER, true);
        $this->curl->setOpt(CURLOPT_VERBOSE, false);
        $this->curl->setOpt(CURLOPT_COOKIESESSION, true);
        $this->curl->setOpt(CURLOPT_POST, true);
        $this->curl->setOpt(CURLOPT_COOKIEJAR, $cookie_jar);
        $this->curl->setOpt(CURLOPT_COOKIEFILE, $cookie_jar);
        $parsedURLs = array();
        $content = $this->getContent();
        $this->Check_Status(strlen(trim($content)), 'Possibly blocked by the website.');
        $urls = $this->extractjobstourl($content);
        $urls = array_unique($urls);
        foreach ($urls as $url) {
            if (in_array($url, $parsedURLs)) {
                break;
            }
            $this->setJobToList($url);
            $parsedURLs[] = $url;
        }
    }

    public function loadJobDetails($url)
    {
        $this->curl->get($url);
        $content = $this->curl->getResponse();
        $dom = str_get_html(preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $content));
        $title = trim($dom->find('div[class=container] h1', 0)->plaintext);
        $companyname = 'Code Of America';
        $getKey = explode('/', $url);
        $keyCount = count($getKey);
        $key = trim($getKey[$keyCount - 2]);
        $snippet = trim($dom->find('div[class=description]', 0)->plaintext);
        print_r($snippet);die;
        $city = '';
        $state = '';
        $country = 'USA';
        $location = $dom->find('li[title=Location]', 0)->plaintext;
        $location = trim(str_replace('Location:', '', $location));
        $location = explode(',', $location);
        $city = trim($location[0]);
        if (count($location) > 1) {
            $state = trim($location[1]);
        }
        $this->setJobToList($url, $key, $title, $snippet, $city, $state, $country, date('Y-m-d'), $companyname);
    }

    private function extractjobstourl($content)
    {
        $dom = str_get_html($content);
        $urls = array();
        $as = $dom->find('li[class=list-group-item]');
        foreach ($as as $a) {
            $url = $a->find('a', 0)->attr['href'];
            if (strlen($url) > 0) {
                $urls[] = $url;
            }
        }
        return $urls;
    }

    private function getContent()
    {
        $url = $this->url;
        $this->curl->get($url);
        $content = $this->curl->getResponse();
        return $content;
    }

    private function Check_Status($length, $message)
    {
        if ($length === 0) {
            throw new Exception($message);
        }
    }

    private function clean($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
}

?>