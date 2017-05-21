<?php
namespace App;

class Helper
{
    public static function getUrl($url = '')
    {
        return Config::getConfig('base_url') . $url;
    }

    public static function getLink($url = '')
    {
        global $router;
        $params = $router->getParams();
        $lang = $params['lang'];
        if ($lang != 'vi') {
            return Config::getConfig('base_url') . $lang . '/' . $url;
        }
        return Config::getConfig('base_url') . $url;
    }

    // translate helpers
    public static function __($key, $default = '') {
        if (!$default) {
            $default = $key;
        }
        return \Core\Language::get($key, $default);
    }

    public static function getPath($folder = '')
    {
        return dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . $folder;
    }

    public static function getAdminUrl($url = '')
    {
        return Config::getConfig('base_url') . Config::getConfig('admin_uri') . '/' . $url;
    }

    public static function selectOption($name,$data,$selected,$value,$text,$exten)
    {
        $ouput=array();
        $ouput[]='<select name="'.$name.'" '.$exten.'>';
        if(!empty($data)){
            foreach($data as $k=>$v){
                if(is_object($v)){
                    $exten_option = (isset($v->exten)?" ".$v->exten." ":"");
                    if(is_array($selected)){
                        if(in_array((string)$v->$value,$selected)){
                            $ouput[] = '<option value="'.$v->$value.'" selected="selected" '.$exten_option.'>'.$v->$text.'</option>';
                        }else{
                            $ouput[] = '<option value="'.$v->$value.'" '.$exten_option.'>'.$v->$text.'</option>';
                        }
                    }else{
                        if((string)$v->$value==(string)$selected){
                            $ouput[] = '<option value="'.$v->$value.'" selected="selected" '.$exten_option.'>'.$v->$text.'</option>';
                        }else{
                            $ouput[] = '<option value="'.$v->$value.'" '.$exten_option.'>'.$v->$text.'</option>';
                        }
                    }
                }else{//array
                    $exten_option = (isset($v["exten"])?" ".$v["exten"]." ":"");
                    if(is_array($selected)){
                        if(in_array((string)$v[$value],$selected)){
                            $ouput[] = '<option value="'.$v[$value].'" selected="selected" '.$exten_option.'>'.$v[$text].'</option>';
                        }else{
                            $ouput[] = '<option value="'.$v[$value].'" '.$exten_option.'>'.$v[$text].'</option>';
                        }
                    }else{
                        if((string)$v[$value]==(string)$selected){
                            $ouput[] = '<option value="'.$v[$value].'" selected="selected" '.$exten_option.'>'.$v[$text].'</option>';
                        }else{
                            $ouput[] = '<option value="'.$v[$value].'" '.$exten_option.'>'.$v[$text].'</option>';
                        }
                    }
                }
            }
        }
        $ouput[]='</select>';
        return implode(' ',$ouput);
    }
}