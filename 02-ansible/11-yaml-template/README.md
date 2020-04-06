# Tester le templating YAML

But: Permettre la réutilisation de templates, cf. [cocadmin yaml](https://youtu.be/7gmW6vxgsRQ?t=360)

```bash
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/02-ansible/11-yaml-template/ansible

# Lancer le playbook
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'
```

## Notes

La syntaxe utilisant "& << *" ne permet pas (en yaml pur ou en ansible) d'include des templates depuis d'autres fichiers.

Et comme le template doit se situer dans le même fichier l'interêt est limité.

Poursuite d'une solution de remplacement dans le procahain test à l'aide de l'[include de tâches ansible}(https://docs.ansible.com/ansible/2.3/playbooks_roles.html#task-include-files-and-encouraging-reuse).

```yaml
test_template: &my_test_template
  foo: bar

templateAndMore:
  <<: *my_test_template
  moreFoo: moreBar
```
