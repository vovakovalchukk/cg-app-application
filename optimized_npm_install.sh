#!/usr/bin/env bash

#Create a folder to cache NPM
export NPM_CACHE_DIR=/tmp/npm/$HOSTNAME
mkdir -p ${NPM_CACHE_DIR}

#Expand the current users npm cache, if it exists
[ -f ${NPM_CACHE_DIR}/${USER}.npm.tar ] && tar xf ${NPM_CACHE_DIR}/${USER}.npm.tar -C ~
#Expand orders.tar if it exists
[ -f ${NPM_CACHE_DIR}/orders.tar ] && tar xf ${NPM_CACHE_DIR}/orders.tar

npm prune #orders
npm install #orders

#Create a cache of the current node modules folder, post install
tar cf ${NPM_CACHE_DIR}/orders.tar node_modules

#Create a cache of the current users .npm folder
tar cf ${NPM_CACHE_DIR}/${USER}.npm.tar -C ~ .npm