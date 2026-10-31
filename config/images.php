<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Responsive image output
    |--------------------------------------------------------------------------
    |
    | Originals are always retained. WebP derivatives are generated alongside
    | them and are used only when the browser asks for an appropriate size.
    |
    */
    'responsive_widths' => [480, 800, 1200, 1600],
    'webp_quality' => (int) env('IMAGE_WEBP_QUALITY', 80),
    'max_source_pixels' => (int) env('IMAGE_MAX_SOURCE_PIXELS', 24000000),
];
