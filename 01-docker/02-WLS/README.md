# Installation de docker desktop, puis lien vers WSL

Outdated, voir le [repo dédié à l'installation de l'environnement de dev](https://github.com/youpiwaza/install-dev-env).

---
---
---

## Old 👴💩

Installation sur WSL pour que cela soit plus proche de l'environnement serveur

[docker doc ubuntu](https://docs.docker.com/install/linux/docker-ce/ubuntu/)

```bash
> sudo docker run hello-world
# docker: Cannot connect to the Docker daemon at unix:///var/run/docker.sock. Is the docker daemon running?.
# See 'docker run --help'.
```

sigh

- [Docker on WLS](https://nickjanetakis.com/blog/setting-up-docker-for-windows-and-wsl-to-work-flawlessly)
  - En cas de mise à jour > Docker desktop settings > Expose daemon on tcp://localhost:2375 without TLS > OKé
    - **Forcer le restart de Docker !**
  - [Installation Docker sur ubuntu officiel (maintenu)](https://docs.docker.com/install/linux/docker-ce/ubuntu/)
  - Installation docker compose ko > [SO / Installation DC](https://stackoverflow.com/a/36689427/12026487)

En gros, on fait tourner Docker desktop qui fait tourner le daemon Docker parfaitement sous windows, et on s'y connecte via WLS

Steps :

- Installations docker & docker compose ok
- Path ok
- WSL pour se connecter au docker windows ok
- Vérifications > KO
  - Variable d'environnement DOCKER_HOST KO [SO](https://stackoverflow.com/questions/25225206/exporting-docker-host-in-bashrc-produces-a-different-result-to-the-same-command)
  - Solution > exécuter directement la création de la variable d'environnement :
    - Note : Cela ne fonctionne que pour l'utilisateur courant...

``` bash
# Dans le terminal
> export DOCKER_HOST=tcp://localhost:2375
> echo "Docker Host is set to ${DOCKER_HOST}"
# Vérifications
> docker info
> docker run hello-world
```

- Vérification docker-compose > KO
  - Installation classique plutôt que par pip [SO / Installation DC](https://stackoverflow.com/a/36689427/12026487)
  - `> docker-compose --version` ok

- Correction volumes
  - /mnt/c/ > /c/
  - Non testé
