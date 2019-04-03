Installation
============

1. Fill in required data in `.env.dist` file, and create `.env` from it.
2. `sudo docker-compose up`.

After that, wait for at least 2 minutes for the service to gather some data. After that the data will be loaded from the storage, to avoid hitting API limits.

3. Access endpoints:
 * http://localhost:8080/api/list - for list of available harbors.
 * http://localhost:8080/api/weather-info/{uuid} - for weather info of a particular harbor.
