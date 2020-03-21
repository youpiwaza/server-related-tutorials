# Base : https://github.com/rastasheep/ubuntu-sshd/blob/master/18.04/Dockerfile
# Docker, running an SSH service / https://docs.docker.com/engine/examples/running_ssh_service/
FROM    ubuntu:18.04

RUN     echo "===> Installing python, sudo, and supporting tools..."                        &&   \
        apt-get update -y                                                                   &&   \
        apt-get install -y                                                                       \
            openssh-server                                                                       \
            vim                                                                                  \
            python

# Deuxième RUN, car la première opération est assez longue. Cela évite d'attendre à chaque modification de config
RUN     echo "===> Enable SSH..."                                                           &&   \
        mkdir /var/run/sshd                                                                 &&   \
        # Définitions de l'utilisateur et du password : root/ansible
        echo 'root:ansible' |chpasswd                                                       &&   \
        sed -ri 's/^#?PermitRootLogin\s+.*/PermitRootLogin yes/' /etc/ssh/sshd_config       &&   \
        sed -ri 's/UsePAM yes/#UsePAM yes/g' /etc/ssh/sshd_config                           &&   \
        mkdir /root/.ssh                                                                    &&   \
        apt-get clean                                                                       &&   \
        rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*                                       &&   \
# Recommandations Docker pour images avec SSH
        # SSH login fix. Otherwise user is kicked off after login
        sed 's@session\s*required\s*pam_loginuid.so@session optional pam_loginuid.so@g' -i /etc/pam.d/sshd

ENV NOTVISIBLE "in users profile"
RUN echo "export VISIBLE=now" >> /etc/profile

EXPOSE 22

CMD    ["/usr/sbin/sshd", "-D"]