const fs = require('fs');
const path = require('path');

const filePath = path.join(__dirname, 'resources', 'views', 'resume', 'partials', 'editor-script.blade.php');

// Read the file
let content = fs.readFileSync(filePath, 'utf-8');

console.log('Original regex patterns found:');

// Replace the problematic regex patterns
const replacements = [
    { old: '/{{skills}}/g', new: r'/\{\{skills\}\}/g' },
    { old: '/{{experience}}/g', new: r'/\{\{experience\}\}/g' },
    { old: '/{{education}}/g', new: r'/\{\{education\}\}/g' },
    { old: '/{{projects}}/g', new: r'/\{\{projects\}\}/g' },
];

for (const { old, new: newStr } of replacements) {
    if (content.includes(old)) {
        content = content.replace(new RegExp(old.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), newStr);
        console.log(`✓ Replaced: ${old} -> ${newStr}`);
    } else {
        console.log(`✗ Not found: ${old}`);
    }
}

// Write the file back
fs.writeFileSync(filePath, content, 'utf-8');

console.log('\n✓ File updated successfully!');
