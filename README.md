# Rift Backend

This is the backend service for Rift, a ninja-themed RPG game. It is built using Laravel and SabreAMF to provide an offline-capable experience for the game client.

## Features

- **AMF Integration**: Seamless communication with the ActionScript-based game client using SabreAMF.
- **Character Management**: Logic for character stats, leveling (with rank-based caps), and skill acquisition.
- **Event Systems**: Backend support for seasonal events like Valentine's 2026 and Hanami 2026.
- **Black Merchant**: A rotating skill package shop with randomized packages and support for special discounts for Diamond/Emblem users.
- **XP Ecosystem**: A centralized `LevelManager` handling XP gains, triple XP rewards for premium users, and level thresholds.
- **Battle & Missions**: Services for handling mission completions, rewards, and battle logic.

## Technology Stack

- **Framework**: Laravel 11.x
- **AMF Server**: SabreAMF
- **Database**: MySQL (MariaDB)
- **Language**: PHP 8.2+

## Setup Instructions

1. **Environment Setup**:
   - Copy `.env.example` to `.env` and configure your database settings.
   - Run `composer install`.
   - Run `php artisan key:generate`.

2. **Database Migrations**:
   - Run `php artisan migrate`.
   - Seed the initial data (including the Black Merchant packages):
     ```bash
     php artisan db:seed --class=BlackMerchantSeeder
     ```

3. **Running the Server**:
   - The backend listens for AMF requests at the `/amf` endpoint.
   - Use `php artisan serve` for local development.

## Project Structure

- `app/Services/`: Core business logic for game systems (Merchant, Events, Character, etc.).
- `app/Traits/`: Reusable logic like `LevelManager` and `SessionValidator`.
- `app/Http/Controllers/`: AMF gateway implementation.
- `database/migrations/`: Database schema definitions.

## License

Private / Confidential.
