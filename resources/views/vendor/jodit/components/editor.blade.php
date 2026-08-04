{{--
    Jodit WYSIWYG Editor Component
    ================================
    Usage:
        <x-jodit::editor name="content" />
        <x-jodit::editor name="content" :value="$post->content" wire-model="content" />
        <x-jodit::editor name="excerpt" :file-browser="false" :height="250" />

    Required layout stacks (add to your layout if not already present):
        @stack('after-styles')   — inside <head>
        @stack('after-scripts')  — before </body>

    Override stack names in config/jodit.php → assets.styles_stack / assets.scripts_stack
--}}

@php
    $joditAssets = \KamikX\LaravelJodit\Support\JoditAssets::configuration();
    $joditScriptAssets = $joditAssets;
    $joditScriptAssets["stylesheet"] = [];
    $allowedMimes = config(
        "jodit.allowed_mimes",
        "jpeg,jpg,png,webp,avif,pdf,doc,docx,xls,xlsx,zip,txt",
    );
@endphp

{{-- ── Load Jodit CSS in the configured head stack ───────────────── --}}
@once
    @push (config("jodit.assets.styles_stack", "after-styles"))
        <link
            rel="stylesheet"
            href="{{ $joditAssets['stylesheet']['cdnUrl'] ?: $joditAssets['stylesheet']['localUrl'] }}"
            @if ($joditAssets["stylesheet"]["cdnUrl"])
                onerror="this.onerror=null;this.href='{{ $joditAssets['stylesheet']['localUrl'] }}'"
            @endif
        />

        <style>
            @keyframes laravel-jodit-spin {
                to {
                    transform: rotate(360deg);
                }
            }

            .laravel-jodit-loading-spinner {
                animation: laravel-jodit-spin 0.8s linear infinite;
                transform-origin: center;
            }
        </style>
    @endpush
@endonce

{{-- ── Load the package asset loader once per page ───────────────── --}}
@once
    @push (config("jodit.assets.scripts_stack", "after-scripts"))
        <script src="{{ asset('vendor/laravel-jodit/laravel-jodit.js') }}"></script>
    @endpush
@endonce

{{--
    ── Editor wrapper ────────────────────────────────────────────────
    wire:ignore prevents Livewire's DOM diffing from destroying the
    editor after every re-render.  It is harmless in non-Livewire
    contexts (the attribute is simply ignored by the browser).
--}}
<div
    id="{{ $editorId }}_wrapper"
    @if ($wireModel) wire:ignore @endif
    class="jodit-wrapper"
    style="position: relative; min-height: {{ $resolvedMinimumHeight }}px; width: 100%"
    aria-busy="true"
>
    <div
        id="{{ $editorId }}_loading"
        role="status"
        aria-live="polite"
        style="position: absolute; z-index: 1; inset: 0; display: flex; align-items: center; justify-content: center"
    >
        <svg
            class="laravel-jodit-loading-spinner"
            aria-hidden="true"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            style="height: 2rem; width: 2rem"
        >
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity="0.25" />
            <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
        </svg>

        <span
            style="
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border: 0;
            "
        >
            {{ __("jodit::editor.loading") }}
        </span>
    </div>

    <div
        id="{{ $editorId }}_error"
        role="alert"
        hidden
        style="margin-bottom: 0.75rem; border: 1px solid #fca5a5; border-radius: 0.5rem; background: #fef2f2; color: #991b1b; padding: 0.75rem"
    >
        <p style="margin: 0">{{ __("jodit::editor.error") }}</p>

        <button
            id="{{ $editorId }}_retry"
            type="button"
            style="
                margin-top: 0.75rem;
                border: 1px solid currentColor;
                border-radius: 0.375rem;
                background: transparent;
                color: inherit;
                cursor: pointer;
                padding: 0.375rem 0.75rem;
            "
        >
            {{ __("jodit::editor.retry") }}
        </button>
    </div>

    <div id="{{ $editorId }}_stage" style="min-height: {{ $resolvedMinimumHeight }}px; width: 100%; visibility: hidden">
        <textarea
            id="{{ $editorId }}"
            name="{{ $name }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($required) required @endif
            @if ($readonly) readonly @endif
            @if ($disabled) disabled @endif
            class="{{ $class }}"
            style="min-height: {{ $resolvedMinimumHeight }}px; width: 100%"
            >{{ $value ?? "" }}</textarea
        >
    </div>
</div>

{{-- ── Per-instance initialisation script ─────────────────────────── --}}
@push (config("jodit.assets.scripts_stack", "after-scripts"))
    <script>
        (function () {
            "use strict";

            var EDITOR_ID = @json ($editorId);
            var WIRE_MODEL = @json ($wireModel);
            var HEIGHT = @json ($resolvedHeight);
            var MINIMUM_HEIGHT = @json ($resolvedMinimumHeight);
            var WITH_BROWSER = @json ($fileBrowser);
            var CONNECTOR = @json ($connectorUrl);
            var BUTTONS = @json ($resolvedButtons);
            var DEBOUNCE_MS = @json ($debounce);
            var DEFAULTS = @json (config("jodit.defaults"));
            var ATOMIC_OPTIONS = @json (config("jodit.atomic_options", []));
            var DISABLE_PLUGINS = @json ($resolvedDisablePlugins);
            var READONLY = @json ($readonly);
            var DISABLED = @json ($disabled);
            var LANGUAGE = @json ($resolvedLanguage);
            var INSTANCE_DISK = @json ($resolvedDisk);
            var INSTANCE_DIR = @json ($resolvedDirectory);
            var INSTANCE_BASE_PATH = @json ($resolvedBasePath);
            var STORAGE_SCOPE = @json ($storageScope);
            var MAX_FILE_SIZE_KB = @json (config("jodit.max_file_size", 10240));
            var ALLOWED_MIMES = @json ($allowedMimes);
            var ASSETS = @json ($joditScriptAssets);
            var LANGUAGE_SCRIPTS = @json (collect(config("jodit.language_scripts", []))->unique()->map(fn(string $script): string => asset($script))->values()->all());
            var PLUGIN_SCRIPTS = @json (collect(config("jodit.plugin_scripts", []))->unique()->map(fn(string $script): string => asset($script))->values()->all());
            var FILE_BROWSER_TYPE = null;

            // Returns extra data fields to scope requests to this editor instance.
            function instanceData() {
                var d = {};
                if (INSTANCE_DISK) {
                    d.disk = INSTANCE_DISK;
                }
                if (INSTANCE_DIR) {
                    d.directory = INSTANCE_DIR;
                }
                if (INSTANCE_BASE_PATH !== null) {
                    d.base_path = INSTANCE_BASE_PATH;
                }
                if (STORAGE_SCOPE) {
                    d.storage_scope = STORAGE_SCOPE;
                }
                if (FILE_BROWSER_TYPE) {
                    d.type = FILE_BROWSER_TYPE;
                }
                return d;
            }

            // ------------------------------------------------------------------
            // Build the Jodit config object
            // ------------------------------------------------------------------
            function buildConfig(csrfToken) {
                var uploadGuard = window.LaravelJodit.createUploadGuard({
                    allowedMimes: ALLOWED_MIMES,
                    maxFileSize: MAX_FILE_SIZE_KB,
                });
                var cfg = Object.assign({}, DEFAULTS, {
                    height: HEIGHT,
                    buttons: BUTTONS,
                });

                if (READONLY) {
                    cfg.readonly = true;
                }
                if (DISABLED) {
                    cfg.disabled = true;
                }
                if (LANGUAGE) {
                    cfg.language = LANGUAGE;
                }
                if (DISABLE_PLUGINS !== null) {
                    cfg.disablePlugins = DISABLE_PLUGINS;
                }

                if (WITH_BROWSER) {
                    if (CONNECTOR) {
                        // ── Built-in connector (or 'custom' backend via connector-url) ──
                        cfg.uploader = {
                            url: CONNECTOR + "?action=upload",
                            headers: { "X-CSRF-TOKEN": csrfToken },
                            data: instanceData(),
                            buildData: uploadGuard.buildData,
                            error: function (uploadError) {
                                this.j.message.error(uploadError.message, 4000);
                            },
                            isSuccess: function (r) {
                                return r === undefined || !!r.success;
                            },
                            getMessage: function (r) {
                                return (r && r.message) || "";
                            },
                            process: function (r) {
                                if (r === undefined) {
                                    return { files: [], baseurl: "", isImages: [] };
                                }

                                return {
                                    files: r.data.files,
                                    baseurl: r.data.baseurl,
                                    newfilename: r.data.newfilename,
                                    isImages: r.data.isImages,
                                    error: r.success ? 0 : 1,
                                    msg: r.message || "",
                                };
                            },
                        };

                        cfg.filebrowser = {
                            isSuccess: function (r) {
                                return !!r.success;
                            },
                            getMessage: function (r) {
                                return r.message || "";
                            },
                            ajax: {
                                url: CONNECTOR,
                                headers: { "X-CSRF-TOKEN": csrfToken },
                                data: instanceData(),
                                prepareData: function (data) {
                                    return Object.assign({}, data || {}, instanceData());
                                },
                            },
                            uploader: {
                                url: CONNECTOR + "?action=upload",
                                headers: { "X-CSRF-TOKEN": csrfToken },
                                data: instanceData(),
                                buildData: uploadGuard.buildData,
                                error: function (uploadError) {
                                    this.j.message.error(uploadError.message, 4000);
                                },
                                isSuccess: function (r) {
                                    return r === undefined || !!r.success;
                                },
                                getMessage: function (r) {
                                    return (r && r.message) || "";
                                },
                                process: function (r) {
                                    if (r === undefined) {
                                        return { files: [], baseurl: "", isImages: [] };
                                    }

                                    return {
                                        files: r.data.files,
                                        baseurl: r.data.baseurl,
                                        newfilename: r.data.newfilename,
                                        isImages: r.data.isImages,
                                        error: r.success ? 0 : 1,
                                        msg: r.message || "",
                                    };
                                },
                            },
                        };
                    }
                }

                return window.LaravelJodit.applyAtomicOptions(cfg, ATOMIC_OPTIONS, window.Jodit.atom);
            }

            var WRAPPER_ID = EDITOR_ID + "_wrapper";
            var LOADING_ID = EDITOR_ID + "_loading";
            var ERROR_ID = EDITOR_ID + "_error";
            var RETRY_ID = EDITOR_ID + "_retry";
            var STAGE_ID = EDITOR_ID + "_stage";

            // ------------------------------------------------------------------
            // Livewire sync helper
            // ------------------------------------------------------------------
            function syncLivewire(el, value) {
                if (!WIRE_MODEL || !window.Livewire) return;

                var wireEl = el.closest("[wire\\:id]");
                if (!wireEl) return;

                var component = window.Livewire.find(wireEl.getAttribute("wire:id"));
                if (component) {
                    component.set(WIRE_MODEL, value);
                }
            }

            function waitForNextPaint() {
                return new Promise(function (resolve) {
                    window.requestAnimationFrame(function () {
                        window.requestAnimationFrame(resolve);
                    });
                });
            }

            function isCurrentWrapper(wrapper) {
                return wrapper.isConnected && document.getElementById(WRAPPER_ID) === wrapper;
            }

            function destroyEditor(editor) {
                if (editor && typeof editor.destruct === "function") {
                    editor.destruct();
                }
            }

            function removeFallbackInputListener(el) {
                if (!el || !el._laravelJoditFallbackListener) return;

                el.removeEventListener("input", el._laravelJoditFallbackListener);
                el._laravelJoditFallbackListener = null;
            }

            function showLoading(wrapper, loading, error, stage) {
                loading.hidden = false;
                loading.style.display = "flex";
                error.hidden = true;
                stage.style.visibility = "hidden";
                wrapper.setAttribute("aria-busy", "true");
            }

            function showEditor(wrapper, loading, error, stage) {
                loading.hidden = true;
                loading.style.display = "none";
                error.hidden = true;
                stage.style.visibility = "visible";
                wrapper.setAttribute("aria-busy", "false");
            }

            function showFallback(wrapper, loading, error, stage, el) {
                el.style.display = "";
                el.style.height = "";
                el.style.minHeight = MINIMUM_HEIGHT + "px";
                el.style.width = "100%";

                el._laravelJoditFallbackListener = function (event) {
                    clearTimeout(el._laravelJoditSyncTimer);
                    el._laravelJoditSyncTimer = setTimeout(function () {
                        syncLivewire(el, event.target.value);
                    }, DEBOUNCE_MS);
                };

                el.addEventListener("input", el._laravelJoditFallbackListener);
                loading.hidden = true;
                loading.style.display = "none";
                error.hidden = false;
                stage.style.visibility = "visible";
                wrapper.setAttribute("aria-busy", "false");
            }

            // ------------------------------------------------------------------
            // Initialise (or re-initialise) the editor
            // ------------------------------------------------------------------
            async function initEditor() {
                var wrapper = document.getElementById(WRAPPER_ID);

                if (!wrapper) {
                    document.removeEventListener("livewire:navigated", initEditor);
                    return;
                }

                if (wrapper.dataset.joditInitializing === "true") return;

                var loading = document.getElementById(LOADING_ID);
                var error = document.getElementById(ERROR_ID);
                var retry = document.getElementById(RETRY_ID);
                var stage = document.getElementById(STAGE_ID);
                var el = document.getElementById(EDITOR_ID);

                if (!loading || !error || !retry || !stage || !el) return;

                if (!retry._laravelJoditBound) {
                    retry.addEventListener("click", initEditor);
                    retry._laravelJoditBound = true;
                }

                wrapper.dataset.joditInitializing = "true";
                clearTimeout(el._laravelJoditSyncTimer);
                removeFallbackInputListener(el);
                showLoading(wrapper, loading, error, stage);

                try {
                    if (
                        !window.LaravelJodit ||
                        typeof window.LaravelJodit.loadJoditAssets !== "function" ||
                        typeof window.LaravelJodit.applyAtomicOptions !== "function"
                    ) {
                        throw new Error("The Laravel Jodit asset loader is unavailable.");
                    }

                    await window.LaravelJodit.loadJoditAssets(
                        Object.assign({}, ASSETS, {
                            languageScripts: LANGUAGE_SCRIPTS,
                            pluginScripts: PLUGIN_SCRIPTS,
                        }),
                    );

                    if (!isCurrentWrapper(wrapper)) return;

                    if (el._jodit && typeof el._jodit.destruct === "function") {
                        el._jodit.destruct();
                    }

                    var initialValue = el.value;
                    var metaEl = document.querySelector('meta[name="csrf-token"]');
                    var csrfToken = metaEl ? metaEl.getAttribute("content") : "";
                    var editor = window.Jodit.make(el, buildConfig(csrfToken));

                    if (typeof editor.waitForReady === "function") {
                        await editor.waitForReady();
                    }

                    if (!isCurrentWrapper(wrapper)) {
                        destroyEditor(editor);
                        return;
                    }

                    editor.value = initialValue;

                    // Patch the filebrowser's open() so each invocation tells the backend
                    // whether it was triggered from the image button (type=images) or the
                    // file button (type=files), enabling server-side filtering.
                    if (WITH_BROWSER && CONNECTOR) {
                        var fb = editor.filebrowser;
                        if (fb && typeof fb.open === "function") {
                            window.LaravelJodit.restrictUploaderInputs(
                                editor.uploader,
                                ALLOWED_MIMES,
                            );
                            window.LaravelJodit.restrictUploaderInputs(
                                fb.uploader,
                                ALLOWED_MIMES,
                                fb.browser,
                            );
                            var _origFbOpen = fb.open.bind(fb);
                            fb.open = function (callback, onlyImages) {
                                FILE_BROWSER_TYPE = onlyImages ? "images" : "files";
                                if (fb.options && fb.options.ajax) {
                                    fb.options.ajax.data = instanceData();
                                }
                                return _origFbOpen(callback, onlyImages);
                            };
                        }
                    }

                    // Sync content changes back to Livewire (debounced)
                    if (WIRE_MODEL && window.Livewire) {
                        editor.events.on("change", function (newValue) {
                            clearTimeout(el._laravelJoditSyncTimer);
                            el._laravelJoditSyncTimer = setTimeout(function () {
                                syncLivewire(el, newValue);
                            }, DEBOUNCE_MS);
                        });
                    }

                    await waitForNextPaint();

                    if (!isCurrentWrapper(wrapper)) {
                        destroyEditor(editor);
                        return;
                    }

                    showEditor(wrapper, loading, error, stage);
                } catch (initializationError) {
                    destroyEditor(el._jodit);

                    if (isCurrentWrapper(wrapper)) {
                        showFallback(wrapper, loading, error, stage, el);
                    }

                    console.error("[laravel-jodit] Editor initialization failed.", initializationError);
                } finally {
                    wrapper.dataset.joditInitializing = "false";
                }
            }

            // ------------------------------------------------------------------
            // Bootstrap
            // ------------------------------------------------------------------

            // Run when the DOM (and Jodit script) are ready
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", initEditor);
            } else {
                initEditor();
            }

            // Re-initialise after Livewire SPA navigations
            document.addEventListener("livewire:navigated", initEditor);
        })();
    </script>
@endpush
