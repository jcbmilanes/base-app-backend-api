<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller as Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::query()->orderBy('id','ASC')->paginate(5);

        return UserResource::collection($users)->additional(['meta' => ['status' => 200, 'success' => true, 'message' => 'Employees loaded']]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), 
            [
                "name" => "required",
                "email" => "required|email",
                "password" => "required",
            ]
        );

        //Validate inputs
        if($validator->fails()) 
        {
            return response()->json(['meta' => ["status" => "failed", "message" => "validation_error", "errors" => $validator->errors()]]);
        }

        //Parse name
        $name = $request->name;
        $name = explode(" ", $name);
        $first_name  = $name[0];
        $last_name  = "";

        if(isset($name[1])) 
        {
            $last_name = $name[1];
        }

        $userDataArray = array(
            "first_name" => $first_name,
            "last_name" => $last_name,
            "full_name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password),
            "phone" => $request->phone
        );

        $user_status = User::where("email", $request->email)->first();

        //Check if email was already registered
        if(!is_null($user_status)) 
        {
           return response()->json(['meta' => ["status" => "failed", "success" => false, "message" => "Whoops! email already registered"]]);
        }

        $user = User::create($userDataArray);

        $token = $user->createToken('BaseApp')->accessToken;

        if(!is_null($user)) 
        {
            return response()->json(["token" => $token,"data" => $user,'meta' => ["status" => 200, "success" => true, "message" => "Registration completed successfully"]]);
        }
        else 
        {
            return response()->json(['meta' => ["status" => "failed", "success" => false, "message" => "failed to register"]]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);

        return (new UserResource($user))->additional(['meta' => ['success' => true,'message' => "User found"]]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), 
            [
                "name" => "required",
                "email" => "required|email",
                "password" => "required",
            ]
        );

        //Validate inputs
        if($validator->fails()) 
        {
            return response()->json(['meta' => ["status" => "failed", "message" => "validation_error", "errors" => $validator->errors()]]);
        }

        //Parse name
        $name = $request->name;
        $name = explode(" ", $name);
        $first_name  = $name[0];
        $last_name  = "";

        if(isset($name[1])) 
        {
            $last_name = $name[1];
        }

        $userDataArray = array(
            "id"=>$id,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "full_name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password),
            "phone" => $request->phone
        );

        $user = User::find($id);

        if(!$user)
        {
            return response()->json(['meta' => ['status' => 400, 'success' => false, 'message' => 'User could not be found!']]);
        }

        $updated = $user->update($userDataArray);

        if($updated)
        {            
            return (new UserResource($user))->additional(['meta' => ['status' => 200, 'success' => true, 'message' => 'User has been updated!']]);              
        }else
        {
            return response()->json(['meta' => ['status' => 500, 'success' => false, 'message' => 'User could not be updated!']]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        $user = User::find($id);

        if(!$user)
        {
            return response()->json(['meta' => ['status' => 400, 'success' => false, 'message' => 'User could not be found!']]);
        }

        if($user->delete())
        {
            return response()->json(['meta' => ['status' => 200, 'success' => true, 'message' => 'User has been deleted!']]);
        }else
        {
            return response()->json(['meta' => ['status' => 500, 'success' => false, 'message' => 'User could not be deleted!']]);
        }
    }
}
