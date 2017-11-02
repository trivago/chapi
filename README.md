# chapi [![Build Status](https://travis-ci.org/trivago/chapi.svg?branch=master)](http://travis-ci.org/trivago/chapi) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/trivago/chapi/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/trivago/chapi/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/trivago/chapi/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/trivago/chapi/?branch=master)
Chronos and marathon api client for your console. 

## Description
Manage your [Chronos](https://github.com/mesos/chronos) and [Marathon](https://github.com/mesosphere/marathon) tasks like a git repository in your console:

* Prepare your tasks before you send them to Remote
* Manage a separate git repository for task backups and history
* Quickly check your tasks' status

It is possible to use either of the systems independently or both at once.

## Requirements

* php >= 5.6

## Installation / Configuration

To install chapi you can download the latest [release](https://github.com/trivago/chapi/releases) or clone this repository.
If you clone the repository you need to run a `composer install` to install all necessary dependencies:

```Shell
composer install
``` 

Before you use chapi the first time you have to setup your chronos api url and the path to your local task repository.
You can use the `configure` command to setup your global settings:

```Shell
bin/chapi configure 
```

### Configuration file locations
Chapi attempts to read a global and a local configuration file, at least one of which must exist. 
Should both files exist, values found in the local configuration override those defined in the global one.

The global configuration file's location is

- `~/.chapi/.chapiconfig`
  if $CHAPI_HOME is not set

- `${CHAPI_HOME}/.chapiconfig`, 
  if $CHAPI_HOME is set

The local configuration searched for in your current working directory.

- `${PWD}/.chapiconfig`,

### Profiles
You can switch between different profiles by using the global
`--profile[=PROFILE]` option.

If no profile is set chapi will use `default` as active profile.


### Configuration file contents
Both configuration files are in the [yaml](http://yaml.org/) format. 

The configuration is located in the `profiles` property. 
There you will find the `parameters` for each set profile.

`default` will be used if you don't use a explicit profile.

```yaml
profiles:
    default:
        parameters:
            chronos_url: http://your.chronos.url:chronos_api_port/
            chronos_http_username: username
            chronos_http_password: password
            repository_dir: /path/to/your/local/task/repository
        
            marathon_url: http://your.marathon.url:marathon_api_port/
            marathon_http_username: username
            marathon_http_password: password
            repository_dir_marathon: /path/to/your/local/marathon/apps/repository
        
            cache_dir: /path/to/chapi/cache/dir
            
        ignore:
          - *-dev
          - !my-active-job-dev
    develop:
       parameters:
           chronos_url: http://your.chronos.url:chronos_api_port/
           chronos_http_username: ''
           chronos_http_password: ''
           repository_dir: /path/to/your/local/task/repository
      
           marathon_url: ''
           marathon_http_username: ''
           marathon_http_password: ''
           repository_dir_marathon: ''
      
           cache_dir: /path/to/chapi/cache/dir_dev
```

#### `chronos_url`
The chronos api url (inclusive port). See also [configure command](#configure) option `-u`.

#### `chronos_http_username`
The chronos http username. See also [configure command](#configure) option `-un`.

Necessary if the setting `--http_credentials` is activated in your Chronos instance.

#### `chronos_http_password`
The chronos http password. See also [configure command](#configure) option `-un`.

Necessary if the setting `--http_credentials` is activated in your Chronos instance.

#### `repository_dir`
Root path to your job files. See also [configure command](#configure) option `-r`.

#### `marathon_url`
The marathon api url (inclusive port). See also [configure command](#configure) option `-mu`.

#### `marathon_http_username`
The marathon http username. See also [configure command](#configure) option `-mun`.

#### `marathon_http_password`
The marathon http password. See also [configure command](#configure) option `-mp`.

#### `repository_dir_marathon`
Root path to your tasks folder. See also [configure command](#configure) option `-mr`.

#### `cache_dir`
Path to cache directory. See also [configure command](#configure) option `-d`.

### Update notes

#### v0.9.0

Because of the new marathon support with v0.9.0 you need to update your configurations.
The `parameters.yml` structure changed and renamed to `.chapiconfig`.

You need to recreate your config settings: 

```sh
bin/chapi configure
```

### Disabling services

To disable Chronos support and only use Marathon, set all the 
Chronos parameters to `'''`:

```yaml
profiles:
    default:
        parameters:
            # [....]
            chronos_url: ''
            chronos_http_username: ''
            chronos_http_password: ''
            repository_dir: ''
```

## Ignoring jobs

You can specify pattern for each profile in your `.chapiconfig` file(s) and add a file to your job repositories to untrack jobs you want chapi to ignore.

* The matching pattern according to the rules used by the libc [glob()](https://en.wikipedia.org/wiki/Glob_(programming)) function, which is similar to the rules used by common shells.
* An optional prefix "`!`" which negates the pattern; any matching job excluded by a previous pattern will become included again.

Example content:
```yaml
profiles:
    default:
        ignore:
          - *-dev
          - !my-active-job-dev
    dev:
        ignore:
          - "*"
          - "!*-dev"
```

## Usage

### list
Display your tasks and filter them by failed

```Shell
bin/chapi list [options] 
```

    Options:
      -f, --onlyFailed      Display only failed jobs
      -d, --onlyDisabled    Display only disabled jobs
      --profile[=PROFILE]  Use a specific profile from your config file.

### info
Display your task information from remote system

```Shell
bin/chapi info <jobName> 
```

    Arguments:
      jobName               selected job
      
    Options:
      --profile[=PROFILE]  Use a specific profile from your config file.
      
The task name in case of marathon would be the full id for the task.

### status
Show the working tree status

```Shell
bin/chapi status
```

    Options:
      --profile[=PROFILE]  Use a specific profile from your config file.

### diff
Show changes between tasks and working tree, etc

```Shell
bin/chapi diff [<jobName>]
```

    Arguments:
      jobName               Show changes for specific job
      
    Options:
      --profile[=PROFILE]  Use a specific profile from your config file.

### add
Add task contents to the index

```Shell
bin/chapi add [<jobnames>]...
```

    Arguments:
      jobnames              Jobs to add to the index
      
    Options:
      --profile[=PROFILE]  Use a specific profile from your config file.
 
### reset 
Remove tasks from the index

```Shell
bin/chapi reset [<jobnames>]...
```

    Arguments:
      jobnames              Jobs to add to the index
      
    Options:
      --profile[=PROFILE]  Use a specific profile from your config file.

### pull
Pull tasks from remote system and add them to local repository

```Shell
bin/chapi pull [options] [--] [<jobnames>]...
```

    Arguments:
      jobnames              Jobnames to pull
    
    Options:
      -f, --force           Force to overwrite local jobs 
      --profile[=PROFILE]  Use a specific profile from your config file.

### commit
Submit changes to chronos or marathon

```Shell
bin/chapi commit
```

    Options:
      --profile[=PROFILE]  Use a specific profile from your config file.

### scheduling
Display upcoming jobs in a specified timeframe

```Shell
bin/chapi scheduling [options]
```

    Options:
      -s, --starttime[=STARTTIME]  Start time to display the jobs
      -e, --endtime[=ENDTIME]      End time to display the jobs
      --profile[=PROFILE]  Use a specific profile from your config file.
      
**Note: Not applicable to marathon**

### configure
Configure application and add necessary configs

```Shell
bin/chapi configure
```

    Options:
      -u, --chronos_url[=CHRONOS_URL]        The chronos url (inclusive port)
      -un, --chronos_http_username[=CHRONOS_HTTP_USERNAME]  The chronos username (HTTP credentials) [default: ""]
      -p, --chronos_http_password[=CHRONOS_HTTP_PASSWORD]   The chronos password (HTTP credentials) [default: ""]
      -d, --cache_dir[=CACHE_DIR]            Path to cache directory
      -r, --repository_dir[=REPOSITORY_DIR]  Root path to your job files
      --profile[=PROFILE]  Use a specific profile from your config file.
      
### validate
Validate local jobs

```Shell
bin/chapi validate [<jobmames>]...
```

    Arguments:
      jobmames              Jobs to validate
      
    Options:
      --profile[=PROFILE]  Use a specific profile from your config file.

## Example workflows

### Add a new job to chronos
A typical workflow to add a new cronjob to your Chronos server via chapi can be:

1. A pull request for a new cronjob (json definition) comes in a git repository (created by a colleague of you)
2. Accept the pull request and switch to your local clone via `cd ~/my/clone`
3. Update your local repository via `git pull`
4. Check the current status via `chapi status`
5. Validate everything via `chapi validate .`
6. Add the new job via `chapi add jobXy`
7. Apply the changes and update the Chronos server via `chapi commit`

### Move jobs from chronos cluster A to cluster B successively
Chapi is able to support you if you need to move your tasks from a chronos cluster to another one.

1. Setup your normal chapi config and local job repository

2. Create a new empty folder which stands for your second chronos cluster repository:
```Shell
mkdir clusterBjobs
```

3. Add a local `.chapiconfig` file (see [configuration](#installation_configuration)) to the new folder:
```Shell
touch clusterBjobs/.chapiconfig
```

4. Edit the file and add the `chronos_url` and `repository_dir` parameters for your second chronos cluster:
```yml
parameters:
    chronos_url: http://your.second.chronos.url:chronos_api_port/
    repository_dir: /path/to/clusterBjobs
```

5. Open a second console and switch to the new folder where the `.chapiconfig` file is located:
```Shell
cd clusterBjobs
```

6. Now you are able to move job for job from your normal repository to the new repository:
```Shell
mv clusterAjobs/jobXy.json clusterBjobs/jobXy.json
```

7. Chapi in console 1 will delete the jobs from the "old" cluster and chapi in the second console 2 will add the moved jobs to the new one.


## Supported commands for either system
|            | chronos            | marathon           |
|------------|--------------------|--------------------|
| list       | :white_check_mark: | :white_check_mark: |
| info       | :white_check_mark: | :white_check_mark: |
| status     | :white_check_mark: | :white_check_mark: |
| diff       | :white_check_mark: | :white_check_mark: |
| add        | :white_check_mark: | :white_check_mark: |
| reset      | :white_check_mark: | :white_check_mark: |
| pull       | :white_check_mark: | :white_check_mark: |
| commit     | :white_check_mark: | :white_check_mark: |
| scheduling | :white_check_mark: | n.a.               |
| configure  | :white_check_mark: | :white_check_mark: |
| validate   | :white_check_mark: |                    |


## Special cases in marathon:
* Pulling a task from marathon will dump the json object with default values.
    This is the choice for now because calling marathon for app info sends
    the default values set as well. Logic to check this could be implemented in future.
* Group apps cannot be pulled from marathon in the configuration with which
    it was posted. This is because once an app is in marathon, the group
    specific config is lost.
* The marathon App id should be be prefixed by `/`.
    This is a good practice. The reason this needs to be forced is because
    the local configuration with `myapp` will be seen in marathon as `/myapp`
    and by chapi as two different apps.

If you find any further issues or edge case, please create an issue.


## Supported Chronos versions
* v2.3
* v2.4

## Docker

You can run chapi also in a docker container.
You will find the laster releases under [dockerhub](https://hub.docker.com/r/msiebeneicher/chapi-client/).

### Prepare a config file for docker

Create a `.chapiconfig_docker` file with the following content:

```yaml
profiles:
    default:
        parameters:
            cache_dir: /root/.chapi/cache
            chronos_url: 'http://your.chronos.url:4400/'
            chronos_http_username: YOUR_CHRONOS_USER
            chronos_http_password: YOUR_CHRONOS_PASS
            repository_dir: /chronos-jobs
            marathon_url: 'http://your.marathon.url:8080/'
            marathon_http_username: YOUR_MARATHON_USER
            marathon_http_password: YOUR_MARATHON_PASS
            repository_dir_marathon: /marathon-jobs
```

### Run docker

```sh
docker pull msiebeneicher/chapi-client:latest

docker run -it \
    -v ~/.chapiconfig_docker:/root/.chapi/.chapiconfig \
    -v /your/local/checkout/chronos-jobs:/chronos-jobs \
    -v /your/local/checkout/marathon-jobs:/marathon-jobs \
    msiebeneicher/chapi-client:latest <COMMAND>
```

### Run docker for development

```sh
docker pull msiebeneicher/chapi-client:latest

docker run -it \
    -v ~/.chapiconfig_docker:/root/.chapi/.chapiconfig_docker \
    -v /your/local/checkout/chronos-jobs:/chronos-jobs \
    -v /your/local/checkout/marathon-jobs:/marathon-jobs \
    -v /your/local/checkout/chapi:/chapi \
    --entrypoint /bin/bash \
    msiebeneicher/chapi-client:latest
```

## Todos:

### Marathon
- [ ] The validate command for marathon is not yet implemented.
- [ ] The list command has status set as `ok` for marathon entities. This could show the last status of the app.
