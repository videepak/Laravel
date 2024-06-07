<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Debug\Exception\FlattenException;
use Illuminate\Session\TokenMismatchException;

class Handler extends ExceptionHandler
{

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if (!$this->isHttpException($exception)) {
            $e = new \Symfony\Component\HttpKernel\Exception\HttpException(500);
            if (config('app.env') === 'production') {
                return response()->view('errors.500', ['exception' => $e], $e->getStatusCode(), $e->getHeaders());
            }
        }
        if ($exception instanceof TokenMismatchException) {
            return redirect()->back()->withInput()->with('error', 'Your session has expired');
        }
        
        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
      
        if ($request->expectsJson()) {
            return response()
                ->json(
                    [
                        'message' => "success",
                        'data' => [
                            'responseCode' => 201,
                            'responseMsg' => 'The session has been expired. Please try again.'
                        ],
                        'status' => 405
                    ],
                    200
                );
            //return response()->json(['error' => 'Token Expired.'], 405);
        }

        $guard = array_get($exception->guards(), 0);
        
        switch ($guard) {
            case 'api':
                return response()
                    ->json(
                        [
                            'message' => "success",
                            'data' => [
                                'responseCode' => 201,
                                'responseMsg' => 'The session has been expired. Please try again.'
                            ],
                            'status' => 405
                        ],
                        200
                    );
            case 'admin':
                $login = 'admin.login';
                break;
            default:
                $login = 'login';
                break;
        }
        return redirect()->guest(route($login));
    }
}
