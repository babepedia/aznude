# Celebrity Data Scraper

A PHP-based web scraper designed to collect and store celebrity information from entertainment websites.

## Overview

This tool scrapes celebrity data including:
- Names and profile information
- Associated movies and media appearances
- Images and video references
- Ratings and other metadata

The collected data is stored in a MySQL database for further processing or display.

## Features

- Automated scraping of celebrity profiles
- Extraction of movie appearances with release years
- Collection of media references (images and videos)
- Database storage with proper escaping for UTF-8 characters

## Requirements

- PHP 7.0+
- MySQL/MariaDB
- PHP extensions:
  - mysqli
  - curl
  - mbstring
- Simple HTML DOM Parser library

## Configuration

Before running the script, you need to:

1. Create a `config.php` file with your database connection details:
```php
<?php
$mysqli = new mysqli('localhost', 'username', 'password', 'database');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
?>
```

2. Ensure the `functions.php` file includes the `func_get_content()` function for HTTP requests

3. Set up the required database tables:
   - `azcelebs`: Stores celebrity information
   - `azmovies`: Stores movie information

## Usage

Run the script through a web server or via command line:

```
php scraper.php
```

The script processes celebrities in batches, storing scraped data in the configured database.

## Notice

This tool is for educational purposes only. Be aware that web scraping may be against the terms of service of some websites. Always respect:

- Website robots.txt rules
- Rate limiting
- Copyright and legal restrictions regarding the content

## License

MIT
