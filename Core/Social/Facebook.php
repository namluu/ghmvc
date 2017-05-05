<?php
namespace Core\Social;

class Facebook
{
    public $fb;

    public function __construct()
    {
        $this->fb = new \Facebook\Facebook([
            'app_id' => \App\Config::getConfig('fb_app_id'),
            'app_secret' => \App\Config::getConfig('fb_secret'),
            'default_graph_version' => 'v2.9',
            //'default_access_token' => '{access-token}', // optional
        ]);
    }
}