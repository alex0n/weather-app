## Task description
Language: PHP 7+

Framework, libraries, database: on your choice
Time: ~3-4 hours, it's ok if code won't be perfect or even unfinished.
TODO comments are highly welcome.

We need scientific application, which stores weather data from two weather stations.
Both stations send you statistic once per 24 hours, ignore question of delivery - let's say files appear in ./data directory once per 24 hours.
Each report should contain 24 records, for each hour, with weather details.

Station 1: you receive data in .json files, file name YYYY-DD-MM, data:

Time: unix timestamp
Temperature: freight
Humidity: percent
Rain, inches per hour
Wind, miles per hour
Battery level, percent

Station 2: you receive data in .csv files, file name DD-MM-YYYY, data:

Time: dd:mm:yyyy, hh:mm:ss
Temperature: celsius
Humidity: percent
Wind, km per hour
Rain, mm per hour
Light, lux
Battery level, enum (low, medium, high, full)

Application should be able to load this data, and provide api for internal clients (i.e. no exposure to the world) to answer following questions:

1) Information about temperature, humidity and wind from station 1, for given date and time
2) Information about temperature, humidity and wind from station 2, for given date and time
3) Averaged information about temperature, humidity and wind from both stations, for given date
4) Last available date/time

Application should write it's activity to the log file(s), logging level should be enough to understand app activities

Application should be done with those in mind (development not required, but architecture should mind this):

1) Multiple stations of both types can be added later
2) API could be exposed to the world for external clients, i.e. authorization will require
3) Weather stations in future could be located in different cities, and averaged info will be required for defined city
4) API could be extended no return more data positions

## Time spent
~ 8h

## Tech stack
- php 8
- symfony components
- RoadRunner php gRPC server
- Protobuf protocol for communication
- InfluxDB time-series database
- docker

## Prerequisites
- make sure docker service installed
- make sure port 8086 is not taken

## Installation instructions
1. Execute command: `docker-compose up -d`
Note: container building process is quite time-consuming, gprc extension installation takes a lot of time
2. Execute command: `docker exec -ti weather-app-php composer install -o`

### How to use app
1. Start services with docker compose if not started
`docker-compose up -d`
2. Put weather station reports in `./data` OR Generate fake weather station reports: 
`docker exec -ti weather-app-php php bin/console app:generate-ws-report --dt=2022-03-01`
note: reports generator creates also invalid report with `i` prefix in filename
3. (optional) Generate fresh grpc client for "frontend": 
`./src/regenerate-clients.sh`
note: client generated in `./src/library` folder based on "proto" in `./src/protos/weather.proto` using protocol buffer language
4. Process latest station reports: 
`docker exec -ti weather-app-php php bin/console app:process-ws-report`
5. (optional) Check reports processed and moved into `./data/processed` and `./data/failed` folders
6. Start gRPC API server:
`docker exec -ti weather-app-php ./rr serve`
7. In another console instance run script "emulate" frontend and get data from API:
`docker exec -ti weather-app-php php get-weather.php`
8. check logs:
`ls -la logs/`
- `app-????-??-??.log` is an application log
- `roadrunner.log` is grpc server log

## DB admin cli UI
1. Open `http://localhost:8086/`
2. Enter username: `root`
3. Enter password: `rootroot`
4. Go to Data->Buckets->"weather" bucket and explore data

## Notes
1. Alternative options for storage: TimescaleDB, ClickHouse
2. Weather station fake generator written quickly (and has quite dirty code) just for test purposes
3. In real world this project will be separated in multiple repos/packages: 
- docker env setup
- "backend" with grpc server and processing commands
- weather api client - generated client for backend
- "frontend" app
3. WeatherConditions\ItemList and WeatherConditions\Item acts as DTO better move to client package
4. If weather stations will be located in different cities, we will add 'location' property to api, models and will
store and query 'location' tag in influxdb
5. Report "types" were not implemented, for now there are only 'processors'. It is possible to resolve type while processing
report and have config where each type has settings which processor to use and which fields to parse (smth like that)
6. For now too much data is logged, some logging can be moved to `debug` level  
7. Probably better to use spiral framework ad it has everything required out of the box

## Todo
- add roadrunner startup to Dockerfile
- use bcmath for calculations
- cover broken report (broken file or broken record) case
- move InfluxDB measurement name into config
- cleanup services definitions
- implement request id, pass it in request/responses and modify monolog log message format so it contains request id
- improve naming and project/classes file structure 
- setup channels for monolog (add console channel)
- investigate if there are benefits having separate measurements per weather station
- separate model for each weather station data structure
- implement config for weather stations, where we can define report file path, filename pattern, processor, station id 
and other related settings
- install dotenv and move some configs to env vars
- add healthchecks for containers/services
- setup graylog/elk container and configure monolog to send logs there
- exclude rr binary from repo, install during container build process
- write unit tests
- review logging
- add logging level setting to .env
- move composer install into container build process

## Questions
- what precision should be used for decimal values?
