<?php
namespace App;

class Helper
{
    public static function getUrl($url = '')
    {
        return Config::BASE_URL . $url;
    }

    public static function getPath($folder = '')
    {
        return dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . $folder;
    }

    public static function getAdminUrl($url = '')
    {
        return Config::ADMIN_URL . $url;
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