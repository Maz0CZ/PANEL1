# UltimatePanel v1.0.1 — Dokumentace (CZ)

**Hlavní vývojář:** Filip Piller  
**Cílový systém:** Debian Linux  
**Framework:** Nette (latest stable)  
**DB:** SQLite (file-based)  
**Frontend:** Bootstrap 5 + FontAwesome  

## 1) Rychlé spuštění (out of the box)
```bash
./quickstart.sh
```
- Spustí lokální server na `http://localhost:8000`.
- Vytvoří SQLite DB a naplní šablony her + výchozí účty.

Výchozí účty:
- **Admin**: `admin@admin.cz` / `pass123`
- **User**: `test@test.cz` / `test123`

## 2) Instalace krok za krokem
1. **Aktualizace systému**
   - `sudo apt update && sudo apt upgrade -y`
2. **Instalace závislostí**
   - PHP (např. 8.2), Composer, sqlite3, screen/tmux, webserver (Nginx/Apache)
3. **Stažení aplikace**
   - `git clone <repo>`
4. **Composer instalace**
   - `composer install`
5. **Inicializace SQLite**
   - `php bin/init_db.php storage/db/panel.sqlite`
6. **Spuštění aplikace**
   - `php -S 0.0.0.0:8000 -t public`

## 3) Registrace a email
- Registrace používá jednoduchý `Nette\Mail` mailer.
- Odesílá uvítací email na zadanou adresu.

## 4) Konfigurace serveru
- Každý server běží pod vlastním Linux uživatelem/skupinou (doporučeno pro produkci).
- Porty validovat proti konfliktům.
- Adresář serveru je izolovaný a chráněný.

## 5) Přidání her
- Hry se přidávají přes **Game Templates** v DB.
- Každá šablona má:
  - startovací příkaz
  - port range
  - popis
  - strukturu adresářů
  - konfigurační soubory

## 6) Bezpečnostní doporučení
- Striktní validace vstupů
- CSRF ochrana (Nette)
- XSS ochrana + CSP
- Hashování hesel `password_hash()`
- Prepared statements v celé aplikaci
- Rate limiting přihlášení
- Hardened sessions
- Žádné root procesy pro hry

## 7) Struktura projektu
```
/app
/config
/templates
/public
/storage
/bin
/docs
```

## 8) Deployment
- Instalace závislostí
- Konfigurace webserveru
- SQLite + seed
- Konfigurace FTP
- Sudo pravidla
- Firewall

## 9) Aktualizace systému
1. `git pull`
2. `composer install --no-dev`
3. Spustit migrační skripty
4. Restart PHP-FPM/webserveru

## 10) Řešení problémů
- **Nejde spustit server:** ověřit port, práva, a dostupnost binárky
- **Nejde FTP:** zkontrolovat chroot a konfiguraci vsftpd/proftpd
- **Prázdná konzole:** ověřit logovací soubory a screen/tmux session

## 11) Doporučené zásady provozu
- Pravidelné zálohy SQLite a konfigurace
- Logování administrátorských akcí
- Omezit přístup k admin účtu (IP whitelist)
