$path = "c:\Users\Mohsin Ali\Documents\institute_course\resources\js\editor\page-builder.js"
$methodPath = "c:\Users\Mohsin Ali\Documents\institute_course\resources\js\editor\temp_method.js"
$lines = Get-Content $path -Encoding UTF8

# Header: Lines 1-294 (Index 0-293)
$header = $lines[0..293]

# Footer: Lines 407-End (Index 406-End)
$footer = $lines[406..($lines.Count - 1)]

$newMethod = Get-Content $methodPath -Encoding UTF8

# Validating
Write-Output "Header length: $($header.Count)"
Write-Output "Footer length: $($footer.Count)"
Write-Output "New method length: $($newMethod.Count)"

# Combine
$header + $newMethod + $footer | Set-Content $path -Encoding UTF8

Write-Output "File spliced successfully."
