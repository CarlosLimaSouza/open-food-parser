<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Open Food Facts Parser API",
    version: "1.0.0",
    description: "API REST para curadoria de dados do Open Food Facts",
    contact: new OA\Contact(email: "suporte@fitnessfood.lc")
)]
#[OA\Server(
    url: "/api",
    description: "API Principal"
)]
#[OA\SecurityScheme(
    securityScheme: "ApiKeyAuth",
    type: "apiKey",
    in: "header",
    name: "x-api-key"
)]
class OpenApi {}
