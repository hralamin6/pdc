@props([
    'label' => null,
    'hint' => null,
    'placeholder' => null,
    'icon' => null,
    'options' => [],
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'searchable' => true,
    'multiple' => false,
    'clearable' => false,
    'disabled' => false,
    'noResultText' => 'No results found',
])
@php(
$config = [
'plugins' => 'advlist autolink lists link image charmap print preview anchor ' .
                 'searchreplace visualblocks code fullscreen ' .
                 'insertdatetime media table paste code help wordcount autoresize',
                         'toolbar' => 'undo redo | blocks | bold italic underline forecolor backcolor | '
                    . 'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | '
                    . 'link image media table | removeformat | code fullscreen preview help',
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
        // optional: customize dark/light mode
        'skin' =>  'oxide',
        'content_css' =>  'default',
        // optional: enable quick toolbar
        'quickbars_selection_toolbar' => 'bold italic underline | link h2 h3 blockquote',
        'quickbars_insert_toolbar' => 'image media table | hr',
        'contextmenu' => 'link image table spellchecker',
    ]
)
<x-editor wire:model.defer="details" label="Description" hint="The full description" :config="$config" />
