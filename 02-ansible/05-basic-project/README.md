# Ansible project : arborescence & modules usages

All modules & docs : [Ansible module index](https://docs.ansible.com/ansible/latest/modules/modules_by_category.html)

Straight extracted from [grafikart ansible tutorial](https://www.grafikart.fr/tutoriels/ansible-753) and updated/tested following the documentation

## Package installation

[Ansible Packaging modules > apt](https://docs.ansible.com/ansible/latest/modules/apt_module.html#apt-module)

### Install 1 package

Exemple installation de git

```yaml
- name: Ubuntu 1 package installation
  apt: name=git update_cache=yes
```

### Install multiple packages through list

Exemple installation de plusieurs packages

```yaml
- name: Ubuntu multiple package installations
  apt:
    cache_valid_time: 3600
    name: ['git', 'vim', 'htop', 'zsh']
    update_cache: yes
    state: latest
```

### Clean package installation stuff

```yaml
- name: Upgrade all packages to the latest version
  apt:
    name: "*"
    state: latest

- name: Remove useless packages from the cache
  apt:
    autoclean: yes

- name: Remove dependencies that are no longer required
  apt:
    autoremove: yes
```
