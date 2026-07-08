FROM nginx:1.27-alpine

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY public /var/www/html/public

RUN mkdir -p /var/www/html/storage/app \
    && ln -s /var/www/html/storage/app/public /var/www/html/public/storage
