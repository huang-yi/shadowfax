# Supervisor配置

在生产环境中，可以使用Supervisor来管理你的Shadowfax进程，配置参考如下：

```ini
[program:shadowfax]
process_name=%(program_name)s
directory=/path/to/project
command=php shadowfax start
autostart=true
autorestart=true
user=www
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/shadowfax.log
```
