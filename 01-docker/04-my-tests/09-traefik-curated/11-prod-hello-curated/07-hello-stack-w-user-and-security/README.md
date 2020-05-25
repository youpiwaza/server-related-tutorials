# tutum/helloworld custom user fix

Based on 06 hellos working WITH user & traefik wo security.

Goal : make it work with traefik security (fine tuning).

Test with previous config full security > OK

- Pay close attention to named volume rights, just re-created it (was chowned root by previous tests..)

## Note

En cas de soucis avec cette erreur syslog lors du lancement d'une stack :

```bash
fatal task error" error="task: non-zero exit (78)" module=node/agent/taskmanager node.id=5fl4gm3ngog3m8ezddov93yqc service.id=qfe2dcwurt6xhyx8sforrmbqc task.id=wodsd2hf7eehiai1cjx6954d9
```

Lancer la stack depuis docker-compose, en intéractif : certaines erreurs lors du lancement ne sont pas répercutées via la stack :

```bash
# Nah
> docker stack deploy -c hello.yml hello
## Logs
# fatal task error" error="task: non-zero exit (78)" : ¯\_(ツ)_/¯

# Yeph
> docker-compose -f hello.yml up
## Logs
# Starting tests_helloworld_1 ...done
# Attaching to tests_helloworld_1
# helloworld_1  | [25-May-2020 12:52:45] NOTICE: [pool www] 'user' directive is ignored when FPM is not running as root
# helloworld_1  | [25-May-2020 12:52:45] NOTICE: [pool www] 'group' directive is ignored when FPM is not running as root
# helloworld_1  | tail: can't open '/var/log/nginx/access.log': Permission denied
# helloworld_1  | nginx: [alert] could not open error log file: open() "/var/log/nginx/error.log" failed (13: Permission denied)
# helloworld_1  | 2020/05/25 12:52:45 [emerg] 1#0: open() "/var/log/nginx/error.log" failed (13: Permission denied)
```
