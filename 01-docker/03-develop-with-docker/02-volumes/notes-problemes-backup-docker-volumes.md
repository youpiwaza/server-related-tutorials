# Notes backup docker volumes

Résolution des problèmes de droits lors de la création de l'archive (permission denied).

Lié à la configuration du démon docker > user remap.

Solution : Lancement du conteneur sans le user remap.

## Bash & tests

```bash
# Tests
## Create a container with a connexion to the desired volume and create an archive

### 1 / OK / Connect to a volume through alpine
docker run --rm -i -t \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  -w /home/volumeContent \
  alpine:latest \
  /bin/ash

>> ls -la
# /home/volumeContent # ls -la
# total 24
# drwxr-xr-x    3 root     root          4096 May 27  2020 .
# drwxr-xr-x    1 root     root          4096 May 26 12:10 ..
# drwxr-xr-x    2 1003     1003          4096 May 27  2020 nginx
# -rw-r--r--    1 1003     1003         11413 May 21 09:33 php-fpm.log

### 2 / KO / container bash manual
>> tar cvf /backup/backup.tar "/home/volumeContent"
# >> tar: can't open '/backup/backup.tar': No such file or directory

# Doc tar / https://doc.ubuntu-fr.org/tar
#   c : crée l'archive
#   f : utilise le fichier donné en paramètre
#   v : active le mode « verbeux » (bavard, affiche ce qu'il fait)
#   x : extrait l'archive
# Need - : tar -cvf

### 3 / OK / Révision syntaxe
>> tar -cvf test-backup.tar "php-fpm.log"
#   php-fpm.log
#   /home/volumeContent # ls
#   nginx            php-fpm.log      test-backup.tar

### 4 / OK / Extraction
>> tar -xvf test-backup.tar

# ---

## Mount 2 volumes
### 1 / KO / permission denied / Test w. interactive & shell
docker run --rm -i -t  \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  --mount type=bind,source=$(pwd),target=/backup \
  alpine:latest \
  /bin/ash

### 2 / KO / Without pwd var
docker run --rm -i -t  \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  --mount type=bind,source=/home/THE_DOCKER_GUY/tests/backup-volumes,target=/backup \
  alpine:latest \
  /bin/ash

### 3 / KO / W. only current directory mounted (no volume)
docker run --rm -i -t  \
  # --mount type=bind,source=$(pwd),target=/backup \
  --mount type=bind,source=/home/THE_DOCKER_GUY/tests/backup-volumes,target=/backup \
  alpine:latest \
  /bin/ash

# ---

## Cf. ci-dessus, utiliser syntaxe avec -v ? user specific ? folder specific ?
#     https://docs.docker.com/engine/reference/run/#volume-shared-filesystems
# pwd > home/DOCKER_GUY 
# KO
docker run --rm -i -t  \
  -v $(pwd):/home/backup \
  alpine:latest \
  /bin/ash

# pwd
# /home/DOCKER_PEON/tests/backups-volumes # Folder create by BUILDER_GUY
# OK
docker run --rm -i -t  \
  -v $(pwd):/home/backup \
  alpine:latest \
  /bin/ash

# ---

## Test OQ in proper folder
# 1 / KO / docker guy > permission denied
docker run --rm \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  --mount type=bind,source=/home/DOCKER_PEON/tests/backups-volumes,target=/backup \
  alpine:latest \
  tar -cvf /backup/backup.tar "/home/volumeContent"

# 2 / KO /  builder guy
sudo docker run --rm \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  -v /home/DOCKER_PEON/tests/backups-volumes:/backup \
  alpine:latest \
  tar -cvf /backup/backup.tar "/home/volumeContent"

# ---

## Manual > bind volume & create a file inside the container (should be replicated to host through bind)
# KO
docker run --rm -i -t  \
  -v $(pwd):/home/backup \
  -w /home/backup \
  alpine:latest \
  /bin/ash

>> /home/backup > touch hey.txt
# touch: hey.txt: Permission denied
>> /home/backup > sudo touch hey.txt
# /bin/ash: sudo: not found
>> /home/backup > ls -la
# total 8
# drwxr-xr-x    2 nobody   nobody        4096 May 26 12:52 .
# drwxr-xr-x    1 root     root          4096 May 26 13:05 ..
>> /home/backup > whoami
# root

# ---

# ## Test via template ci-dessus

# docker run --rm -i -t  \
#   --name temp-backup-volume \
#   --user 1003:1003 \
#   -v /home/DOCKER_PEON/tests/backups-volumes:/home/backup:rw \
#   -w /home/backup \
#   alpine:latest \
#   /bin/ash

# https://docs.docker.com/storage/volumes/#backup-restore-or-migrate-data-volumes

## Possibilité de bind uniquement depuis le dossier du peon
docker run --rm -i -t  \
  -v /home/DOCKER_PEON/tests/backups-volumes:/home/backup \
  -w /home/backup \
  alpine:latest \
  /bin/ash

# On récupère bien le contenu du dossier bindé, may pas d'accès en modif
# Dans le conteneur on est root, le dossier/fichier est attribué à nobody

# Probablement lié a "userns-remap": docker_peon dans la conf docker.json
# https://docs.docker.com/engine/security/userns-remap/#disable-namespace-remapping-for-a-container

docker run --rm -i -t  \
  --userns=host \
  -v /home/DOCKER_PEON/tests/backups-volumes:/home/backup \
  -w /home/backup \
  alpine:latest \
  /bin/ash

>> /home/backup > whoami
# root
>> /home/backup > ls -la
# total 12
# drwxr-xr-x    2 root     root          4096 May 26 13:22 .
# drwxr-xr-x    1 362144   362144        4096 May 26 13:41 ..
# -rw-r--r--    1 root     root             4 May 26 13:22 hey.txt

############################### bingow

docker run --rm -i -t  \
  --userns=host \
  -v $(pwd):/home/backup \
  -w /home/backup \
  alpine:latest \
  /bin/ash

# Mots clés pour futurs problèmes alakon : nobody volumes créer modifier éditer backup bind -b mount :rw
```

Si on arrive pas à éditer du contenu lié au volume bindé (grosso merdo éditer le contenu de l'hôte depuis le conteneur)
C'est normal pour des raisons de sécurité. Mais au cas ou l'on en aurait vraiment besoin, par exemple
afin de faire une sauvegarde d'un volume d'un conteneur spécifique, il faut repasser en root.

Par défaut on est pas root (docker daemon.json > conf afin de remapper l'utilisateur)

Du coup > annuler remap pour un conteneur spécific

Note : attention à l'escalade de privilèges : le conteneur a plus de droit que l'utilisateur hôte !

Exemple :

1. via DOCKER_GUY > je vais chez /home/DOCKER_PEON > touch hey.txt > KO
2. Je monte le conteneur avec le bind dans le même dossier >> je crée le fichier depuis le conteneur >> OK > je ressors du conteneur > le fichier est bien présent sur l'hôte
   - Les droits du nouveau fichier sont extrèememtn restreint `-rw-r--r-- 1 root root   63 mai   26 15:44 hey.txt` donc sans sudo ~np

```bash
# Le test
 DOCKER_GUY@SERVER  /home/DOCKER_PEON/tests/backups-volumes  touch hoy.txt
# ✘ touch: cannot touch 'hoy.txt': Permission denied
 DOCKER_GUY@SERVER  /home/DOCKER_PEON/tests/backups-volumes  docker run --rm -i -t  \
>   --userns=host \
>   -v $(pwd):/home/backup \
>   -w /home/backup \
>   alpine:latest \
>   /bin/ash
/home/backup # touch hoy.txt
/home/backup # vi hoy.txt
### Adding "some content"
/home/backup # exit
 DOCKER_GUY@SERVER  /home/DOCKER_PEON/tests/backups-volumes  cat hoy.txt
# some content
 DOCKER_GUY@SERVER  /home/DOCKER_PEON/tests/backups-volumes  ls -la
# total 16
# drwxr-xr-x 2 root root 4096 mai   26 15:52 .
# drwxr-xr-x 7 root root 4096 mai   26 14:52 ..
# -rw-r--r-- 1 root root   63 mai   26 15:44 hey.txt
# -rw-r--r-- 1 root root   13 mai   26 15:52 hoy.txt
```
