# Leadwire Portal Application


1- [Platform Requirements](doc/SetupRequirements.md)

2- [Development environment](doc/Development.md)

3- [Deploying in production](doc/Deployment.md)



### Notes

- In case of changing parameters such as:
    * Adding user/password to mongodb access
    * Changing IPs of Ldap / elastic VPS

    Update the right parameter in app/config/parameters.yml then rebuild the cache:
    ```sh
    $ app/console cache:clear --env=ENV # Where ENV is "dev" on development environment and "prod" on production servers
    ```

- Stripe account should be unique for each instance. (i.e. Do not share stripe account between test and prod.)

- Stripe account should have a valid phone number and e-mail before going to test and prod.
