module Capistrano
    module DSL
        module Paths
            def version
                version = fetch(:version)
                return version unless version == nil

                path = fetch(:version_path, 'config/autoload/version.json')
                json = JSON.parse(
                    capture :git, :archive, "--remote=#{fetch(:repo_url)}", fetch(:branch), path, '|', :tar, '-Ox', path
                )

                version = ""
                if json.is_a?(Hash) && json.has_key?('version')
                    version = "#{json['version']}"
                end

                set(:version, version)
                return version
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