FROM php:8.2-apache

# 1. Enable Apache mod_rewrite (often needed for PHP apps)
RUN a2enmod rewrite

# 2. Set the Working Directory
WORKDIR /var/www/html

# 3. Copy all your project files
COPY . /var/www/html/

# 4. Set DirectoryIndex to your specific login path
# Note: Ensure the casing matches your folders exactly (e.g., Templates vs templates)
RUN echo "DirectoryIndex index.php" >> /etc/apache2/apache2.conf

# 5. Fix permissions so Apache can read the files
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80