@props([
    'name' => null,
    'id' => 'editor',
    'value' => '',
    'height' => 300,
    'placeholder' => 'Write your message here...',
    'toolbar' => null,
    'assetsOnly' => false
])

@once
    @push('scripts')
    <!-- jQuery and Summernote Lite (Modern CDN) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    
    <style>
        /* Modern theme-consistent styling for Summernote */
        .note-editor.note-frame {
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            overflow: hidden;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .note-editor .note-toolbar {
            background-color: #f9fafb !important;
            border-bottom: 1px solid #d1d5db !important;
            margin: 0 !important;
            padding: 8px !important;
        }
        .note-btn {
            background-color: white !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.375rem !important;
            padding: 5px 10px !important;
            margin-right: 4px !important;
            margin-bottom: 4px !important;
            transition: all 0.2s;
            color: #374151 !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .note-btn:hover {
            background-color: #f3f4f6 !important;
            border-color: #d1d5db !important;
            color: #111827 !important;
        }
        .note-btn.active {
            background-color: #00bde0 !important;
            color: white !important;
            border-color: #00bde0 !important;
        }
        .note-btn.active i {
            color: white !important;
        }
        .note-editor.note-frame.focus {
            border-color: #00bde0 !important;
            box-shadow: 0 0 0 2px rgba(0, 189, 224, 0.2) !important;
        }
        .note-placeholder {
            padding: 10px !important;
            color: #9ca3af !important;
        }
        .note-editable {
            background-color: white !important;
            padding: 15px !important;
            font-family: ui-sans-serif, system-ui, sans-serif !important;
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
            color: #1f2937 !important;
        }
    </style>

    <script>
        window.RichTextEditor = {
            defaultToolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear', 'italic']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            
            init: function(selector, options = {}) {
                const config = {
                    placeholder: options.placeholder || 'Write your message here...',
                    tabsize: 2,
                    height: options.height || 300,
                    toolbar: options.toolbar || this.defaultToolbar,
                    callbacks: {
                        onFocus: function() { $(this).next('.note-editor').addClass('focus'); },
                        onBlur: function() { $(this).next('.note-editor').removeClass('focus'); }
                    }
                };
                $(selector).summernote(config);
            },
            
            destroy: function(selector) {
                $(selector).summernote('destroy');
            },
            
            // Shared logic for conditional toggling (e.g., Email/SMS/Event)
            setupConditionalEditor: function(editorSelector, triggerSelector, options = {}) {
                const self = this;
                const trigger = $(triggerSelector);
                let isActive = false;
                
                function toggle() {
                    const val = trigger.val();
                    const shouldBeActive = options.activeValues ? options.activeValues.includes(val) : (val === 'Email' || val === 'Event');
                    
                    if (shouldBeActive && !isActive) {
                        self.init(editorSelector, options);
                        isActive = true;
                    } else if (!shouldBeActive && isActive) {
                        self.destroy(editorSelector);
                        isActive = false;
                    }
                    
                    if (options.onToggle) options.onToggle(val, shouldBeActive);
                }
                
                trigger.on('change', toggle);
                toggle(); // Initial state
                
                return {
                    refresh: toggle,
                    isActive: () => isActive
                };
            }
        };
    </script>
    @endpush
@endonce

@if(!$assetsOnly && $name)
    <div class="rich-text-editor-container">
        <textarea name="{{ $name }}" id="{{ $id }}" class="hidden">{!! $value !!}</textarea>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            window.RichTextEditor.init('#{{ $id }}', {
                placeholder: '{{ $placeholder }}',
                height: {{ $height }},
                toolbar: {!! $toolbar ?? 'null' !!}
            });
        });
    </script>
    @endpush
@endif
