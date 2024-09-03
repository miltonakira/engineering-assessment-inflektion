<?php
namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class ApiHandler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'errors' => $e->errors(),
                'message' => 'Validation failed.',
            ], 422);
        }

        return parent::render($request, $e);
    }
}