FROM php:8.2-cli-alpine

WORKDIR /app

# Configure PHP for safety (optional but recommended)
# We disable dangerous functions that could kill the container itself
RUN echo "disable_functions = exec,passthru,shell_exec,system,proc_open,popen" > /usr/local/etc/php/conf.d/disable_funcs.ini

COPY server.php .

# Run as non-root user
RUN adduser -D sandboxuser
USER sandboxuser

EXPOSE 8000

# Use PHP's built-in web server for the microservice
CMD ["php", "-S", "0.0.0.0:8000", "server.php"]
