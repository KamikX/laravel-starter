<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Optional CDN Overrides
    |--------------------------------------------------------------------------
    |
    | Published local assets are used by default. When a CDN URL is provided,
    | it is tried first and the package falls back to its local copy on error.
    | Array values are loaded sequentially in the configured order.
    |
    */

    'cdn_css' => null,
    'cdn_js' => null,

    'beautify_cdn_urls_js' => [],
    'ace_cdn_urls_js' => [],
    'ace_worker_cdn_url' => null,

    /*
    |--------------------------------------------------------------------------
    | HTML Beautifier Options
    |--------------------------------------------------------------------------
    |
    | These options are passed directly to js-beautify when Jodit's
    | beautifyHTML option is enabled. Any js-beautify option is supported.
    |
    */

    'beautify_html_options' => [
        'indent_size' => 4,
        'indent_char' => ' ',
        'preserve_newlines' => false,
        'max_preserve_newlines' => 1,
        'wrap_line_length' => 120,
        'wrap_attributes' => 'force-aligned',
        'extra_liners' => [],
        'content_unformatted' => ['pre', 'textarea'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Language Scripts
    |--------------------------------------------------------------------------
    |
    | JavaScript files that register custom translations via Jodit.lang.
    | They are loaded once per page, after Jodit itself and before custom
    | plugins and editor instances. Local asset paths and absolute URLs are
    | supported.
    |
    */

    'language_scripts' => [
        // 'js/jodit/langs/sk_sk.js',
        // 'https://cdn.example.com/jodit/langs/sk_custom.js',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Plugin Scripts
    |--------------------------------------------------------------------------
    |
    | JavaScript files that register custom plugins via Jodit.plugins.add().
    | They are loaded once per page, after Jodit itself and before any editor
    | instance is created. Local asset paths and absolute URLs are supported.
    |
    */

    'plugin_scripts' => [
        // 'js/jodit/plugins/statistics.js',
        // 'https://cdn.example.com/jodit/plugins/custom-plugin.js',
    ],

    /*
    |--------------------------------------------------------------------------
    | Asset Blade Stacks
    |--------------------------------------------------------------------------
    |
    | Names of the @stack() stacks that the editor component pushes the CSS
    | and JS tags into.  Make sure your layout calls @stack('after-styles')
    | inside <head> and @stack('after-scripts') just before </body>.
    |
    */

    'assets' => [
        'styles_stack' => 'after-styles',
        'scripts_stack' => 'after-scripts',
    ],

    /*
    |--------------------------------------------------------------------------
    | Connector Route
    |--------------------------------------------------------------------------
    |
    | The package can register its own connector route automatically.
    | Set `enabled` to false and register the route yourself (see README) if
    | you need a custom prefix or middleware stack.
    |
    */

    'route' => [
        'enabled' => true,
        'prefix' => 'jodit',
        'name' => 'jodit.connector',
        'middleware' => ['web', 'throttle:60,1'],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Storage
    |--------------------------------------------------------------------------
    |
    | The disk and base path used for all Jodit uploads and file browser
    | listings. The disk must exist in config/filesystems.php. Components
    | send these values in an encrypted scope so clients cannot change roots.
    | Disable scope validation only for backwards-compatible custom clients.
    |
    */

    'disk' => 'public',
    'base_path' => 'uploads',
    'user_directory' => false,
    'require_storage_scope' => true,

    /*
    |--------------------------------------------------------------------------
    | Upload Constraints
    |--------------------------------------------------------------------------
    */

    'max_file_size' => 10240, // kilobytes
    'allowed_mimes' => 'jpeg,jpg,png,svg,webp,avif,zip,txt,docx,xlsx',
    'preserve_file_names' => false,

    /*
    |--------------------------------------------------------------------------
    | Editor Language
    |--------------------------------------------------------------------------
    |
    | UI language for the Jodit toolbar and dialogs. Set to null to let the
    | browser decide (Jodit auto-detects from navigator.language).
    | Examples: 'en', 'de', 'fr', 'ar', 'zh_cn'.
    |
    */

    'language' => 'sk',

    /*
    |--------------------------------------------------------------------------
    | Default Editor Options
    |--------------------------------------------------------------------------
    |
    | Any key/value pair here is merged into the Jodit config object before
    | the editor is instantiated.  See https://xdsoft.net/jodit/docs/ for all
    | available options.
    |
    */

    'defaults' => [
        'height' => 'auto',
        'toolbarSticky' => true,
        'toolbarButtonSize' => 'middle',
        'showCharsCounter' => true,
        'showWordsCounter' => true,
        'showXPathInStatusbar' => true,
        'hidePoweredByJodit' => true,
        'defaultActionOnPaste' => 'insert_clear_html',
        'beautifyHTML' => true,
        'beautifyHTMLCDNUrlsJS' => [],
        'sourceEditor' => 'ace',
        'sourceEditorCDNUrlsJS' => [],
        'sourceEditorNativeOptions' => [
            'mode' => 'ace/mode/html',
            'theme' => 'ace/theme/idle_fingers',
            'wrap' => true,
        ],
        'uploader' => [
            'showTabInFileSelector' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Editor Presets
    |--------------------------------------------------------------------------
    |
    | Named sets of Jodit options that can be reused by Blade components and
    | Filament fields. Options are merged in this order: defaults, preset,
    | per-instance config, and finally explicit component/field settings.
    | Nested maps are merged recursively while lists are replaced in full.
    |
    */

    'default_preset' => null,

    'presets' => [
        'article' => [
            // 'minHeight' => 800,
            'toolbarButtonSize' => 'large',
            'toolbarSticky' => true,
            'toolbarStickyOffset' => 80,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Atomic Editor Options
    |--------------------------------------------------------------------------
    |
    | Dot-notated paths listed here are wrapped in Jodit.atom() before the
    | editor is instantiated. Atomic values replace Jodit's built-in value
    | instead of being merged with it.
    |
    */

    'atomic_options' => [],

    /*
    |--------------------------------------------------------------------------
    | Toolbar Profiles
    |--------------------------------------------------------------------------
    |
    | Named button sets. Select a profile per-instance with the `profile` prop:
    |   <x-jodit::editor name="body" profile="simple" />
    |
    | `default_profile` is used when no `buttons` or `profile` prop is given.
    | Set to null to fall back to the `buttons` array defined above.
    |
    */

    'default_profile' => 'full',

    'profiles' => [
        'full' => [
            'undo',
            'redo',
            '|',
            'bold',
            'italic',
            'underline',
            'strikethrough',
            'superscript',
            'subscript',
            'eraser',
            '|',
            'paragraph',
            'font',
            'fontsize',
            'brush',
            'classSpan',
            '|',
            'align',
            'ul',
            'ol',
            'indent',
            'outdent',
            '|',
            'cut',
            'copy',
            'paste',
            'selectall',
            '|',
            'link',
            'image',
            'video',
            'file',
            'table',
            'hr',
            'symbols',
            '|',
            'source',
            '|',
            'find',
            'spellcheck',
            'preview',
            'fullsize',
        ],
        'simple' => [
            'bold',
            'italic',
            'eraser',
            '|',
            'paragraph',
            'align',
            'ul',
            'ol',
            '|',
            'link',
            'image',
            'table',
            'hr',
            '|',
            'source',
            '|',
            'undo',
            'redo',
        ],
        'minimal' => ['bold', 'italic', 'eraser', '|', 'source', '|', 'link'],
    ],
];
