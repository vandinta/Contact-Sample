<?php

namespace App\Services;

use Google_Client;
use Google_Service_PeopleService;

class GooglePeopleService
{
    protected $client;
    
    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->addScope(Google_Service_PeopleService::CONTACTS_READONLY);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getPeopleService()
    {
        return new Google_Service_PeopleService($this->client);
    }
}
