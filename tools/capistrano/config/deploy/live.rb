set :stage, :live

server 'www-data@88.99.87.13', :roles => [:app, :php71]
server 'www-data@159.69.53.71', :roles => [:app, :php71]
