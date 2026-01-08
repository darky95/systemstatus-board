# Beispiel-Konfiguration für Update-DeviceStatus.ps1
# Kopieren Sie diese Datei und passen Sie die Werte an

# API-Konfiguration
$Config = @{
    # API-Endpunkt (passen Sie die Domain an)
    ApiUrl = "http://ihre-domain.de/status5/api/status/update.php"
    
    # Ihr API-Key (aus dem Backend generiert)
    ApiKey = "IHREN_API_KEY_HIER_EINFUEGEN"
    
    # Standard-Geräte (können überschrieben werden)
    Devices = @{
        "Webserver" = "webserver_01"
        "Datenbank" = "database_01" 
        "Mailserver" = "mailserver_01"
        "Backup" = "backup_01"
    }
    
    # Standard-Notizen für verschiedene Status
    DefaultNotes = @{
        "gestoert" = "Automatischer Check fehlgeschlagen"
        "eingeschraenkt" = "Teilweise Performance-Probleme"
        "wartung" = "Geplante Wartungsarbeiten"
        "normal" = "System恢复正常"
    }
}

# Beispielaufrufe:
# .\Update-DeviceStatus.ps1 -ApiKey $Config.ApiKey -DeviceKey $Config.Devices.Webserver -Status "gestoert" -Note $Config.DefaultNotes.gestoert
# .\Update-DeviceStatus.ps1 -ApiKey $Config.ApiKey -DeviceKey $Config.Devices.Datenbank -Status "wartung" -UseJson