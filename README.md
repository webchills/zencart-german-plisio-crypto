# zencart-german-plisio-crypto
Plisio Zahlungsmodul für Zen Cart 1.5.7g/1.5.7h deutsch 
* Plisio ist ein Payment Gateway für Bitcoin, Litecoin, Ethereum und 30 andere Kryptowährungen. Mit der Plisio API können Sie in Ihrem deutschen Zen Cart Onlineshop Kryptowährungen akzeptieren.

## Hinweis: 
Freigegebene getestete Versionen für den Einsatz in Livesystemen ausschließlich unter Releases herunterladen:
* https://github.com/webchills/zencart-german-plisio-crypto/releases

## Sinn und Zweck:
* Mit diesem Modul wird Zahlung mit Kryptowährungen wie Bitcoin, Litecoin, Ethereum, Bitcoin Cash, Monero und anderen Altcoins via Plisio im Shop integriert.

## Voraussetzungen:
* Freigeschalteter Plisio Account (Registrierung auf https://plisio.net)
* Zen Cart 1.5.7g deutsche Version oder 1.5.7h deutsche Version
* Shop verwendet durchgehend https
* PHP mindestens 8.0.x, empfohlen 8.1.x oder 8.2.x (mit 1.5.7h)

## Features:
* Automatische Zahlungsbestätigungen und Aktualisierungen des Bestellstatus.
* Legen Sie Ihre Preise in jeder lokalen Währung fest, und der Zahlungsbetrag in Kryptowährung wird anhand von Echtzeit-Wechselkursen berechnet.
* Keine Einrichtungsgebühren oder wiederkehrende Gebühren.
* Keine Rückbuchungen

## Funktionalität:
* Verlängern Sie die Verfallszeit von Rechnungen auf bis zu 72 Stunden.
* Geringfügige Unterzahlungen werden automatisch akzeptiert.
* Rückerstattungen können direkt von der Rechnung und ohne Beteiligung des Verkäufers ausgestellt werden.

## So funktioniert es - ein Beispiel:
1. Ein Artikel im Shop kostet 100 Euro.
2. Ein Kunde möchte den Artikel kaufen und entscheidet sich für die Zahlung mit Bitcoin.
3. Es wird eine Rechnung erstellt, und nach dem aktuellen Wechselkurs beträgt der Preis 10000 Euro pro Bitcoin, also muss der Kunde 0,01 Bitcoins bezahlen.
4. Sobald die Rechnung bezahlt ist, erhält der Händler 99,5 Euro (100 Euro abzüglich der 0,5%igen Pliso Pauschalgebühr), oder 0,0099 BTC.

## Credits:
* Dieses Modul basiert auf dem Zen Cart Plugin von Plisio (www.plisio.net)
* Grundlage war das entsprechende Github Repository:
* https://github.com/Plisio/zencart-plugin
