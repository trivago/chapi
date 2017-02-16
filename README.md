# chapi [![Build Status](https://travis-ci.org/msiebeneicher/chapi.svg?branch=master)](http://travis-ci.org/msiebeneicher/chapi) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/msiebeneicher/chapi/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/msiebeneicher/chapi/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/msiebeneicher/chapi/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/msiebeneicher/chapi/?branch=master)
Chronos api client for your console

## Description
Manage your [Chronos](https://github.com/mesos/chronos) and [Marathon](https://github.com/mesosphere/marathon) jobs like a git repository on your console:

* Prepare your jobs before you send them to Remote
* Manage a separate git repository for job backups and history
* Fast check of your jobs status

## Installation / Configuration

To install chapi you can download the latest [release](https://github.com/msiebeneicher/chapi/releases) or clone this repository.
If you clone the repository you need to run a `composer install` to install all necessary dependencies:

```Shell
composer install
``` 

Before you use chapi the first time you have to setup your chronos api url and the path to your local job repository.
You can use the `configure` command to setup your global settings:

```Shell
bin/chapi configure 
```

Chapi will accept your global settings, which are located in your home directory, and/or a local `.chapiconfig` in your working directory.
Both files are using the [yaml](http://yaml.org/) format. The settings of the local `.chapiconfig` will overwrite the global settings.

### Parameters

The parameter settings will be found under the `parameters` property:

```yml
parameters:
    chronos_url: http://your.chronos.url:chronos_api_port/
    chronos_http_username: username
    chronos_http_password: password
    repository_dir: /path/to/your/local/job/repository

    marathon_url: http://your.marathon.url:marathon_api_port/
    marathon_http_username: username
    marathon_http_password: password
    repository_dir_marathon: /path/to/your/local/marathon/apps/repository

    cache_dir: /path/to/chapi/cache/dir
```

#### chronos_url
The chronos api url (inclusive port). Look also under the [configure command](#configure) option `-u`.

#### chronos_http_username
The chronos http username. Look also under the [configure command](#configure) option `-un`.

Necessary if the setting `--http_credentials` is activated in your Chronos instance.

#### chronos_http_password
The chronos http password. Look also under the [configure command](#configure) option `-un`.

Necessary if the setting `--http_credentials` is activated in your Chronos instance.

#### repository_dir
Root path to your job files. Look also under the [configure command](#configure) option `-r`.

#### marathon_url
The marathon api url (inclusive port). Look also under [configure command](#configure) option `-mu`.

#### marathon_http_username
The marathon http username. Look also under [configure command](#configure) option `-mun`.

#### marathon_http_password
The marathon http password. Look also under [configure command](#configure) option `-mp`.

#### repository_dir_marathon:
Root path to your apps folder. Look also under [configure command](#configure) option `-mr`.

#### cache_dir
Path to cache directory. Look also under the [configure command](#configure) option `-d`.


## Usage

### list
Display your jobs and filter they by failed

```Shell
bin/chapi list [options] 
```

    Options:
      -f, --onlyFailed      Display only failed jobs
      -d, --onlyDisabled    Display only disabled jobs

### info
Display your job information from remote system

```Shell
bin/chapi info <jobName> 
```

    Arguments:
      jobName               selected job
The job name incase of marathon would be the full id for the job.

### status
Show the working tree status

```Shell
bin/chapi status
```

### diff
Show changes between jobs and working tree, etc

```Shell
bin/chapi diff [<jobName>]
```

    Arguments:
      jobName               Show changes for specific job

### add
Add job contents to the index

```Shell
bin/chapi add [<jobnames>]...
```

    Arguments:
      jobnames              Jobs to add to the index
 
### reset 
Remove jobs from the index

```Shell
bin/chapi reset [<jobnames>]...
```

    Arguments:
      jobnames              Jobs to add to the index

### pull
Pull jobs from remote system and add them to local repository

```Shell
bin/chapi pull [options] [--] [<jobnames>]...
```

    Arguments:
      jobnames              Jobnames to pull
    
    Options:
      -f, --force           Force to overwrite local jobs 

### commit
Submit changes to chronos

```Shell
bin/chapi commit
```

### scheduling
Display upcoming jobs in a specified timeframe

```Shell
bin/chapi scheduling [options]
```

    Options:
      -s, --starttime[=STARTTIME]  Start time to display the jobs
      -e, --endtime[=ENDTIME]      End time to display the jobs
**Note: Not application for marathon**

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
      
### validate
Validate local jobs

```Shell
bin/chapi validate [<jobmames>]...
```

    Arguments:
      jobmames              Jobs to validate

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
Chapi is able to support you if you need to move your jobs from a chronos cluster to another one.

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


## Todos:
### Marathon
- [] The validate command for marathon is not yet implemented.
- [] The list command has status set as `ok` for marathon entities. This could show the last status of the app.


## Special cases in marathon:
* Pulling a job from marathon will dump the json object with default values.
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
