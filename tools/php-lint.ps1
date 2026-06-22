param(
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Paths
)

$repoRoot = Split-Path -Parent $PSScriptRoot
$targetPaths = @()

if ($Paths -and $Paths.Count -gt 0) {
    foreach ($path in $Paths) {
        $fullPath = Resolve-Path -Path (Join-Path $repoRoot $path) -ErrorAction SilentlyContinue
        if ($fullPath) {
            $targetPaths += $fullPath.Path
        } else {
            Write-Error "Caminho nao encontrado: $path"
            exit 1
        }
    }
} else {
    $targetPaths = Get-ChildItem -Path (Join-Path $repoRoot 'App'), (Join-Path $repoRoot 'Lib') -Recurse -File -Filter '*.php' |
        Select-Object -ExpandProperty FullName
}

if (-not $targetPaths -or $targetPaths.Count -eq 0) {
    Write-Host 'Nenhum arquivo PHP encontrado para validar.'
    exit 0
}

$containerPaths = $targetPaths | ForEach-Object {
    $_.Replace($repoRoot, '/var/www/html').Replace('\', '/')
}

$cmd = @('compose', 'run', '--rm', 'php', 'sh', '-lc')
$phpChecks = $containerPaths | ForEach-Object { "php -l '$_'" }
$lintCommand = ($phpChecks -join ' && ')

& docker @cmd $lintCommand
exit $LASTEXITCODE
