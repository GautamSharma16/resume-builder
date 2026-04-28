<select name="type" class="w-full rounded-md border-gray-300">
    <option value="resume" @selected(old('type', $template->type) === 'resume')>Resume</option>
    <option value="cover_letter" @selected(old('type', $template->type) === 'cover_letter')>Cover Letter</option>
</select>
<input name="name" value="{{ old('name', $template->name) }}" class="w-full rounded-md border-gray-300" placeholder="Template name" required>
<input name="category" value="{{ old('category', $template->category ?: 'professional') }}" class="w-full rounded-md border-gray-300" placeholder="Category" required>
<textarea name="html" rows="8" class="w-full rounded-md border-gray-300" placeholder="Template HTML">{{ old('html', $template->html) }}</textarea>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ?? true))> Active</label>
<button class="rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save Template</button>
