#Инструкция по разворачиванию

Создать базу [здесь](https://dev.mysql.com/downloads/mysql/) и настроить [тут](https://dev.mysql.com/downloads/workbench/)

Создать файл `.env.local` (образец `.env`.)


В нем прописать:
 - строку подключения к БД `DATABASE_URL`
 - Ключ для шифрования токенов `JWT_PASSPHRASE`

Собрать докер

```bash
docker build
```

Запустить приложение в контейнере:
```bash
docker-compose up -d
```
Приложeние будет доступно по адресу `127.0.0.1:81`.

Войти в контейнер:
```bash
docker exec -it <ID контейнера> /bin/bash
```

Чтобы работать с MySQL которая находится на той же машине, 
что и контейнер с PHP необходимо в строке подключения указать хост `host.docker.internal`

#Swagger

После запуска контейнера, по адресу `127.0.0.1:81/api/doc`
можно найти Swagger документацию к API

