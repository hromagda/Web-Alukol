# Alukol.cz

Oficiální webové stránky firmy Alukol – specializace na posuvné hliníkové zasklení a zábradlí. Moderní, responzivní a přehledné stránky vytvořené pomocí vlastní MVC architektury mimo Laravel.

---

## ✨ O projektu

Web je postavený s cílem představit portfolio realizací, informovat o službách a produktech firmy Alukol a zprostředkovat kontaktní formulář pro poptávky.

**Klíčové vlastnosti:**

- Vlastní MVC architektura pro jednoduchou správu a rozšiřitelnost
- Mobile-first přístup a responzivní design
- Moderní, čistý a uživatelsky přívětivý frontend
- Administrace obsahu s rozdělením na role (admin, editor)
- Dynamická galerie realizací s lazy loadingem a AJAXem
- Bezpečný kontaktní formulář s validací a odesíláním e-mailů přes PHPMailer
- SEO optimalizace základních stránek

---

## ⚙️ Technologie

| Kategorie           | Použité nástroje                               |
|---------------------|------------------------------------------------|
| Backend             | PHP (vlastní MVC framework)                    |
| Frontend            | HTML5, CSS3 (mobile-first), JavaScript, AJAX  |
| Šablonování         | PHP šablony (`.phtml`)                         |
| Stylování           | Vlastní CSS bez frameworku                     |
| E-mail odesílání    | PHPMailer                                      |
| Databáze            | MySQL                                          |
| Validace            | Vlastní PHP validace formulářů                |
| Autentifikace       | Role admina pomocí PHP session                |

---

## 📁 Struktura projektu

/public - veřejné soubory (CSS, JS, obrázky)
/app - kontrolery, modely, jádro MVC (Router.php)
/views - šablony stránek (.phtml)
/config - konfigurační soubory
/vendor - externí knihovny (např. PHPMailer)

yaml
Zkopírovat
Upravit

---

## 🎨 Styl a UX

- Mobile-first design s responzivním rozvržením
- Barevná paleta odpovídající vizuální identitě Alukolu
- Přehledné menu s aktivním zvýrazněním aktuální stránky
- Galerie optimalizovaná pro rychlé načítání
- Kontaktní formulář s ochranou proti spamu (honeypot, CSRF)

---

## 🛠️ Nasazení na produkci

Pro úspěšné nasazení aplikace:

1. Přenastav připojení k databázi (`/config/database.php`).
2. Importuj produkční dump databáze.
3. Nakonfiguruj SMTP přístup pro e-mailové odesílání (/config/mail.php, PHPMailer).
4. V `index.php` vypni výpis chyb a povol logování.
5. Ujisti se, že `DocumentRoot` směřuje do `/public`.
6. Zkontroluj `.htaccess` a přesměrování.
7. Odstraň vývojové/testovací skripty, skryj citlivé soubory (např. `.env`, `README.md`).
8. Nahraj soubory přes Git nebo SFTP a nastav správná oprávnění (`uploads/`, `storage/`).
9. Otestuj funkčnost, formuláře a vzhled na produkční doméně.

---

## 🚀 Plánovaný rozvoj

- Rozšíření administrace o správu článků a realizací
- Možnosti pro SEO a návštěvnostní analytiku
- Uživatelské recenze a reference
- Vylepšení UX pomocí animací a interaktivních prvků
