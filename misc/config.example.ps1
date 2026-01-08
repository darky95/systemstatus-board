# Beispiel-Konfiguration für Update-DeviceStatus.ps1
# Kopieren Sie diese Datei und passen Sie die Werte an

# API-Konfiguration
$Config = @{
    # API-Endpunkt (passen Sie die Domain an)
    # Produktion (Direkt unter Domain):
    ApiUrl = "https://IHRE-DOMAIN.DE/api/status/update.php"
    
    # Entwicklung (im Unterverzeichnis):
    # ApiUrl = "http://localhost/status5/api/status/update.php"
    
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
# Produktion (Direkt unter Domain):
# .\Update-DeviceStatus.ps1 -ApiKey $Config.ApiKey -DeviceKey $Config.Devices.Webserver -Status "gestoert" -Note $Config.DefaultNotes.gestoert
# .\Update-DeviceStatus.ps1 -ApiKey $Config.ApiKey -DeviceKey $Config.Devices.Datenbank -Status "wartung" -UseJson

# Entwicklung (im Unterverzeichnis):
# .\Update-DeviceStatus.ps1 -ApiUrl "http://localhost/status5/api/status/update.php" -ApiKey "TEST_KEY" -DeviceKey "test_device" -Status "normal"