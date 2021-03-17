<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;

use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    public function userLogin(Request $request) 
    {
        $validator = Validator::make($request->all(),
            [
                "email" => "required|email",
                "password" => "required"
            ]
        );

        //Validate inputs
        if($validator->fails()) 
        {
            return response()->json(["status" => "failed", "validation_error" => $validator->errors()]);
        }

        //Check if email exist in the database 
        $email_status = User::where("email", $request->email)->first();


        //Check the password if email exist
        if(!is_null($email_status)) 
        {
            $data = [
                'email' => $request->email,
                'password' => $request->password
            ];
            
            if(auth()->attempt($data)) 
            {
                $user = $this->userDetail($request->email);
                $token = auth()->user()->createToken('BaseApp')->accessToken;

                return response()->json(["token" => $token,"data" => $user,'meta' => ["status" => 200, "success" => true, "message" => "You have logged in successfully"]]);

                /*return response()->json(["token" => $token,"data" => $user,'meta' => ["status" => 200, "success" => true, "message" => "You have logged in successfully"]])
                        ->withHeaders(['Access-Control-Allow-Origin' => '*',
                                       'Access-Control-Allow-Headers'=> "{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
                        ]);*/

            }
            else 
            {
                return response()->json(['meta' => ["status" => "failed", "success" => false, "message" => "Unable to login. Incorrect password."]]);
            }
        }
        else 
        {
            return response()->json(['meta' => ["status" => "failed", "success" => false, "message" => "Unable to login. Email doesn't exist."]]);
        }
    }
    
    protected function userDetail($email) 
    {
        $user = array();

        if($email != "") 
        {
            $user = User::where("email", $email)->first();

            return $user;
        }
    }    

    public function hasRole($id, $slug)
    {
        $user = User::find($id);
        $hasRole = $user->hasRole($slug);

        if(!$user)
        {
            return response()->json(['meta' => ['status' => 400, 'success' => false, 'message' => 'User could not be found!']]);
        }else
        {
            return response()->json(['hasRole'=>$hasRole, 'slug'=>$slug,'meta' => ['status' => 200, 'success' => true, 'message' => 'Done checking user role']]);
        }
    }

    public function givePermissionsTo($id, $slug)
    {
        $user = User::find($id);
        $givePermissionsTo = $user->givePermissionsTo($slug);

        if(!$user)
        {
            return response()->json(['meta' => ['status' => 400, 'success' => false, 'message' => 'User could not be found!']]);
        }else
        {
            return response()->json(['slug'=>$slug,'meta' => ['status' => 200, 'success' => true, 'message' => 'Done giving user permission']]);
        }
    }

    public function hasPermission($id, $slug)
    {
        $user = User::find($id);
        $hasPermission = $user->hasPermission($slug);

        if(!$user)
        {
            return response()->json(['meta' => ['status' => 400, 'success' => false, 'message' => 'User could not be found!']]);
        }else
        {
            return response()->json(['hasPermission'=>$hasPermission, 'slug'=>$slug,'meta' => ['status' => 200, 'success' => true, 'message' => 'Done checking user permission']]);
        }
    }

    public function deletePermissions()
    {
        
    }

}
