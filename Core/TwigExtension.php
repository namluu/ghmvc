<?php
namespace Core;

use App\Helper;

class TwigExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_Function('path', [$this, 'path']),
            new \Twig_Function('mlpath', [$this, 'mlpath']),
            new \Twig_Function('admin_path', [$this, 'adminPath']),
            new \Twig_Function('is_login', [$this, 'isLogin']),
            new \Twig_Function('get_login_user', [$this, 'getLoginUser']),
            new \Twig_Function('select_option', [$this, 'selectOption'], ['is_safe' => ['html']]),
            new \Twig_Function('select_color', [$this, 'selectColor'], ['is_safe' => ['html']]),
            new \Twig_Function('btn_active', [$this, 'btnActive'], ['is_safe' => ['html']]),
            new \Twig_Function('ckeditor', [$this, 'ckeditor'], ['is_safe' => ['html']]),
            new \Twig_Function('avatar', [$this, 'avatar'], ['is_safe' => ['html']]),
            new \Twig_Function('get_user_notification', [$this, 'getUserNotification'], ['is_safe' => ['html']]),
            new \Twig_Function('count_user_notification', [$this, 'countUserNotification']),
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_Filter('time_ago', [$this, 'timeAgo']),
            new \Twig_Filter('add_request_param', [$this, 'addRequestParam']),
            new \Twig_Filter('remove_request_param', [$this, 'removeRequestParam']),
            new \Twig_Filter('trans', [$this, 'trans'])
        ];
    }

    public function path($string = '')
    {
        return Helper::getUrl($string);
    }

    public function adminPath($string = '')
    {
        return Helper::getAdminUrl($string);
    }

    public function mlpath($string = '')
    {
        return Helper::getLink($string);
    }

    public function trans($key)
    {
        return \Core\Language::get($key, $key);
    }

    public function isLogin()
    {
        return $this->getLoginUser() != null;
    }

    public function getLoginUser()
    {
        return Session::get('login_user');
    }

    public function selectOption($name = '', $data = [], $selected = null, $exten = '', $value = 'id', $text = 'name')
    {
        $ouput = array();
        $ouput[] = '<select name="' . $name . '" ' . $exten . '>';
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                if (is_object($v)) {
                    $exten_option = (isset($v->exten) ? " " . $v->exten . " " : "");
                    if (is_array($selected)) {
                        if (in_array((string)$v->$value, $selected)) {
                            $ouput[] = '<option value="' . $v->$value . '" selected="selected" ' . $exten_option . '>' . $v->$text . '</option>';
                        } else {
                            $ouput[] = '<option value="' . $v->$value . '" ' . $exten_option . '>' . $v->$text . '</option>';
                        }
                    } else {
                        if ((string)$v->$value == (string)$selected) {
                            $ouput[] = '<option value="' . $v->$value . '" selected="selected" ' . $exten_option . '>' . $v->$text . '</option>';
                        } else {
                            $ouput[] = '<option value="' . $v->$value . '" ' . $exten_option . '>' . $v->$text . '</option>';
                        }
                    }
                } else {//array
                    $exten_option = (isset($v["exten"]) ? " " . $v["exten"] . " " : "");
                    if (is_array($selected)) {
                        if (in_array((string)$v[$value], $selected)) {
                            $ouput[] = '<option value="' . $v[$value] . '" selected="selected" ' . $exten_option . '>' . $v[$text] . '</option>';
                        } else {
                            $ouput[] = '<option value="' . $v[$value] . '" ' . $exten_option . '>' . $v[$text] . '</option>';
                        }
                    } else {
                        if ((string)$v[$value] == (string)$selected) {
                            $ouput[] = '<option value="' . $v[$value] . '" selected="selected" ' . $exten_option . '>' . $v[$text] . '</option>';
                        } else {
                            $ouput[] = '<option value="' . $v[$value] . '" ' . $exten_option . '>' . $v[$text] . '</option>';
                        }
                    }
                }
            }
        }
        $ouput[] = '</select>';
        return implode(' ', $ouput);
    }

    public function selectColor($name = '', $data = [], $selected = null, $exten = '', $value = 'id', $color = 'color')
    {
        $ouput = array();
        $ouput[] = '<select name="' . $name . '" ' . $exten . '>';
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $exten_option = (isset($v->exten) ? " " . $v->exten . " " : "");
                if ((string)$v[$value] == (string)$selected) {
                    $ouput[] = '<option value="' . $v[$value] . '" selected="selected" ' . $exten_option . ' data-color="'.$v[$color].'"></option>';
                } else {
                    $ouput[] = '<option value="' . $v[$value] . '" ' . $exten_option . ' data-color="'.$v[$color].'"></option>';
                }
            }
        }
        $ouput[] = '</select>';
        return implode(' ', $ouput);
    }

    public function btnActive($isActive, $activeUrl, $inActiveUrl)
    {
        $ouput = array();
        if ($isActive) {
            $ouput[] = '<a class="btn btn-success btn-xs" disabled="disabled" title="Active"><span class="glyphicon glyphicon-ok"></span></a>';
            $ouput[] = '<a href="'.$inActiveUrl.'" class="btn btn-disable btn-xs" title="Disable"><span class="glyphicon glyphicon-remove"></span></a>';
        } else {
            $ouput[] = '<a href="'.$activeUrl.'" class="btn btn-success btn-xs" title="Active"><span class="glyphicon glyphicon-ok"></span></a>';
            $ouput[] = '<a class="btn btn-disable btn-xs" disabled="disabled" title="Disable"><span class="glyphicon glyphicon-remove"></span></a>';
        }
        return implode(' ', $ouput);
    }

    public function timeAgo($datetime)
    {
        $time = time() - strtotime($datetime);

        $units = array(
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($units as $unit => $val) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return ($val == 'second') ? Helper::__('a few seconds ago') :
                (($numberOfUnits > 1) ? $numberOfUnits : Helper::__('a'))
                . ' ' . Helper::__($val . (($numberOfUnits > 1) ? 's' : '')) .' '. Helper::__('ago');
        }
        return Helper::__('a few seconds ago');
    }

    public function ckeditor($name='content',$value='',$template='Full',$width = '100%',$height='100px',$extent='')
    {
        $ckeditor = Ckeditor::getInstance();
        return $ckeditor->create_editor($name, $value, $template, $width, $height, $extent);
    }

    public function avatar($name, $size = 150, $ext = '')
    {
        if ($name) {
            // full link - social
            if (strpos($name, 'http') !== false) {
                $link = $name;
            } else {
                // file name
                if ($size == 'full') {
                    $link = $this->path('uploads/user/' . $name);
                } else {
                    $link = $this->path('uploads/user/thumbnail/' . $name);
                }
            }
        } else {
            $link = $this->path('uploads/user/thumbnail/avatar_big.jpg');
        }


        return sprintf('<img src="%s" width="%s" %s>', $link, $size, $ext);
    }

    // with filter, first param is the object left of the filter
    public function addRequestParam($url, $params)
    {
        return Url::addRequestParam($url, $params);
    }

    public function removeRequestParam($url, $params)
    {
        return Url::removeRequestParam($url, $params);
    }

    public function getUserNotification($userId)
    {
        $notiModel = new \App\Module\User\Model\Notification();
        $notifications = $notiModel->getNotificationUser($userId, 7);
        return $notifications;
    }

    public function countUserNotification($userId)
    {
        $notiModel = new \App\Module\User\Model\Notification();
        $notifications = $notiModel->countNewUpdateVersions($userId);
        return $notifications;
    }
}