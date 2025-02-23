<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(title="Wedding Invitation API", version="1.0")
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class ApiController extends Controller {
    // This class serves as a central place for OpenAPI documentation
}
