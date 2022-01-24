# Prerequisites
Have docker-compose installed. Yes, it's that simple.  
Don't have in installed yet, there's a detailed explanation on their
[website](https://docs.docker.com/compose/install/).

# How to use
* clone the repository `git clone git@github.com:jasperweyne/helpless-kiwi.git`
* navigate to the cloned repository
* run `docker-compose up -d` the first time this will take some time because it
  needs to build everything. Disregard any red text you might see, this is just
  part of setting up the containers.
* run `docker ps` to see if all 3 services listed below should be up.
	* kiwi_symfony-dev
	* kiwi_phpmyadmin-dev
	* kiwi_database-dev
* assuming they are, run `composer db-rebuild-dev`
* everything *should* be working now
* navigate to localhost:8000, you should be greeted with kiwi's login screen
* you can log in with either of the login credentials listed below
* when you're done you can shut the stack down with `docker-compose down`


## Exposed services and info
all ports are exposed on localhost, as of this writing we only support http.
| port | service | username| password |
| --- | --- | --- | --- |
| 8000 | kiwi | user@kiwi.nl | user |
| 8000 | kiwi | admin@kiwi.nl | admin |
| 8080 | phpmyadmin | root | root |


### Useful commands commands
Starting your stack up (the `-d` is just a flag to run it detached so it
doesn't clog up your terminal. It's optional and if you prefer you could just
run without it. Only difference is that you then need to stop it with `<C-c>`.
```bash
docker-compose up -d
```

Shutting your stack down
```bash
docker-compose down
```

Rebuilding the database for development 
```bash
composer db-rebuild-dev
```

Running the complete testsuite
```bash
composer test
```

Running the styleguide
```bash
composer fix
```
