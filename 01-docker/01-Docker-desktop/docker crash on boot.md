# Docker desktop crash au lancement

## Sécu windows

// Toujours KO ?

Sécurité windows
    Contrôle des applications et du navigateur
        (En bas) Paramètres d'Exploit protection
            Onglet Paramètres du programme
                C:\WINDOWS\System32\vmcompute.exe > Dérouler
                    Modifier
                        Protection du flux de contrôle
                            Décocher "Remplacer les paramètres..."
                            Valider

### Relancer vmcompute

powershell
    Lancer en tant qu'admin
        > net stop vmcompute
        > net start vmcompute

## Tweak docker config

C:\Users\LE_USER\AppData\Roaming\Docker\settings.json

> "lifecycleTimeoutSeconds": 3600,

## Brute Reboot

Gestionnaire de tâches > flinguer tout ce qui commence par Docker.

Relancer Docker desktop > Il va demander pour relancer le service > ok.

## Réinstallation d'origine

En général ela fonctionne, mais il faut reconfigurer après..
