# Setting up a restricted user, which can only sftp

The goal here is to provide one user per client website, to allow host file manipulation (secore ftp)

Note: Those tutorials are slightly outdated, prefer refer to current doc:

- [Ubuntu 20 adduser/addgroup](https://manpages.ubuntu.com/manpages/focal/fr/man8/adduser.8.html)
- [Ubuntu 20 sshd_config](https://manpages.ubuntu.com/manpages/focal/man5/sshd_config.5.html)

Tutorials

- [User sftp restrict](https://www.tecmint.com/restrict-sftp-user-home-directories-using-chroot/) > #Restrict Users to a Specific Directory
- [Only sftp](https://geraldonit.com/2018/05/02/enabling-sftp-only-access-on-linux/)

Note: Both tutorials seems legit, but won't work with my setup as you need ssh key files (.ppk).

## Setup one user

```bash
### Restrict Users to a Specific Directory
# In our previous example, we restrict the existing users to the home directory. Now, we will see how to restrict a new user to a custom directory.

## Create Group and New Users
# Create a new group 'sftpgroup'.
sudo groupadd sftpgroup

# Next, create a directory for SFTP group and assign permissions for the root user.
sudo mkdir -p /sftpusers/chroot
sudo chown root:root /sftpusers/chroot/

# Next, create new directories for each user, to which they will have full access.
# For example, we will create 'tecmint' user and it’s new home directory with correct group permission using following series of commands.

## adduser doc for ubuntu 20
#       https://manpages.ubuntu.com/manpages/focal/fr/man8/adduser.8.html
# Also define the shell to /sbin/nologin, which refuse logins but print a nice message
#       https://ubuntuplace.info/questions/454405/what-is-the-difference-between-sbin-nologin-and-bin-false


# > sudo adduser tecmint -g sftpgroup -s /sbin/nologin
# Option g is ambiguous (gecos, gid, group)
# Option s is ambiguous (shell, system)

# > sudo adduser tecmint --group sftpgroup --shell /sbin/nologin
# adduser: Specify only one name in this mode. // Group already exists, so you must use --ingroup

# > sudo adduser tecmint --ingroup sftpgroup --shell /sbin/nologin
# Adding user `tecmint' ...
# Adding new user `tecmint' (1004) with group `sftpgroup' ...
# Creating home directory `/home/tecmint' ...
# Copying files from `/etc/skel' ...
# New password:
# > 12345
# Retype new password:
# > 12345
# passwd: password updated successfully
# Changing the user information for tecmint
# Enter the new value, or press ENTER for the default
#         Full Name []:
#         Room Number []:
#         Work Phone []:
#         Home Phone []:
#         Other []:
# Is the information correct? [Y/n] Y

## Debug: remove user & /home/tecmint
# > sudo userdel tecmint
# > sudo rm -R /home/tecmint

## Ideally we don't want either home directory nor skeleton folder tree
sudo adduser tecmint --ingroup sftpgroup --shell /sbin/nologin --no-create-home
# Adding user `tecmint' ...
# Adding new user `tecmint' (1004) with group `sftpgroup' ...
# Not creating home directory `/home/tecmint'.
# New password:
# Retype new password:
# passwd: password updated successfully
# Changing the user information for tecmint
# Enter the new value, or press ENTER for the default
#         Full Name []:
#         Room Number []:
#         Work Phone []:
#         Home Phone []:
#         Other []:
# Is the information correct? [Y/n] Y

# Useless with adduser
# passwd tecmint

# Create custom home directory
sudo mkdir /sftpusers/chroot/tecmint
# Create a test file in user folder (to see something when connecting)
touch /sftpusers/chroot/tecmint/hey.txt

# Use correct group permission
sudo chown tecmint:sftpgroup /sftpusers/chroot/tecmint/
sudo chmod 700 /sftpusers/chroot/tecmint/


## Configure SSH for SFTP Access
# Modify or add the following lines at the end of the configuration file: '/etc/ssh/sshd_config'
sudo nano /etc/ssh/sshd_config

## At the very end
#>> #Subsystem  	sftp	/usr/libexec/openssh/sftp-server
#>> Subsystem sftp  internal-sftp
#>>  
#>> Match Group sftpgroup
#>>    ChrootDirectory /sftpusers/chroot/
#>>    ForceCommand internal-sftp
#>>    X11Forwarding no
#>>    AllowTcpForwarding no

# Verify sshd_config file before restarting
sudo sshd -t

# Save and exit the file. Restart sshd service to take effect the saved changes.
# systemctl restart sshd
# OR
sudo service sshd restart

# That’s it, you can check by logging into the your remote SSH and SFTP server by using the step provided above at Verify SSH and SFTP login.
```

## Tests

```bash
## test ssh connexion
# ssh tecmint@SERVER-IP
ssh tecmint@169.169.169.169
# ssh: connect to host 169.169.169.169 port 22: Connection refused

## (Opt) With a specific port
# ssh tecmint@SERVER-IP -p PORT
ssh tecmint@169.169.169.169 -p 69
# tecmint@169.169.169.169: Permission denied (publickey). ## Used noPasswordLogin or something in sshd_config

## Test sftp connexion
# KO
# > sftp tecmint@169.169.169.169

# 🚨 big case -P for port, must be before
# > sftp -P 69 tecmint@169.169.169.169
# tecmint@169.169.169.169: Permission denied (publickey).
# Connection closed.
# Connection closed

Besoin de générer une clé privée publique + ajout au serveur, cf. rôles déjà en place (adapter)
```
