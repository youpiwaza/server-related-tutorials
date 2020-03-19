FROM williamyeh/ansible:debian9

RUN apt-get update && apt-get install -y vim python net-tools telnet curl