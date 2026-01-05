<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class GoogleOneTapController extends Controller
{
    public function callback(Request $request)
    {
        $credential = $request->input('credential');

        if(!$credential){
            return response()->json(['success'=>false, 'message'=>'No credential']);
        }

        // Minimal JWT decode (base64 payload)
        $payload = explode('.', $credential);
        if(count($payload) < 2){
            return response()->json(['success'=>false, 'message'=>'Invalid credential']);
        }

        $decoded = json_decode(base64_decode($payload[1]), true);

        if(!$decoded || !isset($decoded['email'])){
            return response()->json(['success'=>false, 'message'=>'Invalid payload']);
        }

        $email = $decoded['email'];
        $name = $decoded['name'] ?? 'Google User';

        // Check if user exists
        $user = User::where('email', $email)->first();

        if(!$user){
            // Create user automatically
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt(uniqid()),
                'email_verified_at' => now(),
            ]);
        }

        // Login user
        Auth::login($user);

        return response()->json(['success'=>true]);
    }
}
