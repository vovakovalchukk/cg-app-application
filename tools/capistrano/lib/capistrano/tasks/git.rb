require 'json'

namespace :git do
    task :version => :'git:update' do
        path = fetch(:version_path, 'config/autoload/version.json')
        on roles :all do
            within repo_path do
                json = JSON.parse(capture(:git, :show, "#{fetch(:branch)}:#{path}", "|", :col, "-b"))
                if json.is_a?(Hash) && json.has_key?('version')
                    set(:version, json['version'])
                end
            end
        end
    end
end