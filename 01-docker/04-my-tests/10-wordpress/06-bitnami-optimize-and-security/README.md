# Lel

bitnamis recommandations : install 2 plugins

- W3 total cahce
- WordFence

1. ✅ [~Optimize WordPress](https://docs.bitnami.com/bch/apps/wordpress/troubleshooting/optimize-bitnami-wordpress/)
   1. Activate W3 Total Cache (might need to chmod wp-config.php 660 to apply settings, then back to 440)
   2. [Pagespeed verifications](https://developers.google.com/speed/pagespeed/insights/?hl=fr&url=https%3A%2F%2Ftest-wordpress.masamune.fr%2F)
      1. Avant opti: 1st: 500-1500ms, Suivants: 240ms. Pagespeed 93, ~indice de vitesse 2.8s
      2. Après opti: 1st: 460-480ms, suivants: 150-170. Pagespeed 99, ~indice de vitesse 1.5s
   3. Note: police not embedded (theme wp 2020) qui ralentissent, il existe des articles dédiés pour retirer leurs chargements..
      1. ~~Plugin OMGF / Optimize My Google Font > Télécharge en local et fout en cache~~
         1. Nouvelle technos font vars ou chp > [besoin d'être viré](https://ryandaniels.ca/blog/set-up-customize-wordpress-twenty-twenty/#Delete-embedded-fonts)
      2. Après opti: 1st: 420-430ms, suivants: 180. Pagespeed 100, ~indice de vitesse 1.3s
   4. Voir pour les plugins qui passent le site en statique
2. ✅  [Secure WordPress](https://docs.bitnami.com/bch/apps/wordpress/troubleshooting/enforce-security/)
   1. Install WordFence security plugin x') > Scan > Start scan