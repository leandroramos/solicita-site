<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use App\User;
use Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirectToProvider()
    {
        return Socialite::driver('senhaunica')
            ->redirect();
    }

    public function handleProviderCallback()
    {

        $userSenhaUnica = Socialite::driver('senhaunica')->user();
        # busca o usuário local
        $user = User::find($userSenhaUnica->codpes);

        # restrição só para admins
        $admins = explode(',', trim(env('SENHAUNICA_ADMINS')));
        if (!in_array($userSenhaUnica->codpes, $admins)) {
            session()->flash('alert-danger', 'Usuario sem permissao de acesso!');
            return redirect('/');
        }

        if (is_null($user)) {
            $user = new User;
            $user->id = $userSenhaUnica->codpes;
            $user->name = $userSenhaUnica->nompes;
            $user->email = $userSenhaUnica->email;
            $user->save();
        } else {
            # se o usuário EXISTE local
            # atualiza os dados
            $user->id = $userSenhaUnica->codpes;
            $user->name = $userSenhaUnica->nompes;
            $user->email = $userSenhaUnica->email;
            $user->save();
        }
        Auth::login($user, true);
        return redirect('/sites');
    }

    public function logout(Request $request) {
      Auth::logout();
      return redirect('/');
    }
}
