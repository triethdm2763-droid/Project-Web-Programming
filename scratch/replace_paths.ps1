$search = '/Project-Web-Programming/'
$replace = '/'

Get-ChildItem -Path . -Include *.php, *.html, *.js, *.css -Recurse | Where-Object { $_.FullName -notmatch '\\(vendor|node_modules|scratch|brain)\\' } | ForEach-Object {
    $content = Get-Content $_.FullName -Raw
    if ($content -ne $null -and $content.Contains($search)) {
        $content = $content.Replace($search, $replace)
        Set-Content -Path $_.FullName -Value $content -NoNewline
    }
}
Write-Output "Done replacing."
