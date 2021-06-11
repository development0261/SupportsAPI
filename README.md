# Clone repository to local

# Install package
composer update

# Database migration (Create new database with name supports_api)
php artisan migrate

# Set creadentials in .env file for mail
Update MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS variables .env file

# Run Project
php artisan serve
