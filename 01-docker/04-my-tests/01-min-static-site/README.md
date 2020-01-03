# Build depuis un repo

[Doc docker build](https://docs.docker.com/engine/reference/commandline/build/#extended-description)

Création d'un [repo avec un site minimaliste](https://github.com/youpiwaza/test-min-static-site)

Création d'un nouveau build qui va copier depuis le dossier courant .

```
// Le contexte est définit sur le repo, branche master, dossier "site" (d'où la copie du dossier courant)
// KO, plus d'accès au contexte local, donc plus accès au Dockerfile4
>  docker build \
  -f Dockerfile4 \
  -t some-repo-content-nginx \
  https://github.com/youpiwaza/test-min-static-site#master:site

// NE PAS OUBLIER LE .git, on ne passe pas l'url du repo, mais l'url à cloner
>  docker build \
  -t some-repo-content-nginx \
  https://github.com/youpiwaza/test-min-static-site.git#master:site
  
> docker run -d \
  --name test-repo-nginx  \
  -p 8081:80  \
  some-repo-content-nginx
```

[localhost:8081 > Profit](http://localhost:8081/)

Cay vraiment kewl