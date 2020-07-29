<?php

namespace App\Http;

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
}
