# Clone repository to local then run below commands one by one with instruction.

# Install package
composer install

# Database migration (Create new database with name supports_api)
php artisan migrate

# Set creadentials in .env file for mail SMTP (Gmail)
Update MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS variables .env file

# Run Project
php artisan serve

Please Refer the doc file that exist in root folder (doc file.txt).
