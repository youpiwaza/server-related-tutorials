FROM williamyeh/ansible:alpine3

# RUN echo "===> Installing python, sudo, and supporting tools..."    &&  \
#     apt-get update                                                  &&  \
#     apt-get install -y                                              &&  \
#         vim                                                         &&  \
#         python                                                      &&  \
#         net-tools                                                   &&  \
#         curl

# Syntaxe alpine
RUN echo "===> Installing python, sudo, and supporting tools..."    &&  \
    apk update                                                      &&  \
    apk add                                                             \
        vim                                                             \
        python                                                          \
        net-tools                                                       \
        curl
