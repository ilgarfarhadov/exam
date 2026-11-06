<?php



return [
    // Seconds of slack after expires_at before the server treats an attempt as
    // expired, to absorb network latency between the client's timer and the
    // server clock.
    'grace_period_seconds' => (int) env('EXAM_GRACE_PERIOD_SECONDS', 30),
];
