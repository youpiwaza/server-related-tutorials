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
  --mount type=bind,source=$(pwd),destination=/backup \
  alpine:latest \
  /bin/ash

### 2 / KO / Without pwd var
docker run --rm -i -t  \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  --mount type=bind,source=/home/THE_DOCKER_GUY/tests/backup-volumes,destination=/backup \
  alpine:latest \
  /bin/ash

### 3 / KO / W. only current directory mounted (no volume)
docker run --rm -i -t  \
  # --mount type=bind,source=$(pwd),destination=/backup \
  --mount type=bind,source=/home/THE_DOCKER_GUY/tests/backup-volumes,destination=/backup \
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
# /home/DOCKER_PEON/tests/backups-volumes # Folder create by DOCKER_GUY
# OK
docker run --rm -i -t  \
  -v $(pwd):/home/backup \
  alpine:latest \
  /bin/ash

# ---

## Test OQ in proper folder
# 1 / KO / docker_guy > permission denied
docker run --rm \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  --mount type=bind,source=/home/DOCKER_PEON/tests/backups-volumes,destination=/backup \
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

## Tests sauvegarde basique OK

Note : docker_guy peut s'en occuper (création de la sauvegarde & extraction) si c'est dans son dossier.

Si géré dans le dossier de docker_peon, l'extraction doit être faite par builder guy (besoin de sudo).

```bash
## OK / Conteneur temporaire > connexion au volume à sauvegarder & connexion volume bindé sur hôte
## "zip" du contenu à sauvegarder & copie sur l'hôte (via le bind)
# docker_guy, dans /home/DOCKER_PEON/tests/backups-volumes
docker run --rm -i -t  \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  --mount type=bind,source=$(pwd),destination=/backup \
  --userns=host \
  -w /home/backup \
  alpine:latest \
  tar -cvf /backup/backup.tar "/home/volumeContent"
# > ls -la
# total 88
# drwxr-xr-x 2 root root  4096 mai   26 16:07 .
# drwxr-xr-x 7 root root  4096 mai   26 14:52 ..
# -rw-r--r-- 1 root root 73216 mai   26 16:07 backup.tar
## oké

## Test de l'archive, extraction
# BUILDER_GUY
tar -xvf backup.tar
# home/volumeContent/
# home/volumeContent/nginx/
# home/volumeContent/nginx/error.log
# home/volumeContent/nginx/access.log
# home/volumeContent/php-fpm.log
 BUILDER_GUY@ns371715  /home/DOCKER_PEON/tests/backups-volumes  ls
# backup.tar  hey.txt  home  hot.txt
 BUILDER_GUY@ns371715  /home/DOCKER_PEON/tests/backups-volumes  cd home
 BUILDER_GUY@ns371715  /home/DOCKER_PEON/tests/backups-volumes/home  ls
# volumeContent
 BUILDER_GUY@ns371715  /home/DOCKER_PEON/tests/backups-volumes/home  cd volumeContent
 BUILDER_GUY@ns371715  /home/DOCKER_PEON/tests/backups-volumes/home/volumeContent  ls
# nginx  php-fpm.log

# OK / Remove /home folder
sudo tar -xvf backup.tar --strip 1
# ls -la
# total 96
# drwxr-xr-x 4 root   root    4096 mai   26 16:13 .
# drwxr-xr-x 7 root   root    4096 mai   26 14:52 ..
# -rw-r--r-- 1 root   root   73216 mai   26 16:07 backup.tar
# drwxr-xr-x 3 362144 362144  4096 mai   26 14:32 volumeContent
```

Commande à réutiliser :

```bash
# Créer une archive d'un volume
docker run --rm -i -t  \
  --mount source=NOM_VOLUME_A_SAUVEGARDER,destination=/DOSSIER_DANS_VOLUME_A_RECUPERER \
  --mount type=bind,source=/DOSSIER_HOTE_OU_RANGER_L_ARCHIVE,destination=/backup \
  --userns=host \
  -w /home/backup \
  alpine:latest \
  tar -cvf /backup/NOM_FICHIER_SAUVEGARDE.tar "/DOSSIER_DANS_VOLUME_A_RECUPERER"

# Extraire (debug)
(sudo) tar -xvf NOM_FICHIER_SAUVEGARDE.tar --strip 1
```

## KO / Test sauvegarde incrémentielle

[Doc](https://doc.ubuntu-fr.org/tar#utilisation)

A partir du bordel ci-dessus

```bash
## Doc
#  tar --create --file=/save/archive.`date --rfc-3339=date`.tar --listed-incremental=/save/archive.list /home 

## Explication
# tar --create --file=/save/NOM_ARCHIVE.`date --rfc-3339=date`.tar --listed-incremental=/save/archive.list /DOSSIER_A_COMPRESSER

## Test / KO
docker run --rm -i -t  \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  --mount type=bind,source=$(pwd),destination=/backup \
  --userns=host \
  -w /home/backup \
  alpine:latest \
  tar -cvf /backup/archive-incr.`date --rfc-3339=date`.tar --listed-incremental=/save/archive-incr.list "/home/volumeContent"

## KO
docker run --rm -i -t  \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  --mount type=bind,source=$(pwd),destination=/backup \
  --userns=host \
  -w /home/backup \
  alpine:latest \
  tar --create --file=/save/archive.`date --rfc-3339=date`.tar --listed-incremental=/save/archive.list
# tar: unrecognized option: listed-incremental=/save/archive.list
```

## OK / Test sauvegarde avec date dans le nom

```bash
# Run x2
docker run --rm -i -t  \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  --mount type=bind,source=$(pwd),destination=/backup \
  --userns=host \
  -w /home/backup \
  alpine:latest \
  tar -cvf /backup/test-example---backup---$(date +%Y-%m-%d--%H.%M.%S).tar "/home/volumeContent"

ls -la
# total 308
# drwxrwxr-x 3 DOCKER_GUY DOCKER_GUY  4096 mai   27 11:18 .
# drwxrwxr-x 7 DOCKER_GUY DOCKER_GUY  4096 mai   26 15:12 ..
# -rw-r--r-- 1 root                             root                             73216 mai   27 11:18 nom-sauvegarde---backup---2021-05-27--11.18.01.tar
# -rw-r--r-- 1 root                             root                             73216 mai   27 11:18 nom-sauvegarde---backup---2021-05-27--11.18.21.tar
```

## Restaurer volume

[Doc](https://docs.docker.com/storage/volumes/#restore-container-from-backup)

```bash
## Creating test file : Get volume content, add new stuff, create a new archive
# BUILDER GUY, in DOCKER_PEON folder
# backup
sudo docker run --rm -i -t  \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  --mount type=bind,source=$(pwd),destination=/backup \
  --userns=host \
  -w /home/backup \
  alpine:latest \
  tar -cvf /backup/nom-sauvegarde---backup---$(date +%Y-%m-%d--%H.%M.%S).tar "/home/volumeContent"

# extract
sudo tar -xvf nom-sauvegarde---backup---2021-05-27--11.23.57.tar --strip 1

# edit content
sudo nano volumeContent/newContent.txt

# compress new volume
sudo tar -cvf updated-volume.tar "volumeContent/"

## Test restoration
# Doc : un-tar the backup file in the new container`s data volume:
## OQ
# docker run --rm --volumes-from dbstore2 -v $(pwd):/backup ubuntu bash -c "cd /dbdata && tar xvf /backup/backup.tar --strip 1"

## Explanations
docker run --rm \
  # Attach to a container volume
  --volumes-from A_CONTAINER \
  # bind to host file
  -v $(pwd):/backup \
  ubuntu \
  # Go in dedeicated folder, & un-tar host backup
  bash -c "cd /dbdata && tar xvf /backup/backup.tar --strip 1"

## Adapt
# DOCKER_GUY, in proper location
## Test archive injection location
docker run --rm -i -t  \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  --mount type=bind,source=$(pwd),destination=/home \
  --userns=host \
  -w /home \
  alpine:latest \
  /bin/ash

## OK
>> ls -la
# total 248
# drwxr-xr-x    4 root     root          4096 May 27 09:29 .
# drwxr-xr-x    1 362144   362144        4096 May 27 09:36 ..
# -rw-r--r--    1 root     root         81920 May 27 09:29 updated-volume.tar
# drwxr-xr-x    3 362144   362144        4096 May 26 12:32 volumeContent

## Test untar command
# KO / Removes /volumeContent
# >> tar -xvf updated-volume.tar --strip 1

# OK
>> tar -xvf updated-volume.tar
>> cd volumeContent/
>> ls -la
# total 28
# drwxr-xr-x    3 362144   362144        4096 May 27 09:40 .
# drwxr-xr-x    4 root     root          4096 May 27 09:39 ..
# -rw-r--r--    1 root     root            27 May 27 09:26 newContent.txt
# drwxr-xr-x    2 363147   363147        4096 May 27 09:40 nginx
# -rw-r--r--    1 363147   363147       11413 May 21 09:33 php-fpm.log

# Remove content to test from ext. container
>> rm newContent.txt
>> exit

## OK / Final test
docker run --rm -i -t  \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  --mount type=bind,source=$(pwd),destination=/home \
  --userns=host \
  -w /home \
  alpine:latest \
  tar -xvf updated-volume.tar

## Check
docker run --rm -i -t  \
  --mount source=test-helloDeux-logs,destination=/home/volumeContent \
  --mount type=bind,source=$(pwd),destination=/home \
  --userns=host \
  -w /home \
  alpine:latest \
  /bin/ash

>> cd volumeContent/
>> ls -la
# total 28
# drwxr-xr-x    3 362144   362144        4096 May 27 09:41 .
# drwxr-xr-x    4 root     root          4096 May 27 09:39 ..
# -rw-r--r--    1 root     root            27 May 27 09:26 newContent.txt
# drwxr-xr-x    2 363147   363147        4096 May 27 09:41 nginx
# -rw-r--r--    1 363147   363147       11413 May 21 09:33 php-fpm.log
```
