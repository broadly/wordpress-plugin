# development

Docker provides an easy way to launch a wordpress instance. 

* `brew cask install docker`
* Go  through the docker install process by clicking on the whale icon in the menu bar. 

* clone our plugin: `git@github.com:broadly/wordpress-plugin.git`
* create a folder to store you wordpress stuff, and set it up: 
* `mkdir wordpress`
* `cd wordpress`
* `cp ../wordpress-plugin/docker-compose.yml .`
* `mkdir data`
* `ln -s ../wordpress-plugin broadly-plugin`

* Start wordpress: `docker-compose up -d && docker-compose logs -f wordpress`

You wordpress will be available under: `http://localhost:8080/`
