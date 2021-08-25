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
    - Also note that some/most instructions just **WON'T WORK FOR NO REASON** when included, like `ChrootDirectory` && `ForceCommand`
  - They are several commands to restart the ssh service, depending on OS & stuff. Try & note the one working before loosing time.
  - `sshd_config` **doesn't overload configuration** : first instruction is law !
    - If you want to make includes, make them at the beginning of the file, **else they will be ignored** if instruction is already declared by default.
    - Same if no includes. Comment default or declare beforehand.
    - It also has a real problem with same case declarations: don't user `Match User SAME_GUY` several times or some might be ignored. Same for `Match Group`
      - Can also error if `Subsystem sftp  internal-sftp` the Subsystem instruction is declared twice..
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
