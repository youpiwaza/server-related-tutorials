# tutum/helloworld custom user fix

Take the tutum/helloworld image from minimal usage, and test to make use on production.
> Make lots of tests to see at what point the problem is, cf. sub folders

Note: with custom user, etc/nginx/nginx.conf needs to be overloaded, cf [dockerhub nginx](https://hub.docker.com/_/nginx) > Running nginx as a non-root user

Problem was, etc/nginx/nginx.conf was already overloaded by tutum to enable php.

Solution was to merge both nginx.conf into a file allowing custom user AND php

Also linted the named volume stuff (for both nginx & phpfpm) logs.

Also another sh*t ton of problems (don't bind ports, publish nginx on 8080, don't mount /tmp/ as it cause replicas access FIFO conflicts, etc.)
