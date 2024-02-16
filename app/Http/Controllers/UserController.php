<?php

namespace App\Http\Controllers;

use App\Helper\JWTToken;
use App\Mail\OTPMail;
use App\Models\User;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    function LoginPage():View{
        return view('pages.auth.login-page');
    }
    function RegistrationPage():View{
        return view('pages.auth.registration-page');
    }
    function SendOtpPage():View{
        return view('pages.auth.send-otp-page');
    }
    function VerifyOTPPage():View{
        return view('pages.auth.verify-otp-page');
    }

    function ResetPasswordPage():View{
        return view('pages.auth.reset-pass-page');
    }
    public function userRegistration(Request $request)
    {

        try {
            $request->validate([
                'firstName' => 'required|string|max:50',
                'lastName' => 'required|string|max:50',
                'email' => 'required|string|email|max:50|unique:users,email',
                'mobile' => 'required|string|max:50',
                'password' => 'required|string|min:3',
            ]);

            $hashedPassword = Hash::make($request->input('password'));
            /*
            The merge method in Laravel's Request class is used to merge additional 
            input into the request's input data. It's commonly used to add or override values 
            in the request data before further processing.This line is replacing the original 'password' value 
            in the request data with the hashed password. It ensures that the hashed password 
            is used when creating the User instance in the User::create($request->input()) line.
            */
            $request->merge(['password' => $hashedPassword]);

            User::create($request->input());
            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);
        }
    }
    



    public function userLogin(Request $request)
    {

        try {
            $request->validate([
                'email' => 'required|string|email|max:50',
                'password' => 'required|string|min:3'
            ]);

            $user = User::where('email', $request->input('email'))->first();

            //dd($user);

            if (!$user || !Hash::check($request->input('password'), $user->password)) {
                return response()->json(['status' => 'failed', 'message' => 'Invalid User']);
            }

            $token = $user->createToken('authToken')->plainTextToken;
            return response()->json([
                'status' => 'success',
                'message' => 'Login Successful',
                'token' => $token
            ],200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage()
            ]);
        }
    }



    function UserLogout(){
        return redirect('/userLogin')->cookie('Log_token','',-1);
    }
    function sendOTPCode(Request $request){
        $email = $request->input('email');

        $otp = rand(1000,9999);
        $count=User::where('email','=',$email)->count();

        if($count==1){

           Mail::to($email)->send(new OTPMail($otp));

            User::where('email','=',$email)->update(['otp'=>$otp]);
            return response()->json([
                'status' => "success",
                'message'=>"Authorized",
                //'otp'=>$otp
            ],200);
        }else{
            return response()->json([
                'status' => "OTP Failed",
                'message'=>"Unauthorized"
            ],401);
        }
    }

    function verifyOTP(Request $request){
        try{
            $email = $request->input('email');
            $otp = $request->input('otp');

            $count=User::where('email','=',$email)
                        ->where('otp','=',$otp)->count();

            if($count==1){

                User::where('email','=',$email)->update(['otp'=>0]);// otp field value 0

                $token=JWTToken::verifyOTPToken($request->input('email'));
                
                return response()
                        ->json(
                            ['status'=>'success', 
                            'message'=>" OTP verify Success",
                            //'token'=>$token
                        ],200)->cookie('token',$token,60*24*30);;
            }else {
                return response()->json(['status' => 'OTP verify fail', 
                'message' => "verify OTP fail"]);
            }
        }catch(Exception $e){
            return response()->json([
                'status' => " Failed",
                'message'=>"OTP Verify fail"
            ]);
        }
    }

    function resetPassword(Request $request){
        try{
            $email = $request->header('email');
            $password = $request->input('password');

            User::where('email','=',$email)->update(['password' => Hash::make($password)]);
                
                return response()
                        ->json(
                            ['status'=>'success', 
                            'message'=>" Reset password  Success",
                        ],200);
            
        }catch(Exception $e){
            return response()->json([
                'status' => " Failed",
                'message'=>"reset pass fail"
            ]);
        }
    }
}
