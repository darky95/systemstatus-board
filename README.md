# Systemstatus IT

## Was ist das?
Ein modernes System zur Überwachung und Anzeige des Status Ihrer IT-Infrastruktur. Zeigen Sie den Zustand Ihrer Server, Dienste und Geräte übersichtlich auf Ihrer Website oder im internen Portal an.

## Hauptfunktionen im Überblick

**Für Administratoren:**
- Einfache Verwaltung aller überwachten Geräte
- Klare Status-Kategorien: Alles OK, Eingeschränkt, Wartung, Probleme
- Hierarchische Organisation (z.B. Server mit seinen Diensten)
- Mehrere Benutzer mit unterschiedlichen Rechten
- Externe Systeme können automatisch Status melden

**Für Besucher:**
- Aktuelle Statusinformationen in Echtzeit
- Klare farbliche Darstellung (Grün, Gelb, Blau, Rot)
- Übersichtliche Liste aller überwachten Systeme
- Automatische Aktualisierung alle 60 Sekunden
- Funktioniert auf allen Geräten (PC, Tablet, Smartphone)

**Für Entwickler:**
- Einfache API zur Integration in eigene Systeme
- Fertige Widgets zum Einbetten in andere Websites
- PowerShell-Skripte für Windows-Server
- Vollständige Dokumentation

## So installieren Sie das System

### Was Sie brauchen
- Einen Webserver (Apache, Nginx oder vergleichbar)
- PHP Version 7.4 oder neuer
- Einen Browser für die Administration

### Einfache Installation

1. **Dateien hochladen**
   Laden Sie alle Dateien auf Ihren Webserver hoch. Das System erstellt automatisch die benötigte Datenbank.

2. **Erster Start**
   - Öffnen Sie im Browser: `http://IHRE-DOMAIN.DE/` *(für Live-Betrieb)*
   - Oder lokal zum Testen: `http://localhost/status5/`
   - Melden Sie sich an mit:
     - Benutzername: `admin`
     - Passwort: `admin`
   - Ändern Sie das Passwort sofort!

Das war's schon! Das System ist jetzt einsatzbereit.



## Status-Levels im Überblick

🟢 **Normal** - Alles funktioniert einwandfrei
🟡 **Eingeschränkt** - Teilweise Einschränkungen
🔵 **Wartung** - Geplante Wartungsarbeiten
🔴 **Gestört** - Systemstörung

## Externe Systeme einbinden

Das System kann automatisch von Ihren Servern informiert werden:

**Per PowerShell-Skript:**
```powershell
.\Update-DeviceStatus.ps1 -ApiKey "IHRE_API_KEY" -DeviceKey "SERVER_NAME" -Status "gestoert" -Note "Festplatte voll"
```

**Per HTTP-Anfrage:**
Ihre Monitoring-Tools können einfach per HTTP POST den Status aktualisieren.

## Status auf Ihrer Website anzeigen

Sie können den Systemstatus einfach auf Ihrer eigenen Website einbetten:

**Als Widget (einfachste Methode):**
```html
<iframe src="https://IHRE-DOMAIN.DE/public/embed/status-summary.php" 
        width="300" height="80" frameborder="0">
</iframe>
```

**Verschiedene Anzeigeformen verfügbar:**
- Kompakte Zusammenfassung
- Detaillierte Liste
- Seitenleisten-Version
- Alles auf einmal oder nur kritische Meldungen

## Tipps für den Betrieb

✅ **Regelmäßig sichern**: Die Datei `database.db` enthält alle Ihre Daten
✅ **Passwörter ändern**: Sofort nach der Erstinstallation
✅ **Monitoring einrichten**: Automatische Status-Updates von Ihren Servern
✅ **HTTPS nutzen**: Im Live-Betrieb immer verschlüsselte Verbindungen

## Hilfe und Support

- Detaillierte technische Dokumentation finden Sie in den Unterordnern
- Bei Problemen: Prüfen Sie zuerst die Log-Dateien
- Neue Funktionen und Verbesserungen sind willkommen

## Tipps für den Betrieb

✅ **Regelmäßig sichern**: Die Datei `database.db` enthält alle Ihre Daten
✅ **Passwörter ändern**: Sofort nach der Erstinstallation
✅ **Monitoring einrichten**: Automatische Status-Updates von Ihren Servern
✅ **HTTPS nutzen**: Im Live-Betrieb immer verschlüsselte Verbindungen

## Hilfe und Support

- Detaillierte technische Dokumentation finden Sie in den Unterordnern
- Bei Problemen: Prüfen Sie zuerst die Log-Dateien
- Neue Funktionen und Verbesserungen sind willkommen

---
**Version**: 1.0 | **Letztes Update**: Januar 2026