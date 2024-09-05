<?php

namespace App\Http\Controllers\GoogleService;

use App\Http\Controllers\Controller;
use Google\Service\Drive as GoogleDrive;


class Drive extends Controller
{
    private $client;
    private $userId;
    private $service;

    public function __construct($client, string $userId = "me")
    {
        $this->client = $client;
        $this->userId = $userId;
        $this->service = new GoogleDrive($this->client);
    }

    /**
     * Get Directorys from Drive
     * @return array
     */
    function getDirectorys()
    {
    }

    /**
     * Get the list of Files available in specific directory
     * @param string $directory
     * @return array
     */
    function getFiles($path)
    {
    }

    function useResource()
    {
    }

    function uploadResource()
    {
    }
}
