CREATE OR REPLACE FUNCTION some_func() RETURNS void AS
$$
BEGIN
    CREATE EXTENSION dblink;
   IF NOT EXISTS (
      SELECT
      FROM   pg_catalog.pg_roles
      WHERE  rolname = 'masterhome') THEN

      CREATE ROLE masterhome WITH SUPERUSER LOGIN PASSWORD 'masterhome';
   END IF;
   IF EXISTS (SELECT FROM pg_database WHERE datname = 'masterhome') THEN
      RAISE NOTICE 'Database already exists';
   ELSE
      PERFORM dblink_exec('dbname=' || current_database()
                        , 'CREATE DATABASE masterhome');
   END IF;
   GRANT ALL PRIVILEGES ON DATABASE masterhome TO masterhome;
END
$$ LANGUAGE plpgsql VOLATILE;

SELECT some_func();

DROP FUNCTION some_func();