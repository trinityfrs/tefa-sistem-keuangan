<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Irsyadulibad\NIKValidator\Validator;

class NIKController extends Controller
{
    /**
     * Validate the provided NIK.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validateNik(Request $request)
    {
        // Validasi input NIK
        $request->validate([
            'nik' => 'required|string|size:16',
        ]);

        // Ambil NIK dari request
        $nik = $request->input('nik');

        // Validasi NIK
        $validator = new Validator($nik);
        $result = $validator->parse(); 

        if ($result->valid) {
            return response()->json([
                'message' => 'NIK valid',
                'valid' => true,
            ]);
        } else {
            return response()->json([
                'message' => 'NIK tidak valid',
                'valid' => false
            ], 400);
        }
    }
}
