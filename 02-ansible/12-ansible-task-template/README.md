# Tester le templating Ansible

But: Permettre la réutilisation de templates, cf. l'[include de tâches ansible](https://docs.ansible.com/ansible/2.3/playbooks_roles.html#task-include-files-and-encouraging-reuse).

```bash
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/02-ansible/12-ansible-task-template/ansible

# Lancer le playbook
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'
```

## Syntaxe

### Populate values

```yaml
# playbook.yml
- include: task-template.yml
  vars:
    the_var: Hello

# task-template.yml
- name: 'Templatated task to run with var "{{ the_var }}"'
  debug:
    msg: '{{ the_var }}'
```

### Populate arguments

Is kinda [unsafe](https://docs.ansible.com/ansible/devel/reference_appendices/faq.html#argsplat-unsafe).

Be sure to declare vars in the play, or [according to the priority](https://docs.ansible.com/ansible/devel/user_guide/playbooks_variables.html#ansible-variable-precedence).

```yaml
# playbook.yml
- include: task-template-plus.yml
  vars:
    task_arguments:
      msg: Goodbye

# task-template-plus.yml
- name: 'Templatated task to run with var containing tasks arguments "{{ task_arguments }}"'
  debug:
    '{{ task_arguments }}'
```

### Ovveride arguments

KO. Next tests will use ansible [module_defaults](https://docs.ansible.com/ansible/latest/user_guide/playbooks_module_defaults.html).

```yaml
# playbook.yml
- include: task-template-override.yml
  vars:
    task_arguments:
      msg: Not the original message

# task-template-override.yml
- name: 'Templatated task with orginal arg to override with var containing same tasks arguments "{{ task_arguments }}"'
  debug:
    # Nope
    msg: Original message
    '{{ task_arguments }}'
```
