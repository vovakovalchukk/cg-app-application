set :stage, :live

server 'www-data@109.169.50.111', :roles => [:app, :php71]
server 'www-data@109.169.61.125', :roles => [:app, :php71]
server 'www-data@94.130.165.109', :roles => [:app, :php71]
