set :application, 'app'
set :deploy_to, "/var/www/#{fetch(:application)}"
set :keep_releases, 5

set :scm, :git
set :repo_url, 'git@bitbucket.org:channelgrabber/cg-app-application.git'
ask :branch, proc { `git rev-parse --abbrev-ref HEAD`.chomp }
set :deploy_via, :remote_cache

set :log_level, :debug

set :use_sudo, false
set :pty, true
set :ssh_options, {
    :forward_agent => true
}

set :linked_files, []
set :linked_dirs, []

namespace :deploy do
    after :publishing, :restart

    desc 'Restart application'
    task :restart do
        on roles(:php5), in: :parallel do
            execute :sudo, :restart, "php5-fpm"
        end
        on roles(:php71), in: :parallel do
            execute :sudo, :service, "php7.1-fpm", :reload
        end
    end

    after :finishing, 'deploy:cleanup'
end
