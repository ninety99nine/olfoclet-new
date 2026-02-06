FROM node:18-alpine

WORKDIR /app

# Install dependencies
# 'express' for the server
# 'vm2' for the code isolation (prevents infinite loops/crashing the main process)
RUN npm init -y && \
    npm install express vm2

COPY server.js .

# Run as non-root user for security
USER node

EXPOSE 3000

CMD ["node", "server.js"]
