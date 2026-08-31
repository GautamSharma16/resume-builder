@if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <div class="font-semibold">Please fix the errors below.</div>
        <ul class="mt-1 list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-5">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
        <input name="title"
               value="{{ old('title', $article->title) }}"
               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500"
               placeholder="e.g. Top 25 React Interview Questions"
               required>
        @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <div class="flex gap-2">
                <div class="flex-1" id="category_select_container">
                    <select name="category"
                            id="category_select"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(old('category', $article->category ?? 'Preparation') === $category)>{{ $category }}</option>
                        @endforeach
                        <option value="__new__">+ Add New Category</option>
                    </select>
                </div>
                <div class="flex-1 hidden" id="category_input_container">
                    <input type="text" 
                           id="category_input"
                           placeholder="New category name"
                           class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                </div>
                <button type="button" 
                        id="cancel_new_category" 
                        class="hidden rounded-xl bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                    Cancel
                </button>
            </div>
            @error('category')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Thumbnail image</label>
            <input type="file"
                   name="thumbnail"
                   accept="image/*"
                   class="block w-full text-sm text-gray-600
                          file:mr-3 file:rounded-xl file:border-0
                          file:bg-gray-900 file:px-3 file:py-2
                          file:text-sm file:font-semibold file:text-white
                          hover:file:bg-gray-800 cursor-pointer">
            @error('thumbnail')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

            @if($article->thumbnail ?? false)
                <div class="mt-3 flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3">
                    <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="Current thumbnail" class="w-14 h-14 object-cover rounded-lg border border-gray-200">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900">Current thumbnail</p>
                        <p class="text-xs text-gray-500 truncate">{{ basename($article->thumbnail) }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
        <textarea name="excerpt"
                  rows="3"
                  class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500"
                  placeholder="Short preview shown in listings (optional)">{{ old('excerpt', $article->excerpt) }}</textarea>
        @error('excerpt')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Body</label>
        <textarea name="body"
                  id="body_editor"
                  rows="14"
                  class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm leading-relaxed focus:border-teal-500 focus:ring-teal-500"
                  placeholder="Write the article content here...">{{ old('body', $article->body) }}</textarea>
        @error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pt-2 border-t border-gray-100">
        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 select-none">
            <input type="checkbox"
                   name="is_published"
                   value="1"
                   @checked(old('is_published', $article->is_published ?? false))
                   class="rounded border-gray-300 text-teal-700 focus:ring-teal-500">
            Published
        </label>

        <button class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">
            Save article
        </button>
    </div>
</div>

<script defer src="https://cdn.tiny.cloud/1/{{ env('TINYMCE_API_KEY') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // TinyMCE Initialization
        tinymce.init({
            selector: '#body_editor',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            height: 500,
            skin: 'oxide',
            content_css: 'default',
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
            }
        });

        // Category Logic
        const categorySelect = document.getElementById('category_select');
        const categoryInput = document.getElementById('category_input');
        const selectContainer = document.getElementById('category_select_container');
        const inputContainer = document.getElementById('category_input_container');
        const cancelBtn = document.getElementById('cancel_new_category');

        categorySelect.addEventListener('change', function() {
            if (this.value === '__new__') {
                selectContainer.classList.add('hidden');
                inputContainer.classList.remove('hidden');
                cancelBtn.classList.remove('hidden');
                categoryInput.focus();
                // Change name attribute so input value is submitted instead of select
                categorySelect.removeAttribute('name');
                categoryInput.setAttribute('name', 'category');
                categoryInput.setAttribute('required', true);
            }
        });

        cancelBtn.addEventListener('click', function() {
            selectContainer.classList.remove('hidden');
            inputContainer.classList.add('hidden');
            cancelBtn.classList.add('hidden');
            categorySelect.value = categorySelect.options[0].value;
            // Restore name attribute to select
            categorySelect.setAttribute('name', 'category');
            categoryInput.removeAttribute('name');
            categoryInput.removeAttribute('required');
        });
    });
</script>

