<?php
namespace Core;

use App\Helper;

class TwigExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_Function('path', [$this, 'path']),
            new \Twig_Function('admin_path', [$this, 'adminPath']),
            new \Twig_Function('is_login', [$this, 'isLogin']),
            new \Twig_Function('get_login_user', [$this, 'getLoginUser']),
            new \Twig_Function('select_option', [$this, 'selectOption'], ['is_safe' => ['html']]),
            new \Twig_Function('btn_active', [$this, 'btnActive'], ['is_safe' => ['html']])
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_Filter('time_ago', [$this, 'timeAgo'])
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

    public function btnActive($isActive, $activeUrl, $inActiveUrl)
    {
        $ouput = array();
        if ($isActive) {
            $ouput[] = '<a class="btn btn-success btn-xs" disabled="disabled"><span class="glyphicon glyphicon-ok"></span></a>';
            $ouput[] = '<a href="'.$inActiveUrl.'" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span></a>';
        } else {
            $ouput[] = '<a href="'.$activeUrl.'" class="btn btn-success btn-xs"><span class="glyphicon glyphicon-ok"></span></a>';
            $ouput[] = '<a class="btn btn-danger btn-xs" disabled="disabled"><span class="glyphicon glyphicon-remove"></span></a>';
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
            return ($val == 'second') ? 'a few seconds ago' :
                (($numberOfUnits > 1) ? $numberOfUnits : 'a')
                . ' ' . $val . (($numberOfUnits > 1) ? 's' : '') . ' ago';
        }
        return 'a few seconds ago';
    }
}