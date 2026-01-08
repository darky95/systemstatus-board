# Systemstatus IT - PowerShell Tools

## Verzeichnisinhalt

### Update-DeviceStatus.ps1
Hauptskript zur Statusaktualisierung von Geräten über die API.

**Verwendung:**
```powershell
# Grundlegende Verwendung
.\Update-DeviceStatus.ps1 -ApiKey "IHR_KEY" -DeviceKey "gerät_01" -Status "gestoert"

# Mit Notiz
.\Update-DeviceStatus.ps1 -ApiKey "IHR_KEY" -DeviceKey "server_01" -Status "wartung" -Note "Geplante Wartung"

# Mit JSON-Format
.\Update-DeviceStatus.ps1 -ApiKey "IHR_KEY" -DeviceKey "db_01" -Status "eingeschraenkt" -UseJson
```

**Parameter:**
- `-ApiKey` (erforderlich): Ihr API-Schlüssel
- `-DeviceKey` (erforderlich): Eindeutiger Geräte-Identifikator
- `-Status` (erforderlich): normal|eingeschraenkt|wartung|gestoert
- `-Note` (optional): Beschreibung des Statuswechsels
- `-UseJson` (optional): Sendet Daten im JSON-Format
- `-ApiUrl` (optional): Angepasste API-URL (Standard: localhost)

### config.example.ps1
Beispiel-Konfigurationsdatei für häufig verwendete Einstellungen.

## Installation

1. PowerShell-Skripte in dieses Verzeichnis kopieren
2. `config.example.ps1` nach `config.ps1` kopieren und anpassen
3. API-Key aus dem Backend-Interface besorgen

## Sicherheitshinweise

- Speichern Sie API-Keys niemals im Quellcode
- Verwenden Sie separate Konfigurationsdateien
- Schützen Sie die Konfigurationsdateien mit geeigneten Berechtigungen
- Nutzen Sie HTTPS für Produktionsumgebungen

## Fehlerbehandlung

Das Skript liefert detaillierte Fehlermeldungen:
- Netzwerkprobleme
- Ungültige API-Keys
- Falsche Geräte-Keys
- Nicht unterstützte Status-Werte

## Beispiele

### Server-Monitoring Integration
```powershell
# In Health-Check-Skripts einbinden
if (-not (Test-Connection -ComputerName $Server -Quiet)) {
    .\Update-DeviceStatus.ps1 -ApiKey $ApiKey -DeviceKey $DeviceKey -Status "gestoert" -Note "Ping fehlgeschlagen"
}
```

### Geplante Wartung
```powershell
# Vor Wartungsarbeiten
.\Update-DeviceStatus.ps1 -ApiKey $ApiKey -DeviceKey "webserver01" -Status "wartung" -Note "Patchday"

# Nach Abschluss
.\Update-DeviceStatus.ps1 -ApiKey $ApiKey -DeviceKey "webserver01" -Status "normal" -Note "Wartung abgeschlossen"
```