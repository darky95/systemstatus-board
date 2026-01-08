<#
.SYNOPSIS
    Aktualisiert den Status eines Geräts ueber die Systemstatus-API
    
.DESCRIPTION
    Dieses Skript ermoeglicht die Aktualisierung von Geraetestatus ueber die REST-API.
    Es unterstuetzt alle verfuegbaren Status-Werte und optionale Notizen.
    
.PARAMETER ApiUrl
    Die vollstaendige URL zur API-Endpunkt (Standard: http://localhost/status5/api/status/update.php)
    
.PARAMETER ApiKey
    Der API-Key fuer die Authentifizierung (erforderlich)
    
.PARAMETER DeviceKey
    Der eindeutige Schluessel des zu aktualisierenden Geraets (erforderlich)
    
.PARAMETER Status
    Der neue Statuswert (erforderlich)
    Gueltige Werte: normal, eingeschraenkt, wartung, gestoert
    
.PARAMETER Note
    Optionale Notiz zum Statuswechsel
    
.PARAMETER UseJson
    Sendet die Daten im JSON-Format statt Form-URL-encoded
    
.EXAMPLE
    .\Update-DeviceStatus.ps1 -ApiKey "abc123" -DeviceKey "webserver01" -Status "gestoert" -Note "Server nicht erreichbar"
    
.EXAMPLE
    .\Update-DeviceStatus.ps1 -ApiKey "xyz789" -DeviceKey "database01" -Status "wartung" -UseJson
    
.NOTES
    Autor: Systemstatus IT
    Version: 1.0
    Datum: 2026-01-08
#>

param(
    [Parameter(Mandatory=$false)]
    [string]$ApiUrl = "http://localhost/status5/api/status/update.php",
    
    [Parameter(Mandatory=$true)]
    [string]$ApiKey,
    
    [Parameter(Mandatory=$true)]
    [string]$DeviceKey,
    
    [Parameter(Mandatory=$true)]
    [ValidateSet("normal", "eingeschraenkt", "wartung", "gestoert")]
    [string]$Status,
    
    [Parameter(Mandatory=$false)]
    [string]$Note = "",
    
    [Parameter(Mandatory=$false)]
    [switch]$UseJson
)

# Funktion zur Statusaktualisierung
function Update-DeviceStatus {
    param(
        [string]$ApiUrl,
        [string]$ApiKey,
        [string]$DeviceKey,
        [string]$Status,
        [string]$Note,
        [bool]$UseJson
    )
    
    try {
        Write-Host "Aktualisiere Geraetestatus..." -ForegroundColor Cyan
        
        # Daten vorbereiten
        if ($UseJson) {
            # JSON-Format
            $Body = @{
                api_key = $ApiKey
                device_key = $DeviceKey
                status = $Status
                note = $Note
            } | ConvertTo-Json
            
            $Headers = @{
                "Content-Type" = "application/json"
            }
            
            Write-Verbose "Sende JSON-Daten: $Body"
            $Response = Invoke-RestMethod -Uri $ApiUrl -Method Post -Body $Body -Headers $Headers -ErrorAction Stop
            
        } else {
            # Form-URL-encoded Format
            $Body = @{
                api_key = $ApiKey
                device_key = $DeviceKey
                status = $Status
                note = $Note
            }
            
            Write-Verbose "Sende Form-Daten: $($Body | Out-String)"
            $Response = Invoke-RestMethod -Uri $ApiUrl -Method Post -Body $Body -ErrorAction Stop
        }
        
        # Antwort verarbeiten
        if ($Response.success) {
            Write-Host "Status erfolgreich aktualisiert!" -ForegroundColor Green
            Write-Host "  Geraet-ID: $($Response.device.id)" -ForegroundColor Gray
            Write-Host "  Geraetename: $($Response.device.name)" -ForegroundColor Gray
            Write-Host "  Neuer Status: $($Response.device.status)" -ForegroundColor Yellow
            
            if ($Note) {
                Write-Host "  Notiz: $Note" -ForegroundColor Gray
            }
            
            return $true
        } else {
            Write-Host "Fehler bei der Statusaktualisierung:" -ForegroundColor Red
            Write-Host "  $($Response.message)" -ForegroundColor Red
            return $false
        }
        
    } catch [System.Net.WebException] {
        Write-Host "Netzwerkfehler:" -ForegroundColor Red
        Write-Host "  $($_.Exception.Message)" -ForegroundColor Red
        return $false
        
    } catch {
        Write-Host "Unerwarteter Fehler:" -ForegroundColor Red
        Write-Host "  $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Hauptausfuehrung
Write-Host "=== Systemstatus IT - Geraetestatus Aktualisierung ===" -ForegroundColor Blue
Write-Host ""

# Parameter anzeigen
Write-Host "Konfiguration:" -ForegroundColor Cyan
Write-Host "  API-URL: $ApiUrl" -ForegroundColor Gray
Write-Host "  Geraete-Key: $DeviceKey" -ForegroundColor Gray
Write-Host "  Status: $Status" -ForegroundColor Gray
if ($Note) {
    Write-Host "  Notiz: $Note" -ForegroundColor Gray
}
Write-Host "  Format: $(if($UseJson){'JSON'}else{'Form-Data'})" -ForegroundColor Gray
Write-Host ""

# Status aktualisieren
$Success = Update-DeviceStatus -ApiUrl $ApiUrl -ApiKey $ApiKey -DeviceKey $DeviceKey -Status $Status -Note $Note -UseJson $UseJson

# Ergebnis
Write-Host ""
if ($Success) {
    Write-Host "Statusaktualisierung abgeschlossen." -ForegroundColor Green
    exit 0
} else {
    Write-Host "Statusaktualisierung fehlgeschlagen!" -ForegroundColor Red
    exit 1
}