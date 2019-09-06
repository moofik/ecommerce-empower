FROM node:alpine

RUN apk add --no-cache --virtual .persistent-deps \
        curl \
        openssl \
        make \
        gcc \
        g++ \
        python \
        py-pip \
    && npm install --silent --save-dev -g \
        typescript \
    && npm install -g ts-node

ADD startup.sh /

RUN sed -i 's/\r$//' /startup.sh  && \
        chmod +x /startup.sh

WORKDIR /src

EXPOSE 3335

ENV HOST 0.0.0.0

CMD ["npm", "run", "dev"]