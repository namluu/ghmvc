<?php
namespace Core\Social;

class Google
{
    public $gClient;

    public function __construct()
    {
        $this->gClient = new \Google_Client();
        $this->gClient->setApplicationName('Login to ghmvc');
        $this->gClient->setClientId('319757846940-300g28331f8js7f2h0b4elpp84qpt3ep.apps.googleusercontent.com');
        $this->gClient->setClientSecret('JYbAAMhpgi6qKUI0QmgTzxcg');
        $this->gClient->setDeveloperKey('AIzaSyAcVHXFUDeojfc9sr1HViDpUbuxIVqwNnA');
        $this->gClient->addScope(\Google_Service_Drive::DRIVE);
    }
}