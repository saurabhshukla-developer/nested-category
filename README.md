# Category Management System

A Laravel-based category management system that handles hierarchical parent-child relationships using a single table structure.

## Features

- Hierarchical category management with parent-child relationships
- Category listing with full path display
- Add/Edit categories with parent selection
- Delete categories with automatic child reassignment
- Pagination support
- Input validation and error handling

## Requirements

- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer
- Node.js and NPM (for frontend assets)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd test-category-management
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install frontend dependencies:
```bash
npm install
```

4. Create environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your database in `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=test_category_management
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

7. Run migrations:
```bash
php artisan migrate
```

8. Build frontend assets:
```bash
npm run build
```

9. Start the development server:
```bash
php artisan serve
```

## Configuration Details

### Database Configuration
The system uses MySQL as the database. Make sure to:
- Create a database named `test_category_management`
- Set proper database credentials in `.env` file
- Run migrations to create the required tables

### Frontend Configuration
The system uses:
- Bootstrap 5 for styling

### Application Configuration
Key configuration files:
- `config/app.php` - Application settings
- `config/database.php` - Database settings

## Interface Screenshots

### Category Listing
![Category Listing](docs/screenshots/category-listing.png)
*Main category listing page showing the grid with all categories*

### Add Category Modal
![Add Category](docs/screenshots/add-category.png)
*Modal for adding new categories with parent selection*

### Edit Category Modal
![Edit Category](docs/screenshots/edit-category.png)
*Modal for editing existing categories*

### Delete Confirmation
![Delete Category](docs/screenshots/delete-category.png)
*Confirmation modal for deleting categories*

## Database Structure

### Categories Table
- `id` - Primary key
- `name` - Category name
- `status` - Category status (1: Enabled, 2: Disabled)
- `parent_id` - Foreign key to parent category
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

## Features in Detail

### Category Listing
- Displays categories in a paginated grid
- Shows full category path (e.g., "Bedroom > Beds > Panel Bed")
- Status indicators with color-coded badges
- Parent category references
- Creation and update timestamps

### Category Management
- Add new categories with parent selection
- Edit existing categories
- Delete categories with automatic child reassignment
- Parent category dropdown shows full hierarchy paths
- Prevents self-reference and circular references

### Validation Rules
- Required fields: name, status
- Unique category names under the same parent
- Valid parent category reference
- Maximum name length: 255 characters
- Valid status values: 1 (Enabled) or 2 (Disabled)

## Testing

Run the test suite:
```bash
php artisan test
```

The test suite includes:
- Feature tests for category management
- UI tests for category interface
- Unit tests for category model

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
