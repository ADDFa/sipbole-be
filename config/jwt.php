<?php

return [
    "secret"        => env("JWT_SECRET", "your-jwt-secret"),
    "refresh"       => env("JWT_REFRESH", "your-jwt-refresh"),
    "algo"          => env("JWT_ALGO", "HS256"),
    "exp_access"    => 3600, // 1 hours
    "exp_refresh"   => 604800 // 7 days
];
