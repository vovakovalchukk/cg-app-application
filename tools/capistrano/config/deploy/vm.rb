set :stage, :vm

role :app, [
    "www-data@192.168.33."
]

module Capistrano
    module DSL
        module Paths
            def current_path
                deploy_path.join('capistrano')
            end
        end
    end
end