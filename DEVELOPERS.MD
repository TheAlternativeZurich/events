# Developers

## About the setup

This project uses the symfony framework for its php backend. HTML is generated service side.

backend:
- `symfony/sceleton`, `orm`, `translations`, `logging`, `annotations`, `dotenv`, `mailer`, `form`, `requirements-checker` and `templates`.
- dev dependencies include `encore`, `orm-fixtures`, `cs-fixer`, `security-checker`, `debug`, `simple-phpunit`, `brainmaestro/composer-git-hooks`.
- for deployment, `famoser/agnes` is used.

frontend:
is very minimal; boostrap as a css library, jquery for basic usability features. "feels like 2010 again"

## Developing

setup:
- `composer install` to install backend dependencies  
- `yarn install` to install frontend dependencies  

developing:
- `symfony start` to start the symfony server  
- `yarn run dev-server` to start the frontend server
- visit `localhost:8000` and login with `f@thealternative.ch`, `asdf`  
- test error templates inside TwigBundle/views by accessing `/_error/404` and `/_error/500`

clean up:
- `vendor/bin/php-cs-fixer fix` to autoformat code
- `bin/phpunit` to execute tests

database commands:
- `bin/console doctrine:migrations:diff` to generate a new migration class  
- `bin/console doctrine:migrations:migrate` to execute all migrations  
- `bin/console doctrine:fixtures:load` to load fixtures

deployment:
- `vendor/bin/agnes release` to create a new release
- `vendor/bin/agnes deploy` to deploy a release or commitish  

## Troubleshooting

### fortawesome

to be able to execute `yarn install`, you need to download the fortawesome pro fonts. 
For this, you need to setup its registry. You can do so by executing  
`npm config set "@fortawesome:registry" https://npm.fontawesome.com/`  
`npm config set "//npm.fontawesome.com/:_authToken" AUTH_TOKEN`  
You can find the auth token on the [fontawesome account page](https://fontawesome.com/account)

You need to add this auth token to all CI services too.  
[**scrutinizer**](https://scrutinizer-ci.com/g/mangelio/app/settings/build-config)
```yaml
build:
    dependencies:
        before:
            - npm config set "@fortawesome:registry" https://npm.fontawesome.com/
            - npm config set "//npm.fontawesome.com/:_authToken" FONTAWESOME_AUTH_TOKEN
```
[**travis**](https://travis-ci.org/mangelio/app/settings)  
add an environment variable called `FONTAWESOME_AUTH_TOKEN`

### Deployment

server must fulfil requirements of `composer.json`

a sample prod `.env.local` could look like this:

```dotenv
APP_ENV=prod
APP_SECRET=NEW_SECRET
MAILER_URL=smtp://info@example.com:NOT_REAL@smtp.mail.com:465/?encryption=ssl&auth_mode=login
```

### Setup ssh

`ssh-copy-id -i ~/.ssh/id_rsa.pub username@domain` to add ssh key  
`cat ~/.ssh/id_rsa.pub` to query the active ssh key  
`ssh-keygen -t rsa -b 4096 -C "username@domain" && eval $(ssh-agent -s) && ssh-add ~/.ssh/id_rsa` generate a new key & add it to ssh  
