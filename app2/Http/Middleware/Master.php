<?php 

namespace App\Http\Middleware;
use Illuminate\Contracts\Auth\Guard;
use Closure;
use Session;
class Master
{
    protected $auth;
    public function __construct(Guard $guard){
        $this->auth=$guard;
    }
    
    public function handle($request, Closure $next)
    {
        if($this->auth->user()->rol!=1)
        {return redirect()->route('home');}
        return $next($request); //<-- this line :)
    }

    
}