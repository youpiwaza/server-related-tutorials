# Tester le templating Ansible, via modules_defaults

But: Permettre la réutilisation de templates, cf. ansible [module_defaults](https://docs.ansible.com/ansible/latest/user_guide/playbooks_module_defaults.html).

```bash
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/02-ansible/13-ansible-task-template-w-module-defau

# Lancer le playbook
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'
```

## Syntaxe

### Classic default & override

```yaml
# Define module defaults at any level, here at play level
module_defaults:
  debug:
    msg: Un message par defaut

# Then use either faults no override
tasks:
  # Use module defaults
  - debug:

  # Override
  - debug:
      msg: Un message overide
```

### Using template include

Included file MUST not contain module_defaults, or it won't be overriden.

```yaml
# task-template-wo-defaults.yml
- name: Task using a module with default provided by the play
  debug:

# playbook.yml
# Define module defaults at play level
module_defaults:
  debug:
    msg: Un message par defaut

# Then use either faults no override
tasks:
  # Include a task using debug, without msg defaults. Should use the play defaults # OK
  - include: task-template-wo-defaults.yml
  # ok: [127.0.0.1] => { "msg": "Le message par defaut du playbook" }

  # Include a task using debug, with msg arguments. Should override the play defaults # OK
  - include: task-template-wo-defaults.yml
    module_defaults:
      debug:
        msg: "Un message par defaut lors de l'appel de l'include qui ne contient pas de defaults"
    # ok: [127.0.0.1] => { "msg": "Un message par defaut lors de l'appel de l'include qui ne contient pas de defaults" }
```
