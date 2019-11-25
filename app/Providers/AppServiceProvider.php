<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Inbox;
use App\Task;
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
				$untasks = Task::selectRaw('count(*) as count')->where('response_user_id',$user_id)->where('stage','<>',3)->value('count');
			}
			
			$view->with('unreply',$unreply);
			$view->with('untasks',$untasks);
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
