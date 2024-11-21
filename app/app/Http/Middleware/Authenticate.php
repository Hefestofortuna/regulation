<?php
namespace App\Http\Middleware;
use Closure;
use DomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Log;
class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;
    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        return $next($request);
//        try {
//            if (array_key_exists('jwt',$_COOKIE)) {
//                $user = JWT::decode($_COOKIE['jwt'], new Key(env('SECRET_KEY'), env('ALG')));
//                if (date('Y-m-d H:i:s') > $user->info->logout) {
//                    return response('Unauthorized.', 401);
//                }
//                return $next($request);
//            } else {
//                throw new DomainException();
//            }
//        } catch (SignatureInvalidException | DomainException | BeforeValidException | ExpiredException $e) {
//            if ($request->path() == "cache")
//                return $next($request);
//            Log::info($e);
//            return response('Forbidden.', 405);
//        }
    }
}
