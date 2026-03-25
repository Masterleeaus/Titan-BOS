<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Titan-BOS Node API URL
    |--------------------------------------------------------------------------
    |
    | The base URL of the local Python node HTTP API server. Set this in
    | your .env file as TITAN_NODE_API_URL. The default assumes the node
    | process is running on the same machine as the web server.
    |
    */
    'api_url' => env('TITAN_NODE_API_URL', 'http://127.0.0.1:8765'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout (seconds)
    |--------------------------------------------------------------------------
    |
    | Maximum seconds to wait for the node API to respond. Keep this short
    | so a stopped node doesn't freeze page loads.
    |
    */
    'timeout' => env('TITAN_NODE_TIMEOUT', 5),
];
