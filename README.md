# Scrolls Academy website

## Installation

### Prerequisites

- Apache >= 2.2.20
  - mod_headers enabled
  - mod_expires enabled
  - mod_rewrite enabled
- PHP >= 5.3.6 with PDO
- MySQL >= 14.14

### Configuration

Change `config/config.ini` to use your database settings.
Run `install/db.sql` with your SQL installation to configure your database.

`cache` and `public_html/assets/cache` should be writeable.
