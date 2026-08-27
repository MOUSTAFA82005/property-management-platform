<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    /**
     * Laravel 11+ ships a bare base controller, so `$this->authorize()` is not
     * available unless the trait is pulled in explicitly. Several controllers
     * already call it, so it belongs here rather than being repeated per class.
     */
    use AuthorizesRequests;
}
