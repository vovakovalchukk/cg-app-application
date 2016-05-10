set :stage, :vm

role :app, [
    "www-data@192.168.33.53"
]

set :log_level, :debug

module Capistrano
    module DSL
        module Paths
            def current_path
                deploy_path.join('capistrano')
            end
        end
    end
end
