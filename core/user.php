<?php

if (!defined('PATH')) exit('Direct access to script is not allowed.');

class User extends Singleton
{

    /**
     * If credentials are incorrect, method will stop executing further PHP script and prompt alert box
     * with username and password.
     * @param $callback - function that will be called to notify about failed login
     */
    public function requireAuthentication($callback) {
        if (!$this->isAuthenticated()) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="' . Config::REALM .
                '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5(Config::REALM) . '"');
            $callback();
            exit();
        }
    }

    public function isAuthenticated()
    {
        return !empty($_SERVER['PHP_AUTH_DIGEST'])
            && ($data = $this->parseDigest($_SERVER['PHP_AUTH_DIGEST']))
            && $data['username'] == Config::USERNAME
            && $data['response'] == $this->encrypt($data);
    }

    private function parseDigest($txt)
    {
        $neededParts = array(
            'nonce' => 1,
            'nc' => 1,
            'cnonce' => 1,
            'qop' => 1,
            'username' => 1,
            'uri' => 1,
            'response' => 1);
        $data = array();

        preg_match_all('@(\w+)=([\'"]?)([?&a-zA-Z0-9=./\_-]+)([\'"]?)\2@',
            $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3];
            unset($neededParts[$m[1]]);
        }

        return $neededParts ? false : $data;
    }

    private function encrypt($data)
    {
        $e1 = md5($data['username'] . ':' . Config::REALM . ':' . Config::PASSWORD);
        $e2 = $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'];
        $e3 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
        return md5($e1 . ':' . $e2 . ':' . $e3);
    }
}