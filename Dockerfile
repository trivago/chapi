FROM php:7.1-cli

LABEL maintainer "Marc Siebeneicher <marc.siebeneicher@trivago.com>"
LABEL version="dev-master"
LABEL description="chronos & marathon console client - Manage your jobs like a git repository"

# Install depencies
RUN apt-get update \
    && apt-get install --no-install-recommends -y git zip unzip \
    && rm -rf /var/lib/apt/lists/*

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --filename=composer --install-dir=/usr/local/bin \
    && php -r "unlink('composer-setup.php');"

# Copy code
COPY . /chapi

# create symlink
RUN ln -s /chapi/bin/chapi /usr/local/bin/chapi

# Install chapi
WORKDIR /chapi
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configuration
RUN mkdir /root/.chapi

# Set ENTRYPOINT
ENTRYPOINT ["bin/chapi"]