<?php
namespace Core;

use App\Helper;

class TwigExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_Function('path', [$this, 'path']),
            new \Twig_Function('admin_path', [$this, 'adminPath']),
            new \Twig_Function('is_login', [$this, 'isLogin']),
            new \Twig_Function('get_login_user', [$this, 'getLoginUser']),
            new \Twig_Function('select_option', [$this, 'selectOption'], ['is_safe' => ['html']]),
        );
    }

    public function path($string = '')
    {
        return Helper::getUrl($string);
    }

    public function adminPath($string = '')
    {
        return Helper::getAdminUrl($string);
    }

    public function isLogin()
    {
        return $this->getLoginUser() != null;
    }

    public function getLoginUser()
    {
        return Session::get('login_user');
    }

    public function selectOption($name='',$data=[],$selected=null,$exten='',$value='id',$text='name')
    {
        return Helper::selectOption($name,$data,$selected,$value,$text,$exten);
    }
}