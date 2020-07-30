<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helper;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AbsensiController extends Controller
{

    public function login(Request $request)
    {
        $username = $request->input("username");
        $password = $request->input("password");
        $device_uniq = $request->input("device_uniq");

        $validator = $this->validator($request, "login");
        if ($validator->fails()) return Helper::responseError(null, $validator->errors()->first());

        $user = User::where('name', $username)->where('password', $password)->first();

        if (!$user) {
            return Helper::responseError(null, "Data tidak ditemukan");
        } else if ($user->device_uniq != $device_uniq) {
            return Helper::responseError(null, "Device kamu tidak sesuai");
        } else {
            if (!$user->device_uniq) {
                $user->device_uniq = $device_uniq;
                $user->save();
            }
        }
        return Helper::responseSuccess($user, "Login sukses");
    }

    private function validator(Request $request, $method)
    {
        $rules  = [];

        if ($method == "login") {
            $rules = [
                "username"    => "required",
                "password"    => "required",
                "device_uniq" => "required"
            ];
        } else if ($method == "register") {
            $rules = [
                "username"     => "required",
                "password"     => "required",
                "nik"          => "required|numeric",
                "email"        => "required",
                "division"     => "required",
                "gender"       => "required",
                "device_uniq"  => "required",
            ];
        }

        return Validator::make($request->all(), $rules, [
            "username.required"         => "Username harus terisi",
            "password.required"         => "Password harus terisi",
            "nik.numeric"               => "Nik harus berisi angka",
            "nik.required"              => "Nik harus terisi",
            "email.required"            => "Email harus terisi",
            "division.numeric"          => "Division harus terisi",
            "gender.required"           => "Gender harus terisi",
            "device_uniq.required"      => "Device Uniq harus terisi",
        ]);
    }
}
//unique:lp_user,name