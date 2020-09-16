<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helper;
use App\Models\Absensi;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class AbsensiController extends Controller
{

    public function login(Request $request)
    {
        $username = $request->input("username");
        $password = $request->input("password");
        $device_uniq = $request->input("device_uniq");
        $token = $request->input("token");

        $validator = $this->validator($request, "login");
        if ($validator->fails()) return Helper::responseError(null, $validator->errors()->first());

        $user = User::where('name', $username)->where('password', $password)->first();

        date_default_timezone_set("Asia/Jakarta");

        if (!$user) {
            return Helper::responseError(null, "Data tidak ditemukan");
        } else if ($user->device_uniq != null && $user->device_uniq != $device_uniq) {
            return Helper::responseError(null, "Device kamu tidak sesuai");
        } else {
            if (!$user->device_uniq) {
                $user->device_uniq = $device_uniq;
                $user->token = $token;
                $user->save();
            } else {
                $user->updated_at = date("Y-m-d H:i:s");
                $user->token = $token;
                $user->save();
            }
        }

        return Helper::responseSuccess($user, "Login sukses");
    }

    public function getUser(Request $request)
    {
        $device_uniq = $request->input("device_uniq");

        $user = User::where("device_uniq", $device_uniq)->first();
        if (!$user) return Helper::responseError(null, "User tidak ditemukan");

        return Helper::responseSuccess($user, "Berhasil menampilkan user");
    }

    public function updateToken(Request $request)
    {
        $token = $request->input("token");
        $device_uniq = $request->input("device_uniq");

        $user = User::where("device_uniq", $device_uniq)->first();
        if ($user->token == $token) return Helper::responseError(null, "Token masih sama");

        return Helper::responseSuccess($user, "Berhasil Update token");
    }

    public function getNotification()
    {
        $notif = Notification::get();
        if (count($notif) <= 0) return Helper::responseError(null, "Belum ada Pemberitahuan");

        return Helper::responseSuccess($notif, "Berhasil menampilkan pemberitahuan");
    }

    public function requestAbsen(Request $request)
    {
        $user_id = $request->input("user_id");
        $name = $request->input("name");
        $type_absensi = $request->input("type_absensi");
        $image = $request->file("image");
        $address = $request->input("address");
        $latitude = $request->input("latitude");
        $longitude = $request->input("longitude");
        $division = $request->input("division");
        $noted = $request->input("noted");

        $validator = $this->validator($request, "requestAbsen");
        if ($validator->fails()) return Helper::responseError(null, $validator->errors()->first());

        $user = User::find($user_id);
        if (!$user) return Helper::responseError(null, "User Tidak di temukan");

        Image::make($image)->resize(100, 100);
        $image->move(storage_path("Image"), $image->getClientOriginalName());

        date_default_timezone_set("Asia/Jakarta");
        $absensi                = new Absensi();
        $absensi->name          = $name;
        $absensi->type_absensi  = $type_absensi;
        $absensi->division      = $division;
        $absensi->image         = $image->getClientOriginalName();
        $absensi->verification  = $type_absensi == "2" ? "0" : "1";
        $absensi->latitude      = $latitude;
        $absensi->longitude     = $longitude;
        $absensi->address       = $address;
        $absensi->noted         = $noted;
        $absensi->save();

        if ($type_absensi == "2") {
            Helper::sendFcm(
                $user,
                "Absen Luar Kantor",
                "Silahkan tunggu persetujuan hrd untuk verifikasi absen anda",
                $absensi->type_absensi,
                $absensi->id
            );
        } else {
            Helper::sendFcm(
                $user,
                "Absen Kantor",
                "Absen Berhasil",
                $absensi->type_absensi,
                $absensi->id
            );
        }


        return Helper::responseSuccess($absensi, "Berhasil Absen");
    }

    private function validator(Request $request, $method)
    {
        $rules  = [];

        if ($method == "login") {
            $rules = [
                "username"    => "required",
                "password"    => "required",
                "device_uniq" => "required",
                "token"       => "required"
            ];
        } else if ($method == "requestAbsen") {
            $rules = [
                "latitude"     => "required",
                "longitude"    => "required",
                "division"     => "required",
                "name"         => "required",
                "type_absensi" => "required",
                "user_id"      => "required|numeric",
                "image"        => "required"
            ];
        }

        return Validator::make($request->all(), $rules, [
            "username.required"         => "Username harus terisi",
            "password.required"         => "Password harus terisi",
            "nik.numeric"               => "Nik harus berisi angka",
            "nik.required"              => "Nik harus terisi",
            "email.required"            => "Email harus terisi",
            "division.required"         => "Division harus terisi",
            "gender.required"           => "Gender harus terisi",
            "device_uniq.required"      => "Device Uniq harus terisi",
            "user_id.required"          => "User Id harus terisi",
            "user_id.numeric"           => "User Id harus berisi angka",
            "token.required"            => "Token Harus Terisi"
        ]);
    }
}
