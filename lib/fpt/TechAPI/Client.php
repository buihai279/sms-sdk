<?php

namespace DiagVN\Fpt\TechAPI;

class Client
{
    /**
     * Client ID
     * 
     * @var string
     */
    protected $client_id = '';

    /**
     * Client secret
     * 
     * @var string
     */
    protected $client_secret = '';

    /**
     * Scopes
     * 
     * @var array
     */
    protected $scopes = [];


    /**
     * Construction and configs client
     * 
     * @param string $client_id
     * @param string $client_secret
     * @param array $scopes
     */
    public function __construct($client_id, $client_secret, array $scopes = [])
    {
        // restrict client id
        if (empty($client_id)) {
            Error::setError(Error::EMPTY_CLIENT_ID);
        }
        $this->client_id = $client_id;

        // restrict client secret
        if (empty($client_secret)) {
            Error::setError(Error::EMPTY_CLIENT_SECRET);
        }
        $this->client_secret = $client_secret;

        // set scope
        $this->scopes = $scopes;
    }


    /**
     * Get client ID
     * 
     * @return string
     */
    public function getClientID()
    {
        return $this->client_id;
    }


    /**
     * Get client secret
     * 
     * @return string
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }


    /**
     * Get Client Scopes
     * 
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }


    /**
     * Get Auth info
     * 
     * @return array
     */
    public function getAuth()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        return array(
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'scope'         => implode(' ', $this->scopes),
            'session_id'    => session_id(),
        );
    }
}
