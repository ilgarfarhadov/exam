<?php



return [
    // Set once per SEB Configuration Tool export (Faza 3.1) - this key is
    // never committed, only ever lives in the server's .env.
    'config_key' => env('SEB_CONFIG_KEY'),

    // Kill switch: false lets non-SEB browsers through even with a key set
    // (soft rollout / staging). true enforces the check, and - critically -
    // fails closed if config_key is missing entirely in production.
    'required' => env('SEB_REQUIRED', false),
];
