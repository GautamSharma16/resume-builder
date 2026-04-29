#!/bin/bash
cd "c:\xampp\htdocs\resume-builder\resume-builder"
# backup original
cp "resources\views\resume\partials\editor-script.blade.php" "resources\views\resume\partials\editor-script.blade.php.bak"
# replace with fixed
mv "resources\views\resume\partials\editor-script-new.blade.php" "resources\views\resume\partials\editor-script.blade.php"
echo "File replaced successfully"
