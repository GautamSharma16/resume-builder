#!/usr/bin/env python3

file_path = r'c:\xampp\htdocs\resume-builder\resume-builder\resources\views\resume\partials\editor-script.blade.php'

# Read the file
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the problematic regex patterns
replacements = [
    ('/{{skills}}/g', r'/\{\{skills\}\}/g'),
    ('/{{experience}}/g', r'/\{\{experience\}\}/g'),
    ('/{{education}}/g', r'/\{\{education\}\}/g'),
    ('/{{projects}}/g', r'/\{\{projects\}\}/g'),
]

for old, new in replacements:
    if old in content:
        content = content.replace(old, new)
        print(f"Replaced: {old} -> {new}")
    else:
        print(f"Not found: {old}")

# Write the file back
with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("\nFile updated successfully!")
