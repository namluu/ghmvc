<?php
namespace Core;

class Session
{
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return null;
    }

    public static function delete($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy()
    {
        session_destroy();
    }

    public static function setMessage($type, $message)
    {
        self::set('message', [$type => $message]);
    }

    public static function getMessage()
    {
        $messages = self::get('message');
        Session::delete('message');
        return $messages;
    }

    /**
     * Set Form data to session
     *
     * @param string $key
     * @param array $dataCache
     */
    public function setFormData($key, $dataCache)
    {
        $this->set($key, $dataCache);
    }

    /**
     * Get Form data from session and assign to model object
     *
     * @param string $key
     * @param object $model
     * @return object mixed
     */
    public function getFormData($key, $model)
    {
        $postData = $this->get($key);
        if ($postData) {
            foreach ($postData as $k => $value) {
                $model->$k = $value;
            }
            $this->delete($key);
        }
        return $model;
    }
}