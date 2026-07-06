<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TinyMCE Editor Configuration
    |--------------------------------------------------------------------------
    |
    | Default configuration for TinyMCE editor instances.
    | You can override these settings per-instance as needed.
    |
    */

    'default' => [
        'plugins' => 'advlist autolink lists link image charmap preview anchor ' .
                     'searchreplace visualblocks code fullscreen ' .
                     'insertdatetime media table code help wordcount autoresize',

        'toolbar' => 'undo redo | blocks | bold italic underline forecolor backcolor | ' .
                     'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | ' .
                     'link image media table | removeformat | code fullscreen preview help',

        'menubar' => 'file edit view insert format tools table help',
        'branding' => false,
        'height' => 400,
        'min_height' => 300,
        'max_height' => 700,
        'statusbar' => true,
        'autosave_interval' => '30s',
        'autosave_restore_when_empty' => true,
        'content_style' => 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
        'image_title' => true,
        'automatic_uploads' => true,
        'file_picker_types' => 'image media',
        'content_css' => 'default',
        'quickbars_selection_toolbar' => 'bold italic underline | link h2 h3 blockquote',
        'quickbars_insert_toolbar' => 'image media table | hr',
        'contextmenu' => 'link image table spellchecker',
    ],

    /*
    |--------------------------------------------------------------------------
    | Simple Configuration
    |--------------------------------------------------------------------------
    |
    | A minimal configuration for basic text editing.
    |
    */

    'simple' => [
        'plugins' => 'lists link autolink',
        'toolbar' => 'undo redo | bold italic underline | bullist numlist | link',
        'menubar' => false,
        'branding' => false,
        'height' => 300,
        'statusbar' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Basic Configuration
    |--------------------------------------------------------------------------
    |
    | A basic configuration with common formatting options.
    |
    */

    'basic' => [
        'plugins' => 'advlist autolink lists link image code',
        'toolbar' => 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
        'menubar' => false,
        'branding' => false,
        'height' => 350,
        'statusbar' => true,
    ],
];

