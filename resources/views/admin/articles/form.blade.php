<input name="title" value="{{ old('title', $article->title) }}" class="w-full rounded-md border-gray-300" placeholder="Title" required>
<textarea name="excerpt" rows="2" class="w-full rounded-md border-gray-300" placeholder="Excerpt">{{ old('excerpt', $article->excerpt) }}</textarea>
<textarea name="body" rows="10" class="w-full rounded-md border-gray-300" placeholder="Article body" required>{{ old('body', $article->body) }}</textarea>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $article->is_published ?? false))> Published</label>
<button class="rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save Article</button>
