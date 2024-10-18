<?php

use Illuminate\Support\Facades\Facade;

return [
    /**
     * Disk name of Storage, what  will use for storing all attachments
     */
    'disk' => 'public',

    /**
     * Attachment Path toward storage disk location
     */
    'attachment_path' => env('ATTACH_DIR', 'attachments'),

    /**
     * Inline Attachments path to storage disk location
     */
    'inline_attachment_path' =>  env('INLINE_ATTACH_DIR', 'inline-attachments'),
    'filtered_attachment_path' =>  env('FILTER_ATTACH_DIR', 'filtered-attachments'),
];
