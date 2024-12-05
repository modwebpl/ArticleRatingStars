<?php
/**
 * Created by PhpStorm.
 * User: modweb
 * Date: 06/08/2019
 * Time: 11:51
 */

class FormRequest
{
    private $post;
    private $obj;
    private $arr;

    public function __construct(string $method)
    {
        switch ($method) {
            case 'get':
                $this->post = $_GET;
                break;
            case 'post':
                $this->post = $_POST;
                break;
            case 'file':
                $this->post = $_FILES;
                break;
            case 'json':
                $this->post = json_decode(file_get_contents('php://input'));
                break;
        }

        if (!$this->post) $this->post = [];

        $this->obj = new stdClass();

        foreach ($this->post as $key => $val) {
            if (!is_array($val) && !is_object($val)) {
                switch ($key) {
                    case 'email':
                    case 'tr_email':
                        $this->obj->$key = substr($this->sanitize($val, 'email'), 0, 60);
                        break;
                    case 'id':
                    case 'key':
                    case 'phone':
                    case 'tr_crc':
                        $this->obj->$key = substr($this->sanitize($val, 'int'), 0, 15);
                        break;
                    case 'quantity':
                        $this->obj->$key = substr($this->sanitize($val, 'int'), 0, 4);
                        break;
                    case 'npwz':
                        $npwz = substr($this->sanitize($val, 'int'), 0, 7);
                        $this->obj->$key = $this->is_npwz($npwz) ? $npwz : '';
                        break;
                    case 'set_pass':
                    case 'pass':
                    case 'pass2':
                        $this->obj->$key = $this->sanitize($val, 'pass');
                        break;
                    case 'nip':
                        $nip = substr($this->sanitize($val, 'int'), 0, 10);
                        $this->obj->$key = $this->is_nip($nip) ? $nip : '';
                        break;
                    case 'regon':
                        $regon = substr($this->sanitize($val, 'int'), 0, 9);
                        $this->obj->$key = $this->is_regon($regon) ? $regon : '';
                        break;
                    case 'desc':
                    case 'desc2':
                        $this->obj->$key = substr($this->sanitize($val), 0, 3000);
                        break;
                    case 'url':
                        $this->obj->$key = substr($this->sanitize($val, 'url'), 0, 100);
                        break;
                    case 'file':
                    case 'cart':
                    case 'data':
                    case 'response':
                        $this->obj->$key = $this->sanitize($val);
                        break;
                    default:
                        $this->obj->$key = substr($this->sanitize($val, 'utf8'), 0, 500);
                }
            } else {
                $this->obj->$key = $val;
            }
        }
    }

    public function modify_date($date, $when = '+1 day', string $format = 'U')
    {
        if (!$when || !$date) return;
        return intval(date_create($this->get_time($date))->modify($when)->format($format));
    }

    public function get_time(string $val, $format = 'Y-m-d H:i:s')
    {
        return date($format, $val);
    }

    public function sanitize(string $val = null, string $name = 'var')
    {
        if (is_null($val)) return false;

        $str = preg_replace('/\x00|<[^>]*>?/', '', $val);
        $val = str_replace(["'", '"'], ['&#39;', '&#34;'], $str);

        switch ($name) {
            case 'email':
                return filter_var(strtolower($val), FILTER_VALIDATE_EMAIL);
            case 'url':
                return filter_var($val, FILTER_SANITIZE_URL, FILTER_FLAG_PATH_REQUIRED);
            case 'pass':
                return urldecode($val);
            case 'utf-8':
            case 'utf8':
                return mb_convert_encoding($val, 'UTF-8');
            default:
                return filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        // php 8
        /*return match ($name) {
            'email' => filter_var(strtolower($val), FILTER_VALIDATE_EMAIL),
            'url' => filter_var($val, FILTER$this->sanitize_URL, FILTER_FLAG_PATH_REQUIRED),
            'pass' => urldecode($val),
            default => filter_var($val, FILTER$this->sanitize_STRING),
        };*/
    }

    public function is_nip($str)
    {
        $str = preg_replace('/[^0-9]+/', '', $str);

        if (strlen($str) !== 10) return false;

        $arrSteps = [6, 5, 7, 2, 3, 4, 5, 6, 7];
        $intSum = 0;

        for ($i = 0; $i < 9; $i++) $intSum += $arrSteps[$i] * $str[$i];

        $int = $intSum % 11;
        $intControlNr = $int === 10 ? 0 : $int;

        return $intControlNr == $str[9] ? true : false;
    }

    public function is_regon($str)
    {
        if (strlen($str) != 9) return false;

        $arrSteps = array(8, 9, 2, 3, 4, 5, 6, 7);
        $intSum = 0;
        for ($i = 0; $i < 8; $i++) $intSum += $arrSteps[$i] * $str[$i];

        $int = $intSum % 11;
        $intControlNr = ($int == 10) ? 0 : $int;

        return $intControlNr == $str[8] ? true : false;
    }

    public function is_npwz($str)
    {
        $control_num = $str[0];

        $control_count_num = 0;
        for ($i = 1; $i <= 6; $i++) $control_count_num += $str[$i] * $i;
        $final_control_num = $control_count_num % 11;

        return $final_control_num != $control_num ? false : true;
    }

    public function get_all()
    {
        return (array)$this->arr = $this->obj;
    }

    public function get($key)
    {
        return $this->obj->$key;
    }

    public function searcharray($value, $key, $array)
    {
        foreach ($array as $k => $val) {
            if ($val[$key] == $value) {
                return $k;
            }
        }
        return null;
    }

    public function set($key, $val)
    {
        return $this->obj->$key = $val;
    }

    function clean(bool $token = false)
    {
        $_SESSION = [];
        return $this->refresh($token);
    }

    function set_csrf(int $length = 10)
    {
        return $_SESSION['csrf'] = bin2hex(openssl_random_pseudo_bytes($length));
    }

    function get_csrf()
    {
        return $_SESSION['csrf'];
    }

    function refresh(bool $token = false)
    {
        if ($token) $this->set_csrf();
        return session_regenerate_id(true);
    }

    function terminate(string $msg = 'Fatal error', string $url = '')
    {
        $this->clean();
        die(json_encode(['status' => false, 'messages' => $msg, 'msg' => $msg, 'url' => $url]));
    }

    function validate(string $key, $val = '', array $fillable, array $nullable, string $url = '', string $msg = 'Error: Invalid data')
    {
        if (!in_array($key, $fillable) || empty($val) && !in_array($key, $nullable)) $this->terminate($msg = 'Error: invalid obj: ' . $key, $url);
    }

}
