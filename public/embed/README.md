# Embed-Verzeichnis für Statusübersichten

Dieses Verzeichnis enthält verschiedene einbettbare Darstellungsformen der Gerätestatus-Übersicht.

## Verfügbare Widgets:

### 1. Status Cards (`status-cards.php`)
- **Beschreibung**: Vertikale Karten mit allen Statusarten
- **Größe**: ~300px breit, variabel hoch
- **Verwendung**: Übersichtliche Darstellung aller Statuskategorien

### 2. Compact Sidebar (`status-sidebar.php`)
- **Beschreibung**: Platzsparende vertikale Liste
- **Größe**: ~120px breit, ~180px hoch
- **Verwendung**: Ideal für Sidebars oder schmale Bereiche

### 3. Status Summary (`status-summary.php`)
- **Beschreibung**: Dynamische Einzelanzeige (nur höchster Status)
- **Größe**: ~300px breit, ~80px hoch
- **Verwendung**: Kompakte Statuszusammenfassung

### 4. Detailed Status (`detailed-status.php`)
- **Beschreibung**: Detaillierte Ansicht mit konkreten Geräten
- **Größe**: ~350px breit, ~300px hoch
- **Verwendung**: Vollständige Informationen inkl. Notizen

## Einbettungsmethoden:

### 1. Iframe-Einbindung (Empfohlen)
```html
<!-- Status Cards -->
<!-- Produktion (Direkt unter Domain) -->
<iframe src="https://IHRE-DOMAIN.DE/public/embed/status-cards.php" 
        width="300" 
        height="200" 
        frameborder="0" 
        scrolling="no">
</iframe>

<!-- Entwicklung (im Unterverzeichnis) -->
<iframe src="http://localhost/status5/public/embed/status-cards.php" 
        width="300" 
        height="200" 
        frameborder="0" 
        scrolling="no">
</iframe>

<!-- Compact Sidebar -->
<!-- Produktion (Direkt unter Domain) -->
<iframe src="https://IHRE-DOMAIN.DE/public/embed/status-sidebar.php" 
        width="120" 
        height="180" 
        frameborder="0" 
        scrolling="no">
</iframe>

<!-- Entwicklung (im Unterverzeichnis) -->
<iframe src="http://localhost/status5/public/embed/status-sidebar.php" 
        width="120" 
        height="180" 
        frameborder="0" 
        scrolling="no">
</iframe>

<!-- Status Summary -->
<!-- Produktion (Direkt unter Domain) -->
<iframe src="https://IHRE-DOMAIN.DE/public/embed/status-summary.php" 
        width="300" 
        height="80" 
        frameborder="0" 
        scrolling="no">
</iframe>

<!-- Entwicklung (im Unterverzeichnis) -->
<iframe src="http://localhost/status5/public/embed/status-summary.php" 
        width="300" 
        height="80" 
        frameborder="0" 
        scrolling="no">
</iframe>

<!-- Detailed Status -->
<!-- Produktion (Direkt unter Domain) -->
<iframe src="https://IHRE-DOMAIN.DE/public/embed/detailed-status.php" 
        width="350" 
        height="300" 
        frameborder="0" 
        scrolling="no">
</iframe>

<!-- Entwicklung (im Unterverzeichnis) -->
<iframe src="http://localhost/status5/public/embed/detailed-status.php" 
        width="350" 
        height="300" 
        frameborder="0" 
        scrolling="no">
</iframe>
```

### 2. JavaScript Widget
```html
<div id="status-widget"></div>

<script>
// Dynamisches Laden eines Widgets

// Produktion (Direkt unter Domain)
fetch('https://IHRE-DOMAIN.DE/public/embed/status-summary.php')
    .then(response => response.text())
    .then(html => {
        document.getElementById('status-widget').innerHTML = html;
    })
    .catch(error => console.error('Fehler:', error));

// Entwicklung (im Unterverzeichnis)
// fetch('http://localhost/status5/public/embed/status-summary.php')
//     .then(response => response.text())
//     .then(html => {
//         document.getElementById('status-widget').innerHTML = html;
//     })
//     .catch(error => console.error('Fehler:', error));
</script>
```

## Wichtige Hinweise:

1. **Domain anpassen**: Ersetzen Sie `http://IHRE-DOMAIN` mit Ihrer echten URL
2. **HTTPS**: Bei HTTPS-Seiten müssen auch die Widgets HTTPS-URLs verwenden
3. **Größenanpassung**: Passen Sie width/height entsprechend Ihren Layout-Anforderungen an
4. **Responsive Design**: Alle Widgets sind responsiv und passen sich Container-Größen an
5. **Automatische Updates**: Widgets laden alle 60 Sekunden neu (wie die Hauptseite)

## Best Practices:

- Verwenden Sie Iframes für maximale Kompatibilität
- Passen Sie die Container-Größe an das jeweilige Widget an
- Testen Sie die Darstellung auf verschiedenen Bildschirmgrößen
- Beachten Sie CORS-Einschränkungen bei JavaScript-Einbindung