<?php

namespace App\Http;

use Exception;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

if (!defined("SESSIONID")) define("SESSIONID", uniqid());

class Helper
{
    public static function responseSuccess($data = null, $mess = "Success", $code = 200)
    {
        if (isset($data["data"]) && is_array($data) && count($data["data"]) < 1) $data["data"] = null;

        $log = $res = [
            "status"    => true,
            "code"      => $code,
            "message"   => $mess,
            "data"      => $data
        ];

        if (is_array($log["data"]) && ($dataRow = count($log["data"])) > 100) $log["data"] = "Found " . $dataRow . " rows";
        if (isset($log["data"]["data"]) && is_array($log["data"]["data"]) && ($dataDataRow = count($log["data"]["data"])) > 100) $log["data"]["data"] = "Found " . $dataDataRow . " rows";

        $log = json_encode($log);
        if (strlen($log) > 1500) $log = substr($log, 0, 1500);
        Log::info(SESSIONID . "\tRESPONSE\t\t\t" . $log);

        return new Response($res, $code);
    }

    public static function responseError($data = null, $mess = "Internal System Error", $code = 200)
    {
        if (isset($data["data"]) && count($data["data"]) < 1) $data["data"] = null;

        $log = $res = [
            "status"    => false,
            "code"      => $code,
            "message"   => $mess,
            "data"      => $data
        ];

        if (is_array($log["data"]) && ($dataRow = count($log["data"])) > 100) $log["data"] = "Found " . $dataRow . " rows";
        if (isset($log["data"]["data"]) && is_array($log["data"]["data"]) && ($dataDataRow = count($log["data"]["data"])) > 100) $log["data"]["data"] = "Found " . $dataDataRow . " rows";

        $log = json_encode($log);
        if (strlen($log) > 1500) $log = substr($log, 0, 1500);
        Log::info(SESSIONID . "\tRESPONSE\t\t\t" . $log);

        return new Response($res, $code);
    }

    public static function sendFcm(User $user, $title, $message, $status, $type)
    {
        $fcmUrl     = env("FCM_URL");
        $server_key = env("SERVER_KEY");

        $headers = array("Authorization:key=" . $server_key, "Content-Type:application/json");

        $user = User::where("id", $user->id)->first();
        if (!$user || !@$user->token) {
            Log::info(" fcm token tidak ditemukan");
            return;
        }

        $fields = array(
            "to"    => $user->token,
            "data"  => array(
                "title"                 => $title,
                "message"               => $message,
                "status"                => $status,
                "type"                  => $type
            )
        );

        $payload = json_encode($fields);
        $res = self::doCurl($fcmUrl, $payload, $headers);

        date_default_timezone_set("Asia/Jakarta");

        $notif = new Notification();
        $notif->user_id             = $user->id;
        $notif->title               = $title;
        $notif->message             = $message;
        $notif->status              = $status;
        $notif->type                = $type;
        $notif->save();

        return $notif;
    }

    public static function doCurl($url, $POSTDATA, $header = array(), $method = "POST")
    {
        try {
            $ch  = curl_init();

            $data = $POSTDATA;
            if (is_array($POSTDATA)) {
                $data = http_build_query($POSTDATA);
            }
            Log::info(SESSIONID . "\tCURL REQUEST \tHeader:: " . json_encode($header) . "\t Method:: " . $method . "\t\t" . $data . "\t\tURL::" . $url);

            if ($method == "GET") $url .= "?" . $data;

            curl_setopt($ch, CURLOPT_URL,               $url);
            curl_setopt($ch, CURLOPT_HEADER,            TRUE);
            curl_setopt($ch, CURLOPT_HTTPHEADER,        $header);
            curl_setopt($ch, CURLINFO_HEADER_OUT,       true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,    TRUE);
            if ($method == "POST") {
                curl_setopt($ch, CURLOPT_POST,          TRUE);
            }
            if (!in_array($method, ["GET", "POST"])) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            }
            if ($method != "GET") {
                curl_setopt($ch, CURLOPT_POSTFIELDS,    $data);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,    2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,    false);
            curl_setopt($ch, CURLOPT_USERAGENT,         "LangitPay/1.0");
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,    30);
            curl_setopt($ch, CURLOPT_TIMEOUT,           30);
            curl_setopt($ch, CURLOPT_VERBOSE,           true);

            $rawResponse    = curl_exec($ch);
            $header_size    = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $httpcode       = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $header         = substr($rawResponse, 0, $header_size);
            $heads          = explode("\n", $header);
            $head           = FALSE;
            foreach ($heads as $v) {
                $v = trim($v);
                if (strlen($v) < 1) continue;
                if (preg_match("/^(HTTP\/\d\.\d) (\d{3}) (.*)/i", $v, $m)) {
                    $head["http"]           = $m[1];
                    $head["status_code"]    = $m[2];
                    $head["status_string"]  = $m[2] . " - " . $m[3];
                } elseif (preg_match("/^([\w\-\.]+):\s*?(\S.*)$/", $v, $m)) {
                    $head[$m[1]] = $m[2];
                } else continue;
            }
            $response["cURLerror"] = curl_error($ch);
            curl_close($ch);
            $response["header"] = $head;

            $body   = substr($rawResponse, $header_size);
            $body   = trim($body);
            if (!!($jsonRes = json_decode($body, true)))
                $response["body"] = json_decode($body, true);
            else
                $response["body"] = $body;
        } catch (Exception $e) {
            $response["error"] = $e;
        }
        Log::info(SESSIONID . "\tCURL RESPONSE\t\t\t" . json_encode($response));
        return $response["body"];
    }
}
