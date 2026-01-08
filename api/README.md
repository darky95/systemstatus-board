# API-Dokumentation - Gerätestatus Management

## Übersicht
Die API ermöglicht die externe Aktualisierung von Gerätestatus über HTTP-Anfragen mit API-Key Authentifizierung.

## Endpunkt
```
POST /api/status/update.php
```

## Authentifizierung
Alle Anfragen benötigen einen gültigen API-Key im Header oder Body.

### API-Key erstellen
1. Im Backend anmelden
2. Navigieren zu "API-Keys" 
3. Neuen API-Key generieren
4. Key sicher speichern (wird nur einmal angezeigt!)

## Anfrage-Format

### Content-Type: application/x-www-form-urlencoded
```bash
# Produktion (Direkt unter Domain)
curl -X POST "https://IHRE-DOMAIN.DE/api/status/update.php" \
  -d "api_key=IHRE_API_KEY" \
  -d "device_key=GERAETE_KEY" \
  -d "status=normal" \
  -d "note=Optionale Notiz"

# Entwicklung (lokal im Unterverzeichnis)
curl -X POST "http://localhost/status5/api/status/update.php" \
  -d "api_key=IHRE_API_KEY" \
  -d "device_key=GERAETE_KEY" \
  -d "status=normal" \
  -d "note=Optionale Notiz"
```

### Content-Type: application/json
```bash
# Produktion (Direkt unter Domain)
curl -X POST "https://IHRE-DOMAIN.DE/api/status/update.php" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "IHRE_API_KEY",
    "device_key": "GERAETE_KEY",
    "status": "gestoert",
    "note": "Serverausfall erkannt"
  }'

# Entwicklung (lokal im Unterverzeichnis)
curl -X POST "http://localhost/status5/api/status/update.php" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "IHRE_API_KEY",
    "device_key": "GERAETE_KEY",
    "status": "gestoert",
    "note": "Serverausfall erkannt"
  }'
```

## Parameter

| Parameter | Erforderlich | Typ | Beschreibung |
|-----------|-------------|-----|--------------|
| `api_key` | Ja | String | Gültiger API-Key |
| `device_key` | Ja | String | Eindeutiger Geräte-Key |
| `status` | Ja | String | Statuswert |
| `note` | Nein | String | Optionale Notiz |

## Gültige Status-Werte
- `normal` - Alles funktioniert ordnungsgemäß
- `eingeschraenkt` - Teilweise Einschränkungen
- `wartung` - Geplante Wartungsarbeiten
- `gestoert` - Systemstörung

## Antwort-Format

### Erfolgreiche Anfrage
```json
{
  "success": true,
  "message": "Status erfolgreich aktualisiert",
  "device": {
    "id": 123,
    "name": "Webserver01",
    "status": "gestoert"
  }
}
```

### Fehlerantwort
```json
{
  "success": false,
  "message": "Ungültiger oder fehlender API-Key"
}
```

## Fehlermeldungen
- `Nur POST-Requests erlaubt` - Falsche HTTP-Methode
- `Ungültiger oder fehlender API-Key` - API-Key fehlt oder ungültig
- `device_key ist erforderlich` - Geräte-Key fehlt
- `Ungültiger Status` - Statuswert nicht erlaubt
- `Gerät nicht gefunden` - device_key existiert nicht

## Beispiel-Integrationen

### Shell/Bash Script
```bash
#!/bin/bash
API_KEY="ihre_api_key_hier"
DEVICE_KEY="webserver_01"
STATUS="gestoert"
NOTE="Automatischer Check fehlgeschlagen"

# Produktion (Direkt unter Domain)
curl -X POST "https://IHRE-DOMAIN.DE/api/status/update.php" \
  -d "api_key=$API_KEY" \
  -d "device_key=$DEVICE_KEY" \
  -d "status=$STATUS" \
  -d "note=$NOTE"

# Entwicklung (lokal im Unterverzeichnis)
curl -X POST "http://localhost/status5/api/status/update.php" \
  -d "api_key=$API_KEY" \
  -d "device_key=$DEVICE_KEY" \
  -d "status=$STATUS" \
  -d "note=$NOTE"
```

### Python
```python
import requests

# Produktion (Direkt unter Domain)
url = "https://IHRE-DOMAIN.DE/api/status/update.php"

# Entwicklung (lokal im Unterverzeichnis)
# url = "http://localhost/status5/api/status/update.php"

data = {
    "api_key": "ihre_api_key",
    "device_key": "datenbank_server",
    "status": "eingeschraenkt",
    "note": "Langsame Antwortzeiten"
}

response = requests.post(url, data=data)
result = response.json()
print(result)
```

### PHP
```php
<?php
// Produktion (Direkt unter Domain)
$url = "https://IHRE-DOMAIN.DE/api/status/update.php";

// Entwicklung (lokal im Unterverzeichnis)
// $url = "http://localhost/status5/api/status/update.php";

$data = [
    "api_key" => "ihre_api_key",
    "device_key" => "mail_server",
    "status" => "wartung",
    "note" => "Geplante Wartung"
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];

$result = file_get_contents($url, false, stream_context_create($options));
$response = json_decode($result, true);
print_r($response);
?>
```

### PowerShell
```powershell
# Variablen definieren
+# Produktion (Direkt unter Domain)
+$ApiUrl = "https://IHRE-DOMAIN.DE/api/status/update.php"
+
+# Entwicklung (lokal im Unterverzeichnis)
+# $ApiUrl = "http://localhost/status5/api/status/update.php"
$ApiKey = "ihre_api_key"
$DeviceKey = "webserver_01"
$Status = "gestoert"
$Note = "Automatischer Health-Check fehlgeschlagen"

# Daten als Hashtable erstellen
$Body = @{
    api_key = $ApiKey
    device_key = $DeviceKey
    status = $Status
    note = $Note
}

# POST-Anfrage senden
try {
    $Response = Invoke-RestMethod -Uri $ApiUrl -Method Post -Body $Body -ErrorAction Stop
    
    if ($Response.success) {
        Write-Host "Status erfolgreich aktualisiert!" -ForegroundColor Green
        Write-Host "Gerät: $($Response.device.name)" -ForegroundColor Cyan
        Write-Host "Neuer Status: $($Response.device.status)" -ForegroundColor Yellow
    } else {
        Write-Host "Fehler: $($Response.message)" -ForegroundColor Red
    }
} catch {
    Write-Host "Anfrage fehlgeschlagen: $($_.Exception.Message)" -ForegroundColor Red
}

# Alternativ mit JSON
$JsonBody = @{
    api_key = $ApiKey
    device_key = $DeviceKey
    status = "eingeschraenkt"
    note = "Performance-Probleme erkannt"
} | ConvertTo-Json

$Headers = @{
    "Content-Type" = "application/json"
}

$Response = Invoke-RestMethod -Uri $ApiUrl -Method Post -Body $JsonBody -Headers $Headers
```

## Best Practices
1. **Sichere Speicherung**: API-Keys niemals im Quellcode speichern
2. **HTTPS verwenden**: Immer verschlüsselte Verbindungen nutzen
3. **Rate Limiting**: Anfragen nicht zu häufig senden
4. **Fehlerhandling**: Immer auf Fehler reagieren
5. **Logging**: Anfragen und Antworten loggen für Debugging

## Sicherheitshinweise
- API-Keys sind sensibel und sollten geschützt werden
- Keys können jederzeit deaktiviert oder gelöscht werden
- Bei Verdacht auf Kompromittierung Keys sofort austauschen
- Zugriffe werden protokolliert

## Support
Bei Fragen zur API Nutzung wenden Sie sich an den Systemadministrator.