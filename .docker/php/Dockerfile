FROM devilbox/php-fpm:8.2-mods

# msmtp = cliente SMTP leve para rotear o mail() do PHP ao Mailhog (dev)
USER root
RUN apt-get update \
    && apt-get install -y --no-install-recommends msmtp msmtp-mta ca-certificates \
    && rm -rf /var/lib/apt/lists/*

COPY msmtprc /etc/msmtprc
RUN chmod 644 /etc/msmtprc
