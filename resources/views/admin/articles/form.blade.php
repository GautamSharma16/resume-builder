<input name="title" value="{{ old('title', $article->title) }}" class="w-full rounded-md border-gray-300" placeholder="Title" required>

<label class="block mt-4 mb-2 text-sm font-medium text-gray-700">Thumbnail Image</label>
<input type="file" name="thumbnail" accept="image/*" class="w-full rounded-md border-gray-300">
@if($article->thumbnail ?? false)
    <div class="mt-2">
        <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="Current thumbnail" class="w-32 h-32 object-cover rounded">
        <p class="text-sm text-gray-500 mt-1">Current thumbnail</p>
    </div>
@endif

<select name="category" class="w-full rounded-md border-gray-300" required>
    @foreach(['Freshers', 'Experienced', 'Preparation'] as $category)
        <option value="{{ $category }}" @selected(old('category', $article->category ?? 'Preparation') === $category)>{{ $category }}</option>
    @endforeach
</select>
<textarea name="excerpt" rows="2" class="w-full rounded-md border-gray-300" placeholder="Excerpt">{{ old('excerpt', $article->excerpt) }}</textarea>
<textarea name="body" rows="10" class="w-full rounded-md border-gray-300" placeholder="Article body" required>{{ old('body', $article->body) }}</textarea>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $article->is_published ?? false))> Published</label>
<button class="rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save Article</button>
