<?php


namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

function returnError($errNum, $msg, $status = false)
{
    return response()->json([
        'status' => $status,
        'errNum' => $errNum,
        'msg' => $msg
    ]);
}


function returnSuccessMessage($msg = "", $errNum = "S000", $status = true)
{
    return [
        'status' => $status,
        'errNum' => $errNum,
        'msg' => $msg
    ];
}

function returnData($key, $value, $msg = "", $status = true)
{
    return response()->json([
        'status' => $status,
        'errNum' => "S000",
        'msg' => $msg,
        $key => $value
    ]);
}

function returnValidationError($code = "N/A", $validator = null)
{
    return returnError($code, $validator->errors()->first());
}


function uploadFile($folder, $file, $prefix = "")
{
    $filename = "test_" . $prefix . date('d_m_Y_h_i_s') . '.' . $file->getClientOriginalExtension();
    $path2 = public_path("uploads/".$folder);
    $file->move($path2, $filename);
    $path = 'uploads/' . $folder . '/' . $filename;
    return $path;
}

function deleteFile($table, $id, $column)
{
    $img = DB::table($table)->where('id', $id)->first();
    File::delete($img->$column);
    return ;
}
