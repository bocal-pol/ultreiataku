FROM node:20-alpine
WORKDIR /app

COPY package*.json ./
RUN npm ci --ignore-scripts || npm install --ignore-scripts

COPY . .
RUN chown -R 1000:1000 /app
USER 1000

EXPOSE 5181

ENV API_TARGET=http://localhost:80

CMD ["npm", "run", "dev", "--", "--host"]
