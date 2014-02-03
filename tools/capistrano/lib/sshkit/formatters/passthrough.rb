module SSHKit
    module Formatter
        class PassThrough < Pretty
            private

            def write_command(command)
                unless command.started?
                    original_output << level(command.verbosity) + uuid(command) + "Running #{c.yellow(c.bold(String(command)))} on #{c.blue(command.host.to_s)}\n"
                    if SSHKit.config.output_verbosity == Logger::DEBUG
                        original_output << level(Logger::DEBUG) + uuid(command) + "Command: #{c.blue(command.to_command)}" + "\n"
                    end
                end

                unless command.stdout.empty?
                    command.stdout.lines.each do |line|
                        original_output << level(command.verbosity) + uuid(command) + c.green("\t" + line)
                        original_output << "\n" unless line[-1] == "\n"
                    end
                end

                unless command.stderr.empty?
                    command.stderr.lines.each do |line|
                        original_output << level(command.verbosity) + uuid(command) + c.red("\t" + line)
                        original_output << "\n" unless line[-1] == "\n"
                    end
                end

                if command.finished?
                    original_output << level(command.verbosity) + uuid(command) + "Finished in #{sprintf('%5.3f seconds', command.runtime)} with exit status #{command.exit_status} (#{c.bold { command.failure? ? c.red('failed') : c.green('successful') }}).\n"
                end
            end
        end
    end
end