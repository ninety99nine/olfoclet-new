FROM python:3.10-alpine

WORKDIR /app

# Install Flask and Gunicorn
RUN pip install --no-cache-dir flask gunicorn

COPY server.py .

# Create a non-root user
RUN adduser -D sandboxuser
USER sandboxuser

EXPOSE 5000

# Run with Gunicorn for better performance than default Flask server
CMD ["gunicorn", "--bind", "0.0.0.0:5000", "server:app"]
