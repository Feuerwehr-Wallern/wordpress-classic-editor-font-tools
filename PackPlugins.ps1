Set-Location -Path $PSScriptRoot

$releaseDir = "_Release"
$logFile = "PackPlugins.log"

# ==============================
# UTF-8 ohne BOM Writer (direkter String)
# ==============================
function Write-Utf8NoBom {
    param (
        [string]$Path,
        [string]$Content   # String, keine Array-Aufteilung
    )
    $utf8NoBom = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllText($Path, $Content, $utf8NoBom)
}

# ==============================
# Git verfügbar?
# ==============================
$gitAvailable = $false
try {
    git --version | Out-Null
    $gitAvailable = $true
} catch {
    Write-Warning "Git nicht gefunden – Git-Funktionen deaktiviert."
}

# ==============================
# Log starten
# ==============================
Write-Utf8NoBom $logFile @"
=== WordPress Plugin Packager Log ===
Startzeit: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')

"@

# ==============================
# Release-Ordner vorbereiten
# ==============================
if (-not (Test-Path $releaseDir)) {
    New-Item -ItemType Directory -Path $releaseDir | Out-Null
}
Write-Host "Release-Ordner: $releaseDir bereit."

Write-Host "`nStarte Packprozess...`n"

# ==============================
# Plugin-Ordner durchgehen
# ==============================
Get-ChildItem -Directory | Where-Object {
    $_.Name -notin @($releaseDir, '.git')
} | ForEach-Object {

    $pluginDir  = $_.FullName
    $pluginName = $_.Name

    Write-Host "================================="
    Write-Host "Plugin: $pluginName"
    Write-Host "================================="

    # ==============================
    # Haupt-PHP finden
    # ==============================
    $mainPhp = Get-ChildItem $pluginDir -Filter *.php | Where-Object {
        Select-String $_.FullName -Pattern 'Plugin Name:' -Quiet
    } | Select-Object -First 1

    if (-not $mainPhp) {
        Write-Warning "Keine Haupt-PHP-Datei – übersprungen."
        return
    }

    # ==============================
    # Version lesen
    # ==============================
    $match = Select-String $mainPhp.FullName -Pattern 'Version:\s*([0-9]+\.[0-9]+\.[0-9]+)'
    $oldVersion = if ($match) { $match.Matches[0].Groups[1].Value } else { "0.0.0" }

    Write-Host "Aktuelle Version: $oldVersion"
    Write-Host "0 = nein | 1 = Major | 2 = Minor | 3 = Patch"

    $choice = Read-Host "Auswahl"
    $versionChanged = $false

    if ($choice -in @("1","2","3")) {
        $parts = $oldVersion.Split('.')
        if ($parts.Count -ne 3 -or ($parts | Where-Object { $_ -notmatch '^\d+$' })) {
            $major = 0; $minor = 0; $patch = 0
        } else {
            $major = [int]$parts[0]
            $minor = [int]$parts[1]
            $patch = [int]$parts[2]
        }

        switch ($choice) {
            "1" { $major++; $minor = 0; $patch = 0 }
            "2" { $minor++; $patch = 0 }
            "3" { $patch++ }
        }

        $version = "$major.$minor.$patch"
        $versionChanged = $true
        Write-Host "Neue Version: $version"
    } elseif ($choice -eq "0") {
        $version = $oldVersion
        Write-Host "Version bleibt unverändert: $version"
    } else {
        Write-Host "Ungültige Auswahl – übersprungen.`n"
        return
    }

    $releaseNote = Read-Host "Release-Notes (optional)"

    # ==============================
    # PHP-Version aktualisieren, nur wenn sich Version geändert hat
    # ==============================
    if ($versionChanged) {
        $phpContent = Get-Content $mainPhp.FullName -Raw
        $phpContent = $phpContent -replace '(?<=Version:\s*)[0-9]+\.[0-9]+\.[0-9]+', $version
        Write-Utf8NoBom $mainPhp.FullName $phpContent
    }

    # ==============================
    # CHANGELOG.md (Markdown)
    # ==============================
    if ($releaseNote) {
        $changelogPath = Join-Path $pluginDir "CHANGELOG.md"
        $header = "## [$pluginName] v$version – $(Get-Date -Format 'yyyy-MM-dd')"
        $entry = "$header`n- $releaseNote`n"

        if (Test-Path $changelogPath) {
            $existing = Get-Content $changelogPath -Raw
            $newChangelog = $entry + "`n" + $existing
        } else {
            $newChangelog = $entry
        }

        Write-Utf8NoBom $changelogPath $newChangelog
    }

    # ==============================
    # ZIP erstellen (im Repo!)
    # ==============================
    $zipPath = Join-Path $releaseDir "$pluginName.zip"
    if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
    Compress-Archive "$pluginDir\*" $zipPath -Force
    Write-Host "ZIP erstellt: $zipPath"

    # ==============================
    # Git Commit + Tag pro Plugin
    # ==============================
    if ($gitAvailable) {
        git add $pluginDir
        git add $zipPath

        $commitMessage = @(
            "Release: $pluginName v$version"
        )

        if ($releaseNote) {
            $commitMessage += ""
            $commitMessage += $releaseNote
        }

        git commit -m ($commitMessage -join "`n")
        Write-Host "? Commit für $pluginName erstellt"

        $tagName = "$pluginName/v$version"
        git tag -a $tagName -m "Release $tagName"
        Write-Host "? Tag $tagName erstellt"

        if ((Read-Host "Git push für $pluginName (Commit + Tag inkl. ZIP)? (j/n)") -eq "j") {
            git push
            git push --tags
            Write-Host "? Push für $pluginName ausgeführt"
        }
    }

    Write-Host ""
}

Write-Host "`nAlle Plugins verarbeitet."
Write-Host "Fertig."
