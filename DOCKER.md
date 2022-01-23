# Prerequisites
Have docker-compose installed. Yes, it's that simple.  
We do assume you've followed the steps to run everything as your own user. If for some reason you don't want this, just run with elevated privileges.  
Don't have it installed yet, there's a detailed explanation on their [website] (https://docs.docker.com/compose/install/).

# How to use
* clone repo
* navigate to the cloned repo
* create the `.env.local`
    ```
    SECURE_SCHEME=http
    DATABASE_URL=mysql://root:root@database/kiwi_dev
    ```
* run `make rebuild`
* run `docker ps` to see if all 3 services listed below should be up
	* kiwi_symfony-dev
	* kiwi_phpmyadmin-dev
	* kiwi_database-dev
* wait about ten seconds, the database needs to start completely.
* run `make db-build`
* everything *should* be working now
* navigate to localhost:420, you should be greeted with kiwi's login screen
* you can log in with either of the login credentials listed below


## Exposed services and info
All ports are exposed on localhost:port. As of this writing, we only support http.
| port | service | username| password |
| --- | --- | --- | --- |
| 420 | kiwi | user | user |
| 420 | kiwi | admin | admin |
| 421 | phpmyadmin | root | root |


### Docker-compose commands
Given that you can't/won't use make, you'll have to use use the direct commands.  
You'll end up with the same result, just a little more typing.  
[I'll add this later, for now just check the [Makefile](Makefile) for the commands]

### Using the Makefile
If you have access to the `make` command, life is a lot easier.  
Here are some convenience methods listed for your convenience.  
It's as easy as running `make up` every time you want to start developing, and `make down` when you're done/

| Command | Description |
| --- | :-- |
| `make rebuild` | Basically, this is a one time thing, you use it to build the entire stack. As long as the docker files don't change you shouldn't have to run this again. |
| `make db-build` | This builds the database based on the migrations. Running this again will wipe your entire database so you can start fresh. It also configures a default admin (username: admin@kiwi.nl, password: admin)  and a default user (username: user@kiwi.nl, password: user). |
| `make up` | Starts up your stack. |
| `make down` | Shuts down your stack. |
| `make test` | Run the complete test suite |


