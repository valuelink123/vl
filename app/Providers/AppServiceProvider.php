<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Inbox;
use Illuminate\Support\Facades\Auth;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
		view()->composer('layouts/layout', function ($view) {
			$user_id = intval(Auth::user()->id);
			$unreply=[];
			if($user_id){
				$unreply = Inbox::selectRaw('count(*) as count,type')->where('user_id',$user_id)->where('reply',0)->groupBy('type')->pluck('count','type');
			}

			$view->with('unreply',$unreply);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
