# Sense7 Block Theme

Nowoczesny motyw WordPress oparty o Full Site Editing (FSE) dla strony Sense7.

## Wymagania

- WordPress 6.0+
- PHP 8.0+

## Funkcjonalności

### Block Theme (FSE)
Motyw wykorzystuje nową architekturę Block Theme z Full Site Editing:
- **theme.json** - centralna konfiguracja stylów, kolorów, typografii
- **templates/** - szablony HTML oparte o bloki
- **parts/** - części szablonów (header, footer)

### Szablony

Motyw zawiera następujące szablony:
- `index.html` - główny szablon
- `home.html` - strona główna bloga
- `single.html` - pojedynczy wpis
- `page.html` - strona
- `archive.html` - archiwum (kategorie, tagi)
- `search.html` - wyniki wyszukiwania
- `404.html` - błąd 404

### Części szablonów (Template Parts)

- `header.html` - nagłówek z logo i nawigacją
- `footer.html` - stopka z kolumnami i social media

## Konfiguracja theme.json

Plik `theme.json` definiuje:

### Kolory
- Primary: `#007bff`
- Secondary: `#6c757d`
- Foreground: `#333333`
- Background: `#ffffff`
- Accent: `#28a745`
- Light Gray: `#f8f9fa`
- Dark Gray: `#343a40`

### Rozmiary czcionek
- Small: `0.875rem`
- Medium: `1rem`
- Large: `1.25rem`
- X-Large: `1.5rem`
- XX-Large: `2rem`

### Spacing
Predefiniowane odstępy od 30 (0.5rem) do 80 (4rem)

### Layout
- Content Size: `800px`
- Wide Size: `1200px`

## Struktura plików

```
sense7/
├── assets/
│   ├── css/
│   │   ├── main.css          # Dodatkowe style
│   │   └── editor-style.css  # Style dla edytora bloków
│   └── js/
│       └── main.js            # Główny JavaScript
├── parts/
│   ├── header.html            # Nagłówek
│   └── footer.html            # Stopka
├── templates/
│   ├── index.html             # Główny szablon
│   ├── home.html              # Blog
│   ├── single.html            # Pojedynczy wpis
│   ├── page.html              # Strona
│   ├── archive.html           # Archiwum
│   ├── search.html            # Wyszukiwanie
│   └── 404.html               # Błąd 404
├── functions.php              # Funkcje motywu
├── style.css                  # Style CSS (wymagane)
├── theme.json                 # Konfiguracja Block Theme
└── README.md                  # Dokumentacja

```

## Namespace

Wszystkie klasy i funkcje używają namespace'u `Sense7\Theme\`

## Rozbudowa

### Dodawanie własnych bloków

Utwórz klasę w katalogu `includes/Blocks/`:

```php
<?php
namespace Sense7\Theme\Blocks;

class CustomBlock {
    public function __construct() {
        add_action('init', [$this, 'register']);
    }

    public function register(): void {
        // Rejestracja bloku
    }
}
```

Następnie zainicjalizuj w `functions.php`:

```php
private function initializeComponents(): void {
    if (class_exists('Sense7\Theme\Blocks\CustomBlock')) {
        new Blocks\CustomBlock();
    }
}
```

### Dodawanie własnych pattern'ów

Utwórz katalog `patterns/` i dodaj pliki PHP z pattern'ami:

```php
<?php
/**
 * Title: Custom Pattern
 * Slug: sense7/custom-pattern
 * Categories: featured
 */
?>
<!-- wp:paragraph -->
<p>Your pattern content here</p>
<!-- /wp:paragraph -->
```

## Wsparcie

Dla pytań i wsparcia, odwiedź [sense7.pl](https://sense7.pl)

## Licencja

Proprietary - Wszystkie prawa zastrzeżone
