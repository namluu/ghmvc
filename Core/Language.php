<?php
namespace Core;
/**
 * Error and exception handler
 *
 * PHP version 7.0
 */
class Language
{
    protected static $data;

    public function getAvailableLanguages()
    {
        $langs = \App\Config::getConfig('available_languages');
        if ($langs) {
            return explode(',', $langs);
        }
        return [];
    }

    public function getDefaultLanguage()
    {
        return \App\Config::getConfig('default_language');
    }

    public static function load($code) {
        $langPath = ROOT.DS.'App'.DS.'Lang'.DS.strtolower($code).'.php';
        if (file_exists($langPath)) {
            self::$data = include ($langPath);
        } else {
            throw new \Exception('Language file not found. '.$langPath);
        }
    }

    public static function get($key, $default = '') {
        return isset(self::$data[$key]) ? self::$data[$key] : $default;
    }
}