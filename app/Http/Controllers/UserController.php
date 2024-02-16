<?php

namespace App\Http\Controllers;

use App\Helper\JWTToken;
use App\Mail\OTPMail;
use App\Models\User;
use Exception;
use Illuminate\Contracts\View\View;
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
    function userRegistration(Request $request){
       try{
        // User::create([
        //     'firstName'=>$request->input('firstName'),
        //     'lastName'=>$request->input('lastName'),
        //     'email'=>$request->input('email'),
        //     'mobile'=>$request->input('mobile'),
        //     'password'=>$request->input('password'),

        // ]);
        User::create($request->input());//for data input 'firstName'=>$request->input('firstName')
        return response()->json([
            'status'=>'Success',
            'message'=>'User Registration successfully',
        ],201);
       }
       catch(Exception $e){
        return response()->json([
            'status'=>"Failed",
            //'message'=>$e->getMessage(),
            'message'=>"Failed Register",

            ]);
       }
    }

    function userLogin(Request $request){
        try{
            $user= User::where($request->input())->select('email','id')->first();
            //return response()->json(['status'=>'success', 'message'=>$user]);

            $userEmail=$user->email;
            //return response()->json(['status'=>'success', 'message'=>$userID]);

            if($userEmail>0){
                $token=JWTToken::CreateToken($request->input('email'),$user->id);
                return response()
                        ->json(
                            ['status'=>'success', 
                            'message'=>"Login Success",
                            'token'=>$token
                        ],200)
                        ->cookie('Log_token',$token,time()+60*60);
            }else {
                return response()->json(['status' => 'fail', 
                'message' => "No user found"]);
            }
        }catch(Exception $e){
            return response()->json([
                'status' => "Login Failed",
                'message'=>"Unauthorized"
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

            User::where('email','=',$email)->update(['password'=>$password]);
                
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
