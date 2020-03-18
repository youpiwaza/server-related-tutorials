# Ansible project : arborescence

- /defaults         // ?
- /handlers         // Event driven recipies, called with notify
- /roles            // Tasks refacto, same arbo
  - /wtv
    - /defaults
    - /handlers
    - /tasks
      - main.yml
      - wtv.yml
    - /templates
    - /vars
- /templates        // File templating, can use vars & conditionnal
  - wtv.j2
- /vars             // Object-like data storage
  - wtv.yml
- hosts             // What IP to apply cookbooks to
- playbook.yml      // Main recipy, call roles
