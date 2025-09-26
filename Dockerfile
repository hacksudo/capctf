# Dockerfile - vuln lab (php:8.4-fpm + nginx + sqlite3)
FROM php:8.4-fpm

ENV DEBIAN_FRONTEND=noninteractive

# Install packages
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    nginx \
    sqlite3 \
    libsqlite3-dev \
    iputils-ping \
    curl ca-certificates \
 && rm -rf /var/lib/apt/lists/*

# Enable pdo_sqlite extension
RUN docker-php-ext-install pdo pdo_sqlite

# Create webroot
RUN mkdir -p /var/www/html
WORKDIR /var/www/html

# Copy app files
COPY init_db.php ./init_db.php
COPY index.php ./index.php
COPY dashboard.php ./dashboard.php
COPY dict.txt ./dict.txt
COPY . /var/www/html

# Initialize DB at build time
RUN php /var/www/html/init_db.php \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

# Remove default site to avoid duplicate default_server errors, then add our config
RUN rm -f /etc/nginx/sites-enabled/default || true
COPY nginx.conf /etc/nginx/sites-available/vulnlab.conf
RUN ln -sf /etc/nginx/sites-available/vulnlab.conf /etc/nginx/sites-enabled/vulnlab.conf

# Copy entrypoint and make executable
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
# SUID Privilege
RUN cp /bin/bash /var/www/html/.honeypot
RUN chown root:root /var/www/html/.honeypot
RUN chmod u+s /var/www/html/.honeypot
#root flag
RUN echo "Congratulations You have successfully Compromised this lab!!!" > /root/root.txt

#Run web service
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["nginx", "-g", "daemon off;"]
