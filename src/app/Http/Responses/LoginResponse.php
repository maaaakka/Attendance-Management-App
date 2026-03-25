<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
{
    if (auth()->guard('admin')->check()) {
        return redirect('/admin/attendance/list');
    }

    return redirect('/attendance');
}
}