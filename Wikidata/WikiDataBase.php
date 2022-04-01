<?php
namespace Optimeta\Shared\WikiData;

use GuzzleHttp\Exception\GuzzleException;
use Http\Client\Exception;

class WikiDataBase
{
    /**
     * @desc User agent name to identify our bot
     * @var string
     */
    protected $userAgent = 'OJS Optimeta Plugin';

    /**
     * @desc The bots username
     * @var string
     */
    protected $username = '';

    /**
     * @desc The bots password
     * @var string
     */
    protected $password = '';

    /**
     * @desc Whether the bot is logged in
     * @var bool
     */
    protected $isLoggedIn = false;

    /**
     * @desc Token for editing (see http://www.mediawiki.org/wiki/Manual:Edit_token)
     * @var string
     */
    protected $token = '';

    /**
     * @desc The url to the api
     * @var string
     */
    protected $url = 'https://www.wikidata.org/w/api.php';

    /**
     * @desc Contains the last error the bot had.
     * @var string
     */
    protected $lastError;

    /**
     * @desc GuzzleHttp object
     * @var object (class)
     */
    protected $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
    }

    /**
     * @desc Logs the account.
     * @param string $username - The account's username.
     * @param string $password - The account's password.
     * @returns bool - Returns true on success, false on failure.
     * @throws GuzzleException
     */
    public function login(string $username, string $password): bool
    {
        $post = array(
            'lgname' => $username,
            'lgpassword' => $password
        );

        while (true) {
            $return = $this->query(array('action' => 'login'), $post);
            var_dump($return);
            if ($return['login']['result'] == 'Success') {
                $this->isLoggedIn = true;
                return true;
            } elseif ($return['login']['result'] == 'NeedToken') {
                $post['lgtoken'] = $return['login']['token'];
            } else {
                $this->lastError = $return['login']['code'];
                return false;
            }
        }
    }

    /**
     * @desc Logs the account out of the wiki and destroys all their session data.
     */
    public function logout()
    {
        $this->query(array('action' => 'logout'));
        $this->token = null;
        $this->lastError = null;
        $this->isLoggedIn = false;
    }

    /**
     * @desc Returns edit token.
     * @param bool (default=false) $force - Force the script to get a fresh edit token.
     * @returns mixed - Returns the account's token on success or false on failure.
     * @throws GuzzleException
     */
    public function getToken($force = false)
    {
        if ($this->token != null && $force == false)
            return $this->token;
        $x = $this->query(array('action' => 'query', 'meta' => 'tokens'));
        return $x['query']['tokens']['csrftoken'];
    }

    /**
     * @desc Returns the last error the script ran into.
     * @returns string
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * @desc Execute action query (GET) against API and return as array
     * @param array $query
     * @return array
     * @throws GuzzleException
     */
    public function actionQueryGet(array $query): ?string
    {
        $query['action'] = 'query';
        $query['format'] = 'json';
        $queryString = '?' . http_build_query($query);

        $response = $this->client->request('GET', $this->url . $queryString);

        return $response->getBody();
    }

    /**
     * @desc Execute action wbgetentities (GET) against API and return as array
     * @param array $query
     * @return string|null
     * @throws GuzzleException
     */
    public function actionWbGetEntitiesGet(array $query): ?string
    {
        $query['action'] = 'wbgetentities';
        $query['format'] = 'json';

        $queryString = '?' . http_build_query($query);

        $response = $this->client->request('GET', $this->url . $queryString);

        return $response->getBody();
    }

    /**
     * @desc Get entity with action query
     * @param string $searchString
     * @return string
     * @throws GuzzleException
     */
    public function getEntity(string $searchString): string
    {
        if (empty($searchString)) return '';
        $entity = '';
        $query = [
            "prop" => "",
            "list" => "search",
            "srsearch" => $searchString,
            "srlimit" => "2"
        ];

        try{
            $response = json_decode($this->actionQueryGet($query), true);
            $entity = $response['query']['search'][0]['title'];
            if($entity === null) $entity = '';
        }
        catch(Exception $ex){}

        return $entity;
    }

    /**
     * * @desc Get doi with action get entities
     * @param string $entity
     * @return string
     * @throws GuzzleException
     */
    public function getDoi(string $entity): string
    {
        if (empty($entity)) return '';
        $doi = '';
        $query = [ "ids" => $entity ];

        try{
            $response = json_decode($this->actionWbGetEntitiesGet($query), true);
            $doi = $response['entities'][$entity]['claims']['P356'][0]['mainsnak']['datavalue']['value'];
            if($doi === null) $doi = '';
        }
        catch(Exception $ex){}

        return $doi;
    }
}
