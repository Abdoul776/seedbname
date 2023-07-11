<?php

namespace App\Http\Controllers;

use App\Http\Requests\validateRegister;
use App\Models\User;

use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Notifications\ConfirmationNotification;


class AuthController extends Controller
{
    public function login_view(){
        return view('auth.pages.login');
    }
    
    public function register_view(){
        return view('auth.pages.register');
    }
    public function login(Request $request){
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::attempt(request()->only(['email', 'password']))) {
            $request->session()->regenerate();
            return redirect('/dashboard');
        }
        return back()->withErrors([
            'email' => 'Mot de passe ou email incorrect.',
        ])->onlyInput('email');
    }
    public function Inscription(validateRegister $request,User $inserte)
    { 
      $piece=$request->file('piece')->store('public/users/users_validation_pieces');
      $emails = $request->email;
      $code_bic=random_int(100,999);
      $code_guichet=random_int(100,999);
      $code_banque=random_int(100,999);
      $numero_compte=date('U');
      $token=Str::random(16);
      $user= User::create([
          'nom' => $request->nom,
          'prenom' => $request->prenom,
          'email'=> $request->email,
          'telephone'=> $request->telephone,
          'civilite' => $request->civilite,
          'pays' => $request->pays,
          'code_bic' => $code_bic,
          'code_guichet' => $code_guichet,
          'code_banque' => $code_banque,
          'numero_compte' => $numero_compte,
          'codePostal'=>$request->codePostal,
          'ville' => $request->ville,
          'adresse' => $request->adresse,
          'devise' => $request->devise,
          'piece' => $piece,
          'confirmation_token'=>$token,
          'password' =>  Hash::make($request->password)
      ]);

$user->notify(new ConfirmationNotification($token) );
     
      return view('auth.pages.Envoi_mail',[
        'email'=> $emails
      ]);
      
    }

    public function change_profile_avatar(Request $request){
        $credentials = $request->validate([
            'photo_profil' => ['required','file'],

        ]);
      $photo_profil=$request->file('photo_profil')->store('public/users/profil');

        $user_auth=auth()->user();
        $user=User::find($user_auth->id);
        $user->photo_profil=$photo_profil;
        $user->save();
      return back()->with(['success'=>'Profil modifié avec success']);

    }

    public function change_profile(Request $request)
    { 
        $credentials = $request->validate([
            'nom' => ['required'],
            'prenom' => ['required'],
            'telephone' => ['required'],
            'devise' => ['required'],
            'ville' => ['required'],
            'adresse' => ['required'],
            'pays' => ['required'],

        ]);
      $user_auth=auth()->user();
      $user=User::find($user_auth->id);
      $user->update([
          'nom' => $request->nom,
          'prenom' => $request->prenom,
          'telephone'=> $request->telephone,
          'pays' => $request->pays,
          'codePostal'=>$request->codePostal,
          'ville' => $request->ville,
          'adresse' => $request->adresse,
          'devise' => $request->devise
      ]);

      return back()->with(['success'=>'Profil modifié avec success']);

      
    }
    public function confirmation($token){

    }
    public function Envoi_mail()
    {
        return view('auth.pages.Envoi_mail');
    }
    public function forget_password()
    
    {
        return view('auth.pages.password_forget');
    }



    public function password_change(Request $request)
    {
        $credentials = $request->validate([
            'password' => ['required'],
            'password2' => ['required','same:password'],
            'token' => ['required','exists:password_reset_tokens,token']
        ]);
        $tokenData = DB::table('password_reset_tokens')->where('token', $request->token)->first();
       

        $user = User::where('email', $tokenData->email)->firstOrFail();

        $user->password=Hash::make($request->input('password'));
        $user->save();
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return view('auth.change_password_success');

    }
}
