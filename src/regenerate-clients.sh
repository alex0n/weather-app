#!/bin/bash
APP_ROOT_PATH="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null && pwd )"

cd $APP_ROOT_PATH || exit
rm -rf library/weather-client
docker run -v `pwd`:/defs namely/protoc-all -f protos/weather.proto -l php -o library/weather-client/src
