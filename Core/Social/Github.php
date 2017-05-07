<?php
namespace Core\Social;

use Core\Session;

class Github
{
    public $client_id;
    public $client_secret;
    public $scope;
    public $session;
    public $access_token;
    public $error_message;

    public function __construct()
    {
        $this->session  = new Session();
        $this->client_id = '1bcb0cc11ed8418b4cba';
        $this->client_secret = '2c7d5434f1d52324eaf9696054c9536e3459897f';
        $this->scope = 'user,user:email';
    }

    public function get_login_url()
    {
        return 'https://github.com/login/oauth/authorize?client_id='.$this->client_id.'&scope='.$this->scope;
    }

    public function authorize()
    {
        if (!isset($_GET['code'])) {
            return FALSE;
        }

        $code = $_GET['code'];

        //at this point, user has accepted the application, $_GET['state'] == session_id -- time/safe to proceed and retrieve the access_token
        $request = $this->request_access_token($code);

        //checks to see if access_token was returned successfully (if error occurs, it's likely because the temporary code param has expired)
        if (isset($request->error))
        {
            $this->_set_error('There was an error because things were probably happening too slow! Possibly a timeout or an expired code parameter. Please try again!');
            return FALSE;
        }

        //request for access token was a success! Store the access_token in the session for future use
        $this->set_access_token($request->access_token);
        $this->session->set('access_token', $request->access_token);

        return TRUE;
    }

    public function request_access_token($code)
    {
        $data = array(
            'client_id'        => $this->client_id,
            'client_secret' => $this->client_secret,
            'code'        => $code,
            'accept' => 'json'
        );
        return $this->curl_new('https://github.com/login/oauth/access_token', $data, 'POST');
    }

    public function get_access_token()
    {
        return $this->access_token;
    }

    /*	Note:
    The access_token is stored in the session when "authorize()"
    is called, so you'll only need to use this if you're using
    a different access_token than the current logged in user.
    You'll see in the construct of this file that the access_token
    is set by the session each time this library is initialized/loaded. */
    public function set_access_token($access_token)
    {
        $this->access_token = $access_token;
        return;
    }

    public function rate_limit()
    {
        return $this->curl('rate_limit');
    }

    public function user()
    {
        return $this->curl_new('https://api.github.com/user?access_token='.
            $this->session->get('access_token'));
    }

    public function user_email()
    {
        $emails = $this->curl_new('https://api.github.com/user/emails?access_token='.
            $this->session->get('access_token'));
        if (count($emails)) {
            return $emails[0]->email;
        }
        return '';
    }

    public function list_gists()
    {
        return $this->curl('gists');
    }

    public function read_gist($id)
    {
        return $this->curl('gists/'.$id);
    }

    public function create_gist($body = array())
    {
        return $this->curl('gists', 'POST', $body);
    }

    public function edit_gist($id, $body = array())
    {
        return $this->curl('gists/'.$id, 'PATCH', $body);
    }

    public function delete_gist($id)
    {
        return $this->curl('gists/'.$id, 'DELETE', '');
    }

    public function curl_new($uri, $data = array(), $verb = 'GET')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=utf-8","Accept:application/json"));
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($verb == 'POST') {
            $json_data = json_encode($data);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        }
        $result = curl_exec($ch);

        return json_decode($result);
    }

    public function curl($uri, $verb = 'GET', $body = array(), $headers = FALSE)
    {
        $url = (preg_match('#^www|^http|^//#', $uri)) ? $uri : $this->api_url.$uri.'?access_token='.$this->access_token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");

        if ($headers)
        {
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
        }

        if (!empty($body))
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        switch ($verb)
        {
            case 'POST' :
                curl_setopt($ch, CURLOPT_POST, 1);
                break;
            case 'PATCH' :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                break;
            case 'PUT' :
                curl_setopt($ch, CURLOPT_PUT, 1);
                break;
            case 'DELETE' :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default :
                break;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        $output = curl_exec($ch);

        if ($headers)
            $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        else
            $result = json_decode($output);

        curl_close($ch);

        if (isset($result->message))
        {
            $this->_set_error($result->message);
            return FALSE;
        }

        return $result;
    }

    public function get_error()
    {
        if (!$this->error_message)
            return FALSE;

        $error_message = $this->error_message;
        $this->error_message = FALSE;

        return $error_message;
    }

    protected function _set_error($message)
    {
        $this->error_message = $message;
        return FALSE;
    }
}