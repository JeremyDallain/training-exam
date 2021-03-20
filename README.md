# Training Symfony

### Les étapes à suivre pour récuperer et tester le projet :

* `git clone url_projet`

* Configurer la BDD dans un `.env.local`  (à créer)

  `DATABASE_URL="mysql://<identifiant>:<motDePasse>@127.0.0.1:3306/<nomDeLaBDD>?serverVersion=5.7"`

* `composer install`

* `php bin/console doctrine:database:create`

* `php bin/console doctrine:migrations:migrate`

* `php bin/console doctrine:fixtures:load`

* Pour que CKEditor fonctionne il faut également faire ces 2 commandes :

  `php bin/console ckeditor:install`
  
  `php bin/console assets:install public`
  

### à savoir :

* Les mots de passe sont toujours : ‘password’

* Les redirections vers pages 404, 403 fonctionnent en mode production

