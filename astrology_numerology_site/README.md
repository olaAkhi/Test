# Astrology & Numerology Services Website

This project is a website offering various astrology and numerology services. Users can register, purchase services using an account balance, view their purchased reports, and receive daily forecasts.

## Project Structure

- `public/`: Web server document root. Contains `index.php` (router), CSS, JS, and images.
- `src/`: PHP backend code.
    - `includes/`: Reusable PHP components (header, footer).
    - `core/`: Core application logic (database, sessions).
    - `models/`: Data models.
    - `controllers/`: Request handlers.
    - `services/`: Astrology/Numerology calculation logic.
- `templates/`: HTML page templates (PHP files).
    - `pages/`: Main page content.
    - `layouts/`: Base HTML structure.
- `config/`: Configuration files.

## Setup (Conceptual - for local development with PHP server)

1.  Ensure you have PHP installed.
2.  Navigate to the `public/` directory.
3.  Start the PHP built-in web server: `php -S localhost:8000`
4.  Open `http://localhost:8000` in your browser.

## Technologies Used

-   **Frontend:** HTML, CSS, Bootstrap 5, JavaScript
-   **Backend:** PHP

## Features (Planned)

-   User registration and login
-   Detailed astrology and numerology service listings
-   User dashboard with account balance and purchased services
-   Service purchase via account balance deduction
-   Generation of astrology/numerology reports
-   Daily email forecasts
-   Admin panel (future)
-   Payment gateway integration for adding funds (future)Tool output for `create_file_with_block`:
