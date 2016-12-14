set :stage, :vm

server 'www-data@192.168.33.53', :roles => [:app, :php71]

module Capistrano
    module DSL
        module Paths
            def current_path
                deploy_path.join('capistrano')
            end
        end
    end
end
