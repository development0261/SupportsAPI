<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
Use DB;
Use JWTAuth;

class AuthController extends Controller
{
	public function register(Request $request) {
		DB::beginTransaction();
		try{
	        $validator = Validator::make($request->all(), [
	            'name' => 'required',
	            'email' => 'required|email|unique:users',
	            'password' => 'required|confirmed|min:6',
	            'user_type' => 'required|in:Customer,Support',
	        ]);

	        if($validator->fails()){
	        	$response['status'] = 'error';
	        	$response['response'] = $validator->errors(); 
	            return response()->json($response, 401);
	        }
	 
	 		$user = User::create([
	 			'name' => $request->name,
	 			'email' => $request->email,
	 			'password' => bcrypt($request->password),
	 			'user_type' => $request->user_type,
	 		]);

	 		DB::commit();
	 		$response['status'] = 'success';
        	$response['response'] = 'User successfully registered';
	        return response()->json($response, 201);
	    }catch(\Exception $e){
	    	DB::rollback();
	    	$response['status'] = 'error';
        	$response['response'] = $e->getMessage();
            return response()->json($response, 401);
	    }
    }

    public function login(Request $request){
    	try{
	    	$validator = Validator::make($request->all(), [
	            'email' => 'required|email|exists:users',
	            'password' => 'required',
	        ]);

	        if ($validator->fails()) {
	            $response['status'] = 'error';
	        	$response['response'] = $validator->errors(); 
	            return response()->json($response, 401);
	        }

	        if (! $token = JWTAuth::attempt($validator->validated())) {
	            $response['status'] = 'error';
	        	$response['response']['invalid'][0] = 'Invalid username or password'; 
	            return response()->json($response, 401);
	        }

	        $response['status'] = 'success';
	        $response['response']['user_data'] = auth()->user();
	        $response['response']['access_token'] = $token; 
	        return response()->json($response, 201);
	    }catch(\Exception $e){
	    	$response['status'] = 'error';
        	$response['response'] = $e->getMessage();
            return response()->json($response, 401);
	    }
    }

	// public function getData(){	
 //  		$error = ["status" => "failed", "response" => "Access not granted from browser"];
 //  		return response()->json($error, 401);
 //    }
}
