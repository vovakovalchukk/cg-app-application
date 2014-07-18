require_relative '../tasks/git'

module Capistrano
    module DSL
        module Paths
            def version
                if !fetch(:version) && scm == :git
                    Rake::Task['git:version'].invoke
                end

                return fetch(:version)
            end

            def shared_path
                paths = ['shared']

                version = version()
                if version
                    paths.unshift('version', version)
                end

                return deploy_path.join(*paths)
            end
        end
    end
end