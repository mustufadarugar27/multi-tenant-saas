-- Grant CREATE DATABASE and full privileges to the app user so stancl/tenancy
-- can create per-tenant databases at runtime.
GRANT ALL PRIVILEGES ON *.* TO 'saas_user'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
