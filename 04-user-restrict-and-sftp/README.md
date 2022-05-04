# Setting up a restricted user, which can only sftp in a specific folder, aka chroot prison

The goal here is to provide one sftp user per client website, to allow host file manipulation

Note: Those tutorials are slightly outdated, prefer refer to current **doc**:

- [Ubuntu 20 adduser/addgroup](https://manpages.ubuntu.com/manpages/focal/fr/man8/adduser.8.html)
- [Ubuntu 20 sshd_config](https://manpages.ubuntu.com/manpages/focal/man5/sshd_config.5.html)
- [Ubuntu 20 usermod](https://manpages.ubuntu.com/manpages/focal/fr/man8/usermod.8.html)

Tutorials

- [User sftp restrict](https://www.tecmint.com/restrict-sftp-user-home-directories-using-chroot/) > #Restrict Users to a Specific Directory
- [Only sftp](https://geraldonit.com/2018/05/02/enabling-sftp-only-access-on-linux/)

Note: Both tutorials seems legit, but won't work with my setup as you need ssh key files (.ppk). Edit: Create user through ansible role (incl. keys), then proceed to tutorials.

SO

- [home dir management](https://askubuntu.com/a/250877)
  - Note that users must be logged out else... Yeah just don't log in at the same time (and don't forget to log off after tests)
  - Don't use `chroot` command, use the User/Group Matches in `sshd_config` & `ChrootDirectory`
    - Also user `usermod --home FOLDER USER`

## (Purge) Notes

This has been a real purge & a long process, some bullsh*t & lots of things not in the official documentation.

Here's some must know/notes/tips about this:

- Security
  - When using sftp protocol only, most stuff are restricted: user can mostly `ls cd chmod mkdir touch` & upload/download files (depending on rights & own) but he can't execute scripts or such (from what i've seen).
  - Note that he (vanilla) still has no access restriction to folders and can `cd ..` or `cd /`, implying the need of the chroot prison (~= can't "escape" from folder) nor edit stuff outside.
- sftp is compatible with user having shell set to `/sbin/nologin` under certain circumstances (chroot prison &&||? sftp only)
- sftp can be tested through either
  - terminal > eval ssh agent, add key & passphrase
  - software (~filezilla) > Authentication "key file" > set private .ppk
- Problems encountered
  - Some commands/params don't work: Make sure to refer to ubuntu doc, PROPER VERSION, and prefer using full params & not aliases, eg. `adduser -g MY_GROUP -s MY_SHELL` >> `adduser --group MY_GROUP --shell MY_SHELL`
  - Can't sftp connect
    - Must have ssh keys set (ssh-agent or key file)
    - Those keys **MUST BE** in user's home folder, in `.ssh/` hidden folder
      - Use `ls -lah` to check for hidden stuff
      - This is especially important if user's home directory is moved/renamed
      - Usually a `.cache/` folder comes along, with specific chmod/chown: `700 root:root`
    - Don't forget to specify the SSH port, especially if it's custom
      - Pay attention to syntax & params placement : big case `-P` to specify port, **must be before** adress: `sftp -P 1234 USER@123.123.123.123`
    - user home directory **must** have correct rights & ownership, ~7XX & USER:GROUP
    - I'd not recommand shell set as `rbash`
    - sshd_config shenanigans
- `/etc/ssh/sshd_config` bullsh*t
  - If new config isn't good, it won't update. Pay attention to error messages when restarting the service. Prefer test the config before with `sudo sshd -t`
  - Under certain circumstances, it won't include `/etc/ssh/sshd_config.d/*.conf` by default. Need to be manually included through `Include /etc/ssh/sshd_config.d/*.conf` in the sshd_config file.
    - Also note that some/most instructions just **WON'T WORK FOR NO REASON** when included, like `ChrootDirectory` && `ForceCommand`, [hey SO](https://unix.stackexchange.com/questions/464637/put-forcecommand-in-a-user-specific-configuration-file)
  - They are several commands to restart the ssh service, depending on OS & stuff. Try & note the one working before loosing time.
  - `sshd_config` **doesn't overload configuration** : first instruction is law !
    - If you want to make includes, make them at the beginning of the file, **else they will be ignored** if instruction is already declared by default.
    - Same if no includes. Comment default or declare beforehand.
    - It also has a real problem with same case declarations: don't user `Match User SAME_GUY` several times or some might be ignored. Same for `Match Group`
      - Can also error if `Subsystem sftp  internal-sftp` the Subsystem instruction is declared twice..
      - cf. [SO](https://unix.stackexchange.com/questions/61655/multiple-similar-entries-in-ssh-config)
  - When using `ChrootDirectory`, pay attention to trailing `/`, as it can error on user login.
  - `ChrootDirectory` allow the use of some aliases
    - `%u` for user name
    - `%h` for user home directory path
  - You can specify default folder location when user connects through `ForceCommand internal-sftp -d /FOLDER`
    - It must be accessible & properly chmod/chown, else connexion KO
- Chroot prison
  - Pay attention to target specific user/group, else you'll be lock
  - Main folder must be own be `root:root` AND can't be editable other than root
  - Must contains a folder with proper user rights USER:USER|GROUP 7XX
  - Documents for user to read (README.md) must allow to read, use special rights > 311 & root:root for no modification
  - Can be set ~anywhere: in `/home`, in `/`, and in another user `/home/DAT_GUY/chroot_prison/` folder as long are rights & config are set properly
  - User will always have access to main chroot prison folder, but can't get back higher.
  - You ~can (not tested) set several chroot prison in one system

## Usefull commands

```bash
## User stuff
# List ubuntu users (+ /home folder definition & designated shell)
sudo cat /etc/passwd

# Add user / Prefer use ansible role
# adduser --group MY_GROUP --shell MY_SHELL DA_USER

# Edit user, change /home folder, shell
~sudo usermod
sudo usermod --home /test_chroot/DA_USER DA_USER
sudo usermod --shell /sbin/nologin DA_USER

# Remove ubuntu user / might not remove user /home folder, wherever it is
sudo userdel -r DA_USER


## Group stuff
# Create group
sudo groupadd DA_GROUP
sudo groupdel DA_GROUP

# Add user to group
sudo usermod -a -G DA_GROUP DA_USER

# List user's groups
sudo groups DA_USER


## SSH stuff
# Display current sshd configuration (especially usefull when using Includes or such)
#       https://unix.stackexchange.com/a/218191
sudo sshd -T
sudo sshd -T | sort

# (Check config & ) Reboot sshd service
sudo sshd -t
sudo service sshd restart
# Or sudo systemctl restart sshd


## Folder/File stuff
mkdir DIRECTORY_TO_CREATE_NO_ARBO
sudo mkdir -p /test_chroot/DA_USER

chmod -R RIGHTS PATH
sudo chmod 700 /test_chroot/DA_USER/

chown USER:GROUP PATH
sudo chown DA_USER:DA_GROUP /test_chroot/DA_USER/

sudo rm -R PATH_TO_REMOVE
```

## sshd_config example configuration

`sudo nano /etc/ssh/sshd_config` << don't forget to save & TEST & restart ssh service

```ini
### Simple example
# Subsystem     sftp    /usr/lib/openssh/sftp-server
Subsystem   sftp    internal-sftp

# Match User DA_USER
Match Group DA_GROUP
    ChrootDirectory     /test_chroot/
    ForceCommand        internal-sftp
    X11Forwarding       no
    AllowTcpForwarding  no



### Explanations
## Restrict sftp
## Disable default config, else error
# Subsystem     sftp    /usr/lib/openssh/sftp-server
Subsystem   sftp    internal-sftp

## Target only a user or all users from a group
# Match User DA_USER
Match Group DA_GROUP
    ## Force prison on login in a specific folder (must be created manually & chmod/chown & user /home assigned accordingly)
    ## Can use %u for username if one prison for several users
    ## Don't forget trailing /
    ChrootDirectory     /test_chroot/%u/
    ## User can only use sftp and nothing else
    ## -d to specify which folder user is set in on login, here his own folder
    ForceCommand        internal-sftp -d /%u
    ## Security stuff
    X11Forwarding       no
    AllowTcpForwarding  no
```

## Manual tests

Not translated :D, just to keep a trace of step by step

```bash
## Tester chroot prison a la racine des utilisateurs, à la main
## builder_guy
## Création du répertoire chroot
sudo mkdir -p /test_chroot/
sudo chown root:root /test_chroot/



### Création de l'utilisateur / "Pas possible" de le faire à la main pour clés connexions SSH

## Vidanger utilisateur
## Local > C:\Users\Patolash\.ssh >> Virer bob*
sudo userdel -r bob

## Créer utilisateur AVEC connexion alakon
# Local > ansible-playbook -i hostsWithCustomSSHPort 99-craft-and-tests.yml
## Local > C:\Users\Patolash\.ssh\_filezilla sftp commeng.txt // Ajouter clé & tester connexion

## Changer le répertoire home de l'utilisateur
sudo mkdir -p /test_chroot/bob
sudo chown bob:bob /test_chroot/bob/
sudo chmod 700 /test_chroot/bob/

## Ne doit pas être connecté en même temps !
sudo usermod --home /test_chroot/bob bob
# // ^ connexion filezilla KO
sudo rm -R /home/bob

## Changer son shell en nologin
sudo usermod --shell /sbin/nologin bob


## Configurer le ssh afin de mettre en place la prison chroot
# sudo nano /etc/ssh/sshd_config
#> Subsystem sftp  internal-sftp
#> 
#> Match User bob
#>    ChrootDirectory /test_chroot/
#>    ForceCommand internal-sftp
#>    X11Forwarding no
#>    AllowTcpForwarding no
## Tester la nouvelle configuration du ssh avant reboot
sudo sshd -t
## Reboot ssh
sudo systemctl restart sshd (OU ||) sudo service sshd restart

### Tester connexion via filezilla
## KO :'(
## Tests empiriques
sudo chmod 777 /test_chroot/bob/      // nope
sudo usermod --shell /bin/bash bob    // nope

## Restoration du shell & du répertoire par défaut
sudo mkdir /home/bob
sudo chown bob:bob /home/bob
sudo usermod --home /home/bob bob
## Toujours KO

## Restoration de la config sshd
sudo nano /etc/ssh/sshd_config
#> # *
sudo sshd -t
## Reboot ssh
sudo systemctl restart sshd
## Toujours KO

### Script création user >> ansible-install-web-server\ansible\roles\users\tasks\add-ssh-key.yml
### La clé est ajoutée dans le répertoire home de l'utilisateur dans le dossier (caché) /.ssh lelelel
# Local > ansible-playbook -i hostsWithCustomSSHPort 99-craft-and-tests.yml
## /home/bob/.ssh/ restauré > possibilité de se connecter



##### DU COUP
### Même chose en conservant .ssh/
sudo cp -R /home/bob/ /test_chroot/
sudo chown -R bob:bob /test_chroot/bob/
sudo chmod -R 700 /test_chroot/bob/
sudo usermod --home /test_chroot/bob bob

## Connexion toujours ok mais pas emprisonné
sudo usermod --shell /sbin/nologin bob
## Connexion KO ^
sudo usermod --shell /usr/bin/zsh bob
## Connexion re-OK
## Modif sshd_config..
## Test connexion > user arrive sur /   -_-"""""
## OK > Léger lag du au reboot du service sshd ?
## User bien bloqué dans /test_chroot/, ne peux pas remonter.
## Ne peux créer des fichiers que dans /test_chroot/bob/
## MAIS il peut aussi modifier les droits des fichiers (~777 > executable)


## Shell > nologin
sudo usermod --shell /sbin/nologin bob
## OKé, mais droits toujours modifiables (fait partie du sftp après tout)


## Teste de goyer depuis le terminal
#> eval `ssh-agent` && ssh-add ~/.ssh/bob-ssh-key-ed25519
#> bonjour
#> sftp -P SSH_PORT bob@HOST_IP
#> // Lancement de script etc. >> Tout est KO en dehors de mkdir/touch/cd/chmod, c'est oké



### Définir dossier par défaut /bob
## modif sshd_config
#> Match User bob
#>    ForceCommand internal-sftp -d /bob





##### Note: 💩 DOESN'T WORK - WASTE OF TIME 💩
##### Tester avec conf sshd dans fichier utilisateur dédié plutôt que dans conf générale !
## modif sshd_config > virer Match User bob
# + restart sshd service > Connexion KO ce qui est normal

## Ajouter un fichier dédié à bob dans /etc/ssh/sshd_config.d/
sudo nano /etc/ssh/sshd_config.d/bob.conf
#> Subsystem sftp  internal-sftp
#> 
#> Match User bob
#>    ChrootDirectory /test_chroot/
#>    ForceCommand internal-sftp -d /bob
#>    X11Forwarding no
#>    AllowTcpForwarding no
# sudo sshd -t
# sudo systemctl restart sshd
## KO > Pas pris en compte

### dans sshd_config, besoin de 2 choses
## Modifier le sftp par défaut
# # Subsystem     sftp    /usr/lib/openssh/sftp-server
# Subsystem sftp  internal-sftp

## Inclure les fichiers de conf dans le dossier dédié
# Include /etc/ssh/sshd_config.d/*.conf

## Retirer la conf du sftp par défaut dans le fichier de l'utilisateur
# sudo nano /etc/ssh/sshd_config.d/bob.conf
#> ## Chroot prison for user 'bob'
#> Match User bob
#>   ChrootDirectory /test_chroot/
#>   ForceCommand internal-sftp -d /bob
#>   X11Forwarding no
#>   AllowTcpForwarding no

# sudo sshd -t
# sudo systemctl restart sshd

## ETtt.... toujours KO wtf
# test niquer fichier > erreurs procs > bien chargé
# test avec 1 ligne vierge en plus a la fin
# (Doc & test) >> GROSSO MERDO premier arrivé premier servi > Pas d'override
# >> Inclure les fichiers de conf en premier dans sshd_config
## Toujours KO

## Vérification C/C >> dans sshd_config ca roule, CC dans fichier dédié non
## Sur le net > Problème avec forcecommand ? https://unix.stackexchange.com/a/464736

## Test avec force command uniquement dans sshd_config (si cela fonctionne > groupe pr force command & user file pour dossier dédié (!= de ./home donc pas de %h :/))
## Note voir avec %u ?? :0
## edit both sshd_conf & bob.conf >> bob contient uniquement ChrootDirectory
## KO
##### 💩 - END OF WASTE OF TIME - 💩





## Plusieurs Match User bob << Seulement le premier est chopay ?
## sshd_config > tout sauf force command > OK
## bob.conf > inclus & tout sauf force command > KO

# cf. https://unix.stackexchange.com/a/359554
# > faire groupe & utiliser %u ?
# > 1 seul utilisateur possédant un seul prison (docker_peon)/home, dans lequel sont montés les volumes nécessaires ?

## Exemple archi
# /home/docker_peon/clients/_websites_files_sftp_access_chroot_prison/
#     README.md >> Not really the datas, only access to named volumes (l'intérêt c'de pouvoir kill les conteneurs temp/ afin de renforcer la sécu)
#     // /core & /tests << Pas besoin, accès direct ssh via builder
#     /%u---DASHED-URI
#     /michel---michel--com
#     /bob---bob--com
#       /.ssh/auth_keyz
#       /configs
#       /volume1
#       /volume2
#     /bob---sub--bob--com

#> Match Group esseEffeTayPayAkses
#>  ChrootDirectory /home/docker_peon/clients/_websites_files_sftp_access_chroot_prison/%u/

# ^ Comme ça c'rangé a chacun a son dossier sans dégueuler sur les autres
# Attention ! Pas un seul utilisateur par client è_é >> Un par site




##### METTRE CA EN PLACE
#### Cleaner merdier d'avant
## Virer *.conf & conf OK

#### Tester sans les clés ssh dans la home de bob // OK

#### Tester le merdier avec les groupes
### Nouvel emplacement
sudo cp /home/bob/ /test_chroot/bob/
## Ajouter les bons droits
sudo chown -R bob:esseEffeTayPayAkses /test_chroot/bob/
sudo chmod 700 /test_chroot/bob/

## Créer groupe
sudo groupadd esseEffeTayPayAkses
## Ajouter connard au groupe
sudo usermod -a -G esseEffeTayPayAkses bob

### Maj sshd_config
## Remplacer user bob par group esseEffeTayPayAkses
## Verif & reboot
### OK



#### Tester chroot dans (repertoire autre utilisateur) /home/docker_peon
## Créer répertoire de home alakon fermé, pour le client avec /.ssh/auth_keyz
sudo cp /home/bob/ /home/docker_peon/clients/_websites_files_sftp_access_chroot_prison/bob/

## Ajouter les bons droits
sudo chown -R bob:esseEffeTayPayAkses /home/docker_peon/clients/_websites_files_sftp_access_chroot_prison/bob/
sudo chmod 700 /home/docker_peon/clients/_websites_files_sftp_access_chroot_prison/bob/

## Modifier sshd_config + reboot
## KO / Mauvais dossier chroot
# ChrootDirectory /home/docker_peon/clients/_websites_files_sftp_access_chroot_prison/%u/ >>> ChrootDirectory /home/docker_peon/clients/_websites_files_sftp_access_chroot_prison/
## OK



#### Revoir arbo & droits

## Créer répertoire de home alakon fermé, pour le client avec /.ssh/auth_keyz
sudo cp /home/bob/ /home/docker_peon/clients/_websites_files_sftp_access_chroot_prison/bob/

## Ajouter les bons droits
sudo chown -R root:root /home/docker_peon/clients/_websites_files_sftp_access_chroot_prison/bob/
sudo chmod 700 /home/docker_peon/clients/_websites_files_sftp_access_chroot_prison/bob/

## Répertoire de goye
sudo mkdir /home/docker_peon/clients/_websites_files_sftp_access_chroot_prison/bob/yay_fun/
sudo chown -R bob:esseEffeTayPayAkses /home/docker_peon/clients/_websites_files_sftp_access_chroot_prison/bob/yay_fun/

## Modifier sshd_config + reboot
# Erreur : Impossible de récupérer le contenu du dossier << typo 💩
### OKAY PUTAIN





##### 🎉 SAUVEGARDE DU BAIL 🎉
#### Arbo
# /home/docker_peon/clients/_websites_files_sftp_access_chroot_prison/                                      / root:root 755
#     README.md >> Not really the datas, only access to named volumes (l'intérêt c'de pouvoir kill les conteneurs temp/ afin de renforcer la sécu)  / root:root 311
#     /%u---DASHED-URI              // chroot prison template : CET utilisateur (& son site : 1 utilisateur par site) / root:root 755
#     /michel---michel--com         // chroot prison de CET utilisateur / root:root 755
#     /bob---bob--com               // chroot prison de CET utilisateur / root:root 755
#       /.cache/                    // bob:bob 700
#       /.ssh/                      // bob:bob 700
#       /README.md                  // root:root 311 -rw-r--r-- > Ne rien modifier ici, de toutes manières tu peux pas laul
#       /yay_fun                    // bob:bob 700 + point d'arrivée de l'utilisateur (il peut remonter dans /bob---bob--com mais pas plus haut)
#         /README.md                // root:root 311 -rw-r--r-- > # Hellow /n Ton petit dossier d'utilisateur ubuntu a toi ;) /n Tu as accès aux fichiers des sites (configs & volumes des conteneurs) dans les différents dossiers présents ici. /n Si c'pas la go me demander d'activer le conteneur dédié. /n Bonne journée
#         /configs                    
#         /volume1                    
#         /volume2                    
#     /bob---sub--bob--com



#### Config sshd_config
#> Match Group esseEffeTayPayAkses
#>         ChrootDirectory         /home/docker_peon/clients/_websites_files_sftp_access_chroot_prison/%u/
#>         ForceCommand            internal-sftp -d /yay_fun
#>         X11Forwarding           no
#>         AllowTcpForwarding      no
```

Yay let's automate this horsesh*t and never speak of it again.

## Automation / CHANGE VARS AVANT COMMIT / Bind a named volume

Notes & pistes

- Only with named volumes existing data is copied from the container target folder into the volume.
- 💩 KO / Bind autre dossier + Création d'un lien symbolique
  - liens symboliques [doc fr](https://doc.ubuntu-fr.org/lien_physique_et_symbolique)
  - Virer un lien (symbolique) `unlink LIEN`
- Serveur sftp en tant que conteneur, utilisant le même volume nommé
  - [docker forum](https://forums.docker.com/t/shared-web-hosting-with-docker-best-practices/7893/3)
  - dockerhub > [maintained sftp](https://hub.docker.com/r/atmoz/sftp)
- Site de dev > docker cp prod vers host > dev avec bind edit > docker cp dev vers prod
  - `docker cp` Copy files/folders between a container and the local filesystem
    - [doc](https://docs.docker.com/engine/reference/commandline/cp/)

```bash
# Monter un conteneur ubuntu alakon afin de bind un volume dans un rep
#     https://docs.docker.com/engine/reference/run/
#     https://docs.docker.com/storage/volumes/
# Récup du named volume via --volumes-from
# Mise a dispo via un BIND
docker run --rm -i -t  \
  ## Mount all volumes related to the container
  # --volumes-from CONTAINER
  ## Create a new volume binded to a SOURCE path on the host

  --mount destination=/home/volumeContent,source=/home/singed_the_docker_peon_9f3eqk4s9/tests/masamune/bind-volumes,type=bind \
  -w /home/volumeContent \
  alpine:latest \
  /bin/ash

### nc
## Tests w. hello-masa-fr, using ~nginx
# cf. ansible-install-web-server\ansible\generated\tests\masamune\hello--masamune--fr\stack\hello--masamune--fr---nginx--generated.yml

# OK > Les fichiers présents dans le dossier host sont visibles dans le conteneur
docker run --rm -i -t  \
  --mount destination=/home/volumeContent,source=/home/singed_the_docker_peon_9f3eqk4s9/tests/masamune/bind-volumes,type=bind \
  -w /home/volumeContent/ \
  alpine:latest \
  /bin/ash

# OK > ls dans conteneur > on a les fichiers
docker run --rm -i -t  \
  --volumes-from test---hello--masamune--fr_nginx.1.nt838dx0c46q00t4mhgewfbn6 \
  -w /var/log/ \
  alpine:latest \
  /bin/ash

# KO > le mount écrase le contenu du volume-from
docker run --rm -i -t  \
  --mount destination=/var/log/,source=/home/singed_the_docker_peon_9f3eqk4s9/tests/masamune/bind-volumes,type=bind \
  --volumes-from test---hello--masamune--fr_nginx.1.nt838dx0c46q00t4mhgewfbn6 \
  -w /var/log/ \
  alpine:latest \
  /bin/ash

# OK // Essai en montant le volume nommé plutôt que les volumes d'un conteneur
docker run --rm -i -t  \
  --mount destination=/var/log/,source=test---hello--masamune--fr---nginx--logs,type=volume \
  -w /var/log/ \
  alpine:latest \
  /bin/ash

# double trouble # Prevent duplicate mount point
#   /var/log/ contient bien les logs
#   /home/ contient bien 'hey' présent sur l'hôte
docker run --rm -i -t  \
  --mount destination=/var/log/,source=test---hello--masamune--fr---nginx--logs,type=volume \
  --mount destination=/home/,source=/home/singed_the_docker_peon_9f3eqk4s9/tests/masamune/bind-volumes,type=bind \
  -w /var/log/ \
  alpine:latest \
  /bin/ash

# KO, /home/ chown is nobody
>> ln –s /var/log/ /home/

# fuck chown
# Création du lien oké
# KO, /home/ contient le lien symbolique vers /var/log, et c'est bien répercuté sur l'hôte
#     DANS LE PUTAIN DE SENS ou sur l'hôte on est redirigés vers /var/log de l'hôte wtf
docker run --rm -i -t  \
  --mount destination=/var/log/,source=test---hello--masamune--fr---nginx--logs,type=volume \
  --mount destination=/home/,source=/home/singed_the_docker_peon_9f3eqk4s9/tests/masamune/bind-volumes,type=bind \
  --userns=host \
  -w /var/log/ \
  alpine:latest \
  /bin/ash

>> ln –s /var/log/ /home/

# ln -s > symbolique ; need lien physique
# KO // Cross-device link
>> ln /var/log/ /home/

# Essai sans les / finaux -_-
# KO
>> ln -s /var/log /home
# KO
>> ln /var/log /home

# ---

### Test sftp container
# https://hub.docker.com/r/atmoz/sftp
# docker run \
#     -v <host-dir>/upload:/home/foo/upload \
#     -p 2222:22 -d atmoz/sftp \
#     foo:pass:1001

# OK
docker run --rm -i -t \
    -v /home/singed_the_docker_peon_9f3eqk4s9/tests/masamune/bind-volumes:/home/foo/upload \
    -p 2222:22 \
    atmoz/sftp \
    foo:pass:1101

# OK
sftp -P 2222 foo@188.165.253.170

# w. bind. volumes
docker run --rm -i -t \
    --mount destination=/var/log/,source=test---hello--masamune--fr---nginx--logs,type=volume \
    --name 
    -p 2222:22 \
    -w /var/log/ \
    atmoz/sftp \
    foo:pass:1101

```
