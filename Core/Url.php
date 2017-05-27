<?php
namespace Core;

class Url
{

    private $viChars;
    private $enChars;

    public function __construct()
    {
        $diacritics = array(
            $this->utf8(0x0300),
            $this->utf8(0x0301),
            $this->utf8(0x0309),
            $this->utf8(0x0323),
        );

        $this->viChars = array_merge($diacritics, array(
            'Ạ','Ắ','Ằ','Ặ','Ấ','Ầ','Ẩ','Ậ','Ẽ','Ẹ','Ế','Ề','Ể','Ễ','Ệ','Ố',
            'Ồ','Ổ','Ỗ','Ộ','Ợ','Ớ','Ờ','Ở','Ị','Ỏ','Ọ','Ỉ','Ủ','Ũ','Ụ','Ỳ',
            'Õ','ắ','ằ','ặ','ấ','ầ','ẩ','ậ','ẽ','ẹ','ế','ề','ể','ễ','ệ','ố',
            'ồ','ổ','ỗ','Ỡ','Ơ','ộ','ờ','ở','ị','Ự','Ứ','Ừ','Ử','ơ','ớ','Ư',
            'À','Á','Â','Ã','Ả','Ă','ẳ','ẵ','È','É','Ê','Ẻ','Ì','Í','Ĩ','ỳ',
            'Đ','ứ','Ò','Ó','Ô','ạ','ỷ','ừ','ử','Ù','Ú','ỹ','ỵ','Ý','ỡ','ư',
            'à','á','â','ã','ả','ă','ữ','ẫ','è','é','ê','ẻ','ì','í','ĩ','ỉ',
            'đ','ự','ò','ó','ô','õ','ỏ','ọ','ụ','ù','ú','ũ','ủ','ý','ợ','Ữ',
        ));

        $this->enChars = array_merge(array_map(function(){return '';},$diacritics), array(
            'A','A','A','A','A','A','A','A','E','E','E','E','E','E','E','O',
            'O','O','O','O','O','O','O','O','I','O','O','I','U','U','U','Y',
            'O','a','a','a','a','a','a','a','e','e','e','e','e','e','e','o',
            'o','o','o','O','O','o','o','o','i','U','U','U','U','o','o','U',
            'A','A','A','A','A','A','a','a','E','E','E','E','I','I','I','y',
            'D','u','O','O','O','a','y','u','u','U','U','y','y','Y','o','u',
            'a','a','a','a','a','a','u','a','e','e','e','e','i','i','i','i',
            'd','u','o','o','o','o','o','o','u','u','u','u','u','y','o','U',
        ));
    }

    public function slug($str, $params)
    {
        if ($params['toascii'])
            $str = $this->toPlainLatin($str);

        $str = str_replace(array('(',')','-','=',',','.','/','#','?','+','!','@','$','%','^','&','*',':',"\t","\r","\n","　"), " ", $str);
        $str = str_replace(array("'", "’",'"', '“', '”'), '', $str);
        $str = preg_replace('/[\s]{2,}/', ' ', $str);
        $str = preg_replace('/[\s]$/', '', $str);
        $str = str_replace(' ', '-', $str);

        if ($params['tolower'])
            $str = strtolower($str);

        return $str;
    }

    public function toPlainLatin($str) {
        return str_replace($this->viChars, $this->enChars, $str);
    }

    private function utf8($num) {
        if($num<=0x7F)       return chr($num);
        if($num<=0x7FF)      return chr(($num>>6)+192).chr(($num&63)+128);
        if($num<=0xFFFF)     return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
        if($num<=0x1FFFFF)   return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128).chr(($num&63)+128);
        return '';
    }

    public static function getCurrentLink()
    {
        return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    /**
     * Add request parameter into url
     *
     * @param  string $url
     * @param  array $param array( 'key' => value )
     * @return string
     */
    public static function addRequestParam($url, $params)
    {
        $url = self::getCurrentLink();

        $startDelimiter = false === strpos($url, '?') ? '?' : '&';

        $arrQueryParams = [];
        foreach ($params as $key => $value) {
            if (is_numeric($key) || is_object($value)) {
                continue;
            }
            if (is_array($value)) {
                // $key[]=$value1&$key[]=$value2 ...
                $arrQueryParams[] = $key . '[]=' . implode('&' . $key . '[]=', $value);
            } elseif ($value === null) {
                $arrQueryParams[] = $key;
            } else {
                if (isset($_GET[$key])) { // exist param on url
                    $url = self::removeRequestParam($url, $key);
                }
                $arrQueryParams[] = $key . '=' . $value;
            }
        }
        if (!empty($arrQueryParams)) {
            $url .= $startDelimiter . implode('&', $arrQueryParams);
        }

        return $url;
    }

    /**
     * Remove request parameter from url
     * @param string $url
     * @param string $paramKey
     * @param bool $caseSensitive
     * @return string
     */
    public static function removeRequestParam($url, $paramKey, $caseSensitive = false)
    {
        $url = self::getCurrentLink();
        $regExpression = '/\\?[^#]*?(' . preg_quote($paramKey, '/') . '\\=[^#&]*&?)/' . ($caseSensitive ? '' : 'i');
        while (preg_match($regExpression, $url, $matches) !== 0) {
            $paramString = $matches[1];
            // if ampersand is at the end of $paramString
            if (substr($paramString, -1, 1) != '&') {
                $url = preg_replace('/(&|\\?)?' . preg_quote($paramString, '/') . '/', '', $url);
            } else {
                $url = str_replace($paramString, '', $url);
            }
        }
        return $url;
    }
}