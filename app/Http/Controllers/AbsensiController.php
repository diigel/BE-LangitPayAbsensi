<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helper;
use Illuminate\Support\Facades\Validator;

class AbsensiController extends Controller
{


    public function login(Request $request)
    {

        $username = $request->input("username");
        $password = $request->input("password");

        $validator = $this->validator($request, "login");
        if ($validator->fails()) return Helper::responseError(null, $validator->errors()->first());
    }

    private function validator(Request $request, $method)
    {
        $rules  = [];

        if ($method == "login") {
            $rules = [
                "username" => "required",
                "password" => "required"
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
