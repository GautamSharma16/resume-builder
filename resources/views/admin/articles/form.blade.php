@if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <div class="font-semibold">Please fix the errors below.</div>
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
            <select name="category"
                    class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500"
                    required>
                @foreach(['Freshers', 'Experienced', 'Preparation'] as $category)
                    <option value="{{ $category }}" @selected(old('category', $article->category ?? 'Preparation') === $category)>{{ $category }}</option>
                @endforeach
            </select>
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
                  rows="14"
                  class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm leading-relaxed focus:border-teal-500 focus:ring-teal-500"
                  placeholder="Write the article content here..."
                  required>{{ old('body', $article->body) }}</textarea>
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
