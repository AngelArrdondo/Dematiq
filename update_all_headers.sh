#!/bin/bash

echo "Updating all HTML files to use header component..."

# Function to add header script to HTML file
add_header_script() {
    local file=$1
    local depth=$(echo "$file" | tr -cd '/' | wc -c)
    local script_path=""
    
    # Calculate relative path to assets/js/components/header.js
    for ((i=1; i<depth; i++)); do
        script_path="../$script_path"
    done
    script_path="${script_path}assets/js/components/header.js"
    
    # Remove duplicate slashes
    script_path=$(echo "$script_path" | sed 's|//|/|g')
    
    echo "  📄 $file -> $script_path"
    
    # Add header.js before </body> if not already present
    if ! grep -q "header.js" "$file"; then
        sed -i "s|</body>|  <script src=\"$script_path\"></script>\n</body>|" "$file"
        echo "    ✅ Added header script"
    else
        echo "    ⏭️  Already has header script"
    fi
}

# Process all HTML files
find . -name "*.html" -not -path "./node_modules/*" -not -path "./.git/*" | while read file; do
    add_header_script "$file"
done

echo ""
echo "✅ Done! All HTML files updated."
echo "⚠️  Remember to remove old <header>...</header> code from each file manually."
