<?php

namespace ORTInteractive\Froxlor;

use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Session;
use phpQuery;

/**
 * Class Froxlor
 * @package Mpociot\Froxlor
 */
class Froxlor
{
    /** @var Browser */
    protected $browser;

    /** @var Session */
    protected $session;

    /** @var string */
    protected $baseUrl;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /**
     * Froxlor constructor.
     * @param $baseUrl
     * @param $username
     * @param $password
     */
    public function __construct($baseUrl, $username, $password)
    {
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->password = $password;

        $this->session = new Session(new GoutteDriver());
        $this->session->start();
    }

    /**
     * Perform login
     */
    protected function login()
    {
        $this->session->visit($this->baseUrl);
        $page = $this->session->getPage();
        $page->find('css', '#loginname')->setValue($this->username);
        $page->find('css', '#password')->setValue($this->password);
        $page->find('css', 'form')->submit();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getDomains()
    {
        $this->login();
        $page = $this->session->getPage();
        $page->find('css', 'a[href*="page=domains"]')->click();

        $document = phpQuery::newDocumentHTML($page->getOuterHtml(), 'utf-8');
        $domains = $document->find('#maincontent table.full tbody tr td:nth-child(1)')->texts();
        $paths = $document->find('#maincontent table.full tbody tr td:nth-child(2)')->texts();
        $domainResult = [];

        foreach ($domains as $key => $domain) {
            $domainResult[] = [
                'domain' => trim($domain),
                'path' => trim($paths[$key])
            ];
        }
        return collect($domainResult);
    }

    /**
     * @param $repository
     * @param $user
     * @param string $publicPath
     */
    public function createDomain($repository, $user, $publicPath = 'public')
    {
        $this->login();

        $page = $this->session->getPage();
        $page->find('css', 'a[href*="page=domains"]')->click();
        $page->find('css', 'a[href*="page=domains&action=add"]')->click();

        $page->find('css', '#subdomain')->setValue($user.'.'.$repository);
        $page->find('css', '#path')->setValue('/'.$user.'/'.$repository.'/'.$publicPath);
        $page->find('css', 'form')->submit();
    }

    /**
     * @param $description
     * @return array
     */
    public function createDatabase($description)
    {
        $this->login();

        $page = $this->session->getPage();

        $page->find('css', 'a[href*="page=mysqls"]')->click();
        $page->find('css', 'a[href*="page=mysqls&action=add"]')->click();

        $password = $page->find('css', '#mysql_password_suggestion')->getValue();

        $page->find('css', '#description')->setValue($description);
        $page->find('css', '#mysql_password')->setValue($password);
        $page->find('css', 'form')->submit();

        $page->find('css', 'a[href*="sortfield=databasename&sortorder=desc"]')->click();

        return [
            'username' => $page->find('css', '#maincontent table.full tbody tr:nth-child(1) td:nth-child(1)')->getText(),
            'password' => $password
        ];
    }

    /**
     * @param $domain
     * @return bool
     */
    public function hasDomain($domain)
    {
        return $this->getDomains()->where('domain', $domain)->count() > 0;
    }
}
