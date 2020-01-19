# EnvDevHome

EnvDevHome is a home admin containerfor [EnvDev project](https://vfac.fr/projects/envdev).
The utlisation outside this project is naturally possible.

## Usage

An example of utilisation in docker compose is available in [EnvDev project](https://vfac.fr/projects/envdev)

```docker
# Web development environment
version: "3.6"
services:

  home:
    image: vfac/envdevhome
    container_name: home
    env_file: .env
    volumes:
      - ${PROJECTS_PATH}:${PROJECTS_PATH_DEST}
      - ./version:/var/www/html/envdev/version
    ports:
      - '1234:80'
    networks:
      vfac:
        ipv4_address: 172.16.238.18
```

## Screenshot

![Screenshot](https://github.com/vfalies/EnvDevHome/blob/master/doc/EnvDevHome.png)
