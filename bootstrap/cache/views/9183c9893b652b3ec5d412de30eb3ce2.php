<select name="type" class="w-full rounded-md border-gray-300">
    <option value="resume" <?php if(old('type', $template->type) === 'resume'): echo 'selected'; endif; ?>>Resume</option>
    <option value="cover_letter" <?php if(old('type', $template->type) === 'cover_letter'): echo 'selected'; endif; ?>>Cover Letter</option>
</select>
<input name="name" value="<?php echo e(old('name', $template->name)); ?>" class="w-full rounded-md border-gray-300" placeholder="Template name" required>
<input name="category" value="<?php echo e(old('category', $template->category ?: 'professional')); ?>" class="w-full rounded-md border-gray-300" placeholder="Category" required>
<textarea name="html" rows="8" class="w-full rounded-md border-gray-300" placeholder="Template HTML"><?php echo e(old('html', $template->html)); ?></textarea>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" <?php if(old('is_active', $template->is_active ?? true)): echo 'checked'; endif; ?>> Active</label>
<button class="rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save Template</button>
<?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views/admin/templates/form.blade.php ENDPATH**/ ?>