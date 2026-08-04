@php
    $statePath = $getStatePath();
    $configuredHeight = $field->getHeight();
    $height = $configuredHeight !== 0 && $configuredHeight !== "0"
        ? $configuredHeight
        : config("jodit.defaults.height", "auto");
    $minimumHeight = is_numeric($height) && (int) $height > 0
        ? (int) $height
        : max((int) config("jodit.defaults.minHeight", 200), 1);
    $hasFileBrowser = $field->hasFileBrowser();
    $connectorUrl = $field->getConnectorUrl();
    $basePath = $field->getBasePath();
    $storageScope = \KamikX\LaravelJodit\Support\StorageScope::issue(
        $field->getDisk(),
        $basePath,
        $field->getDirectory(),
    );
    $language = $field->getLanguage() ?? config("jodit.language");
    $languageScripts = collect(config("jodit.language_scripts", []))->unique()->map(fn(string $script): string => asset($script))->values()->all();
    $pluginScripts = collect(config("jodit.plugin_scripts", []))->unique()->map(fn(string $script): string => asset($script))->values()->all();
    $assets = \KamikX\LaravelJodit\Support\JoditAssets::configuration();
    $allowedMimes = config(
        "jodit.allowed_mimes",
        "jpeg,jpg,png,webp,avif,pdf,doc,docx,xls,xlsx,zip,txt",
    );

    if ($hasFileBrowser && $connectorUrl === null) {
        try {
            $connectorUrl = route(config("jodit.route.name", "jodit.connector"));
        } catch (\InvalidArgumentException) {
            $connectorUrl = null;
        }
    }
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        wire:key="{{ $getLivewireKey() }}"
        class="jodit-wrapper"
        style="position: relative; min-height: {{ $minimumHeight }}px; width: 100%"
        aria-busy="true"
        x-load
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('jodit-editor', package: 'kamikx/laravel-jodit') }}"
        x-data="joditEditor({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')", isOptimisticallyLive: false) }},
            assets: @js($assets),
            languageScripts: @js($languageScripts),
            pluginScripts: @js($pluginScripts),
            defaults: @js(config('jodit.defaults')),
            atomicOptions: @js(config('jodit.atomic_options', [])),
            height: @js($height),
            minimumHeight: @js($minimumHeight),
            fileBrowser: @js($hasFileBrowser),
            connectorUrl: @js($connectorUrl),
            buttons: @js($field->getButtons()),
            debounce: @js($field->getDebounce()),
            disabledPlugins: @js($field->getDisabledPlugins()),
            readOnly: @js($field->isReadOnly()),
            disabled: @js($field->isDisabled()),
            language: @js($language),
            disk: @js($field->getDisk()),
            directory: @js($field->getDirectory()),
            basePath: @js($basePath),
            storageScope: @js($storageScope),
            maxFileSize: @js(config('jodit.max_file_size', 10240)),
            allowedMimes: @js($allowedMimes),
        })"
    >
        <div
            x-ref="loading"
            role="status"
            aria-live="polite"
            style="position: absolute; z-index: 1; inset: 0; display: flex; align-items: center; justify-content: center"
        >
            <x-filament::loading-indicator style="height: 2rem; width: 2rem" />

            <span class="fi-sr-only">{{ __("jodit::editor.loading") }}</span>
        </div>

        <div
            x-ref="error"
            role="alert"
            hidden
            class="fi-color-danger fi-bg-color-50 fi-text-color-700 dark:fi-bg-color-950 dark:fi-text-color-300"
            style="margin-bottom: 0.75rem; border-radius: 0.5rem; padding: 0.75rem"
        >
            <p>{{ __("jodit::editor.error") }}</p>

            <x-filament::button type="button" color="danger" size="sm" style="margin-top: 0.75rem" x-on:click="initializeEditor">
                {{ __("jodit::editor.retry") }}
            </x-filament::button>
        </div>

        <div x-ref="editorStage" style="min-height: {{ $minimumHeight }}px; width: 100%; visibility: hidden">
            <textarea
                x-ref="editor"
                id="{{ $getId() }}-jodit-editor"
                name="{{ $statePath }}"
                style="min-height: {{ $minimumHeight }}px; width: 100%"
                @if ($field->getPlaceholder()) placeholder="{{ $field->getPlaceholder() }}" @endif
                @if ($field->isRequired()) required @endif
                @if ($field->isReadOnly()) readonly @endif
                @if ($field->isDisabled()) disabled @endif
                >{{ $getState() ?? "" }}</textarea
            >
        </div>
    </div>
</x-dynamic-component>
