set :application, ''
set :deploy_to, "/var/www/#{fetch(:application)}"
set :keep_releases, 5

set :scm, :git
set :repo_url, ''
ask :branch, proc { `git rev-parse --abbrev-ref HEAD`.chomp }
set :deploy_via, :remote_cache

set :format, :pretty
set :log_level, :info

set :use_sudo, false
set :pty, true
set :ssh_options, {
    :forward_agent => true
}

set :linked_files, []
set :linked_dirs, []

namespace :deploy do
    desc 'Restart application'
    task :restart do
        on roles(:all), in: :parallel do
            execute :sudo, :restart, "php5-fpm"
        end
    end

    after :finishing, 'deploy:cleanup'
end
