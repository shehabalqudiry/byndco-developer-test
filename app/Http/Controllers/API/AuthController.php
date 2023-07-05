<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use function App\Helpers\returnData;
use function App\Helpers\returnError;
use function App\Helpers\returnValidationError;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            "name"          => "required|string|max:255",
            "email"         => "required|string|max:255|email|unique:users,email",
            "password"      => "required|string|min:8|max:190",
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return returnValidationError("N/A", $validator);
        }

        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
        ];

        $user = User::create($data);

        $user->api_token = $user->createToken($request->email)->plainTextToken;

        return returnData('data', $user, __('Register Done'));
    }

    public function login(Request $request)
    {
        $rules = [
            "email"         => "required|string|email",
            "password"      => "required|string",
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return returnValidationError("N/A", $validator);
        }

        $data = [
            'email'     => $request->email,
            'password'  => $request->password,
        ];

        $userLogin = auth()->attempt($data);
        if (!$userLogin) {
            return returnError('404', "User Not Register");
        }
        $user = auth()->user();

        $user->api_token = $user->createToken($request->email)->plainTextToken;

        return returnData('data', $user, __('Register Done'));
    }

}

