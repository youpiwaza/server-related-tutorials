# Docker docs

Get started > Quickstart > [Part 1: Orientation and setup](https://docs.docker.com/get-started/)

I used the windows version

---

1. Installed docker desktop
2. Enabled Kubernetes (in settings)


## Test kubernetes

1. Création du fichier pod.yaml
2. L'ensemble des commandes tournent bien, même si cela met un peu de temps (~5 secondes pour certaines)


## Test docker swarm

> docker swarm leave

Error response from daemon: You are attempting to leave the swarm on a node that is participating as a manager. Removing the last manager erases all current state of the swarm. Use `--force` to ignore this message.

> docker swarm leave --force

Node left the swarm.

> docker swarm init

Swarm initialized: current node (uc1ugbnrpdrskva7gdursmfip) is now a manager.

To add a worker to this swarm, run the following command:

    docker swarm join --token SWMTKN-1-1fuqqgtfuyz1ridr70l2jntlknqj2oabde853std5r3mas94fh-4saxb11a9mxyobrj9iuh1fao3 192.168.65.3:2377

To add a manager to this swarm, run 'docker swarm join-token manager' and follow the instructions.

---

Le reste des commandes roule bien













































//