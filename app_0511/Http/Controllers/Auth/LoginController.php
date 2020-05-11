<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
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
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
	 
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
	
	
	public function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            array_merge($this->credentials($request),['locked'=>0]), $request->filled('remember')
        );
    }
	
	public function authenticated(Request $request, $user){    
		return ($user->seller_rules || $user->sap_seller_id)?redirect('home'):redirect('service');
	}
	
	public function redirectTo(){
		if(array_get($_REQUEST,'redirect_url')){
			return array_get($_REQUEST,'redirect_url');
		}else{
			return '/home';
		}
		
	}
}
