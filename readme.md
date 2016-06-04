# Webapp Deployer

A pretty simple continious integration webapp deployer that I use on one of my servers.

## Installation

1. **Clone Repository**
Clone the repository to the server where you want deployments to be made.
`git clone https://github.com/aaronheath/webapp-deployer.git`
2. **Install Composer Packages**
We now need to install the composer packages and initilise the project.
`composer run-script setup`
3. **Cron Job**
Add the Laravel cron job
`(crontab -l 2>/dev/null; echo "* * * * * run-one php /path/to/webapp-deployer queue:work --daemon --sleep=5 --delay=60 --tries=60") | crontab -`

## Add Repository To Deploy

We'll use the built in CLI tool to add a repsoitory to deploy.

`php artisan repo:add NAME BRANCH TOKEN JOB`

| Arguement  | Description                              | Example             |
| ---        | ---                                      | ---                 |
| NAME       | Name of the repository to deploy         | aaronheath/afl-2016 |
| BRANCH     | Name of the branch to deploy             | master              |
| TOKEN      | Travis CI account token                  | ABCD1234            |
| JOB        | Laravel Job to dispatch to deploy webapp | DeployerJob         |

## View Repositories

View a list of repositories that are known to the deployer.

`php artisan repo:view`

## License

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).