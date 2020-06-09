# KO / Create a git repo and upload a file

But: Store passwords in private repos.

```bash
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/02-ansible/15-git-create-repo/ansible

# Lancer le playbook
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'
```

🔍Docs:

1. ✅ [~Ansible git](https://docs.ansible.com/ansible/latest/modules/git_module.html)
2. ✅💚 [SO > Use case](https://stackoverflow.com/questions/39204455/ansible-how-to-init-git-bare-repository-and-clone-it-to-the-same-machine)
3. [Ansible example push playbook](https://github.com/willtome/ansible-git/blob/master/tasks/push.yml)
   1. Looks like Ansible has no module, so we'll use git through shell/command

LATER: To much time or needs stuff installation ([hub](https://github.com/github/hub#installation)).

Manual file upload for now, ansible will locally generate the file with ids & pass.

## KO / Create a github repo from CLI

```bash
## ~KO
# touch README.md
# git init
# git add README.md
# git commit -m "first commit"
# git remote add origin git@github.com:youpiwaza/test-git-create-project.git
# git push -u origin master

## ~OK, probably needs already logged in user
# curl -u 'youpiwaza' https://api.github.com/user/repos -d '{"name":"test-git-create-project"}'
```
