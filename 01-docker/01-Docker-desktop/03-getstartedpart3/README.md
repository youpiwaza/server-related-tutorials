# Docker docs

Get started > Quickstart > [Part 3: Deploying to Kubernetes](https://docs.docker.com/get-started/part3/)

Objectif : Déployer sur Kubernetes

1. Déploiement sur kube
2. Vérifications > déploiement & service OK
3. Test sur localhost OK
4. Fin du déploiement

---

Test de l'utilisation de [l'interface kube](https://kubernetes.io/docs/tasks/access-application-cluster/web-ui-dashboard/)

1. Récup de l'interface sur le repo (avec la commande fournie)
2. Création d'un [login de démo](https://github.com/kubernetes/dashboard/blob/master/docs/user/access-control/creating-sample-user.md) (attention, pas cool niveau sécurité)

	- cf. dashboard-adminuser.yaml
	
Générer le bearer token

> kubectl -n kubernetes-dashboard describe secret $(kubectl -n kubernetes-dashboard get secret | sls admin-user | ForEach-Object { $_ -Split '\s+' } | Select -First 1)

// Obligation de passer par windows powershell

3. Expo de l'interface

> kubectl proxy

4. Ca roule bieng.

5. Petits tests

- Monter une autre instance de l'appli (cf. bb2.yaml) OK
- Possibilité de passer par l'interface pour lancer (a partir d'un fichier) ou scale/arreter un déploiement :)



























//