FROM masterhome/php-fpm

RUN apt-get update \
    && apt-get install -y ssh \
    && useradd -ms /bin/bash masterhome \
    && echo "masterhome:masterhome" | chpasswd \
    && mkdir -p /var/run/sshd \
    && chown -R masterhome:masterhome /opt \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /tmp/* \
    && echo Done!

EXPOSE 9000 22

CMD ["/usr/sbin/sshd", "-D"]
