* clone it into your root folder
* rename the folder to toggl_docker (zyz abbreviation of your project)
* in rest of this file replace xyz with that abbreviation
* cd toggl_docker
* git clone git@github.com:aligent/LAMP-docker.git
* run docker using `toggl_docker/cmd/up.sh`
* copy the contents/target of `local.xml` to `local.xmldocker` 
* symlink `local.xmldocker` to `local.xml` (`cd app/etc` and `ln -s local.xmldocker local.xml`)
* get list of ips running `toggl_docker/cmd/ips.sh`
* edit your `/etc/hosts` file for local domain `local.xyzg.dev`
* optionally use `docker.xyz-db.dev` and `docker.xyz-redis.dev` if you want to run cli commands from host machine

## Mac

* install docker app https://store.docker.com/editions/community/docker-ce-desktop-mac
* make sure php is installed (brew is a quick way to install) 
* install gnu utilities `brew install coreutils`
* in your `~/.bashrc` or `~/.zshrc` add the path `export PATH="/usr/local/opt/coreutils/libexec/gnubin:$PATH"`
* start a new shell
* docker networking does not work out of the box. So recommend port forwarding
* in `toggl_docker/docker-compose.override.yml` php section add:
```
ports:
    - 80:80
```