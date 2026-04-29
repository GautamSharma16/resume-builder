<select name="type" class="w-full rounded-md border-gray-300">
    <option value="resume" @selected(old('type', $template->type) === 'resume')>Resume</option>
    <option value="cover_letter" @selected(old('type', $template->type) === 'cover_letter')>Cover Letter</option>
</select>
<input name="name" value="{{ old('name', $template->name) }}" class="w-full rounded-md border-gray-300" placeholder="Template name" required>
<select name="category" class="w-full rounded-md border-gray-300" required>
    @foreach(['ats' => 'ATS Resume', 'fresher' => 'Fresher Resume', 'experienced' => 'Resume for Experienced', 'word' => 'MS Word Resume', 'professional' => 'Professional Cover Letter', 'modern' => 'Modern Cover Letter', 'executive' => 'Executive Cover Letter', 'minimal' => 'Minimal Cover Letter'] as $value => $label)
        <option value="{{ $value }}" @selected(old('category', $template->category ?: 'professional') === $value)>{{ $label }}</option>
    @endforeach
</select>
<textarea name="html" rows="8" class="w-full rounded-md border-gray-300" placeholder="Template HTML">{{ old('html', $template->html) }}</textarea>
<input type="file" name="preview_image" accept="image/*" class="w-full rounded-md border border-gray-300 p-2 text-sm">
@if($template->preview_image)
    <p class="text-xs text-gray-500">Current preview image: {{ $template->preview_image }}</p>
@endif
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ?? true))> Active</label>
<button class="rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save Template</button>
