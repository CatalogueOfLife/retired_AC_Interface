SetEnv APPLICATION_ENV @ENVIRONMENT@
php_value session.auto_start 0

RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} -s [OR]
RewriteCond %{REQUEST_FILENAME} -l [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^.*$ - [NC,L]
RewriteRule !\.(js|ico|gif|jpg|png|css)$ index.php [NC,L]