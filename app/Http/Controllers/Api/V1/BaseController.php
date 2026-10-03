<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

/**
 * Base controller for all API V1 controllers.
 * All controllers should extend this class to gain
 * the standardized ApiResponse trait methods.
 */
abstract class BaseController extends Controller
{
    use ApiResponse;
}
