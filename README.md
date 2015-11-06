# chapi [![Build Status](https://travis-ci.org/msiebeneicher/chapi.svg?branch=master)](http://travis-ci.org/msiebeneicher/chapi) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/msiebeneicher/chapi/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/msiebeneicher/chapi/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/msiebeneicher/chapi/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/msiebeneicher/chapi/?branch=master)
Chronos api client for your console

## Description
Manage your [Chronos][link_chronos] jobs like a git repository on your console:

* Prepare your jobs before you send them to Chronos
* Manage a separate git repository for job backups and history
* Fast check of your jobs status

## Installation

```Shell
composer install
``` 

```Shell
bin/chapi configure 
```

## Usage

### list
Display your jobs and filter they by failed

```Shell
bin/chapi list [options] 
```

    Options:
      -f, --onlyFailed      Display only failed jobs

### info
Display your job information from chronos

```Shell
bin/chapi info <jobName> 
```

    Arguments:
      jobName               selected job

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
Pull jobs from chronos and add them to local repository

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

### configure
Configure application and add necessary configs

```Shell
bin/chapi configure
```

    Options:
      -u, --chronos_url[=CHRONOS_URL]        The chronos url (inclusive port)
      -d, --cache_dir[=CACHE_DIR]            Path to cache directory
      -r, --repository_dir[=REPOSITORY_DIR]  Root path to your job files
      
### validate
Validate local jobs

```Shell
bin/chapi validate [<jobmames>]...
```

    Arguments:
      jobmames              Jobs to validate

## Workflow

A typical workflow to add a new cronjob to your Chronos server via chapi can be:

1. A pull request for a new cronjob (json definition) comes in (created by a colleague of you
2. Accept the pull request and switch to your local clone via `cd ~/my/clone`
3. Update your local repository via `git pull`
4. Check the current status via `chapi status`
5. Validate everything via `chapi validate`
6. Add the new job via `chapi add jobXy`
7. Apply the changes to the Chronos server via `chapi commit`

## Supported Chronos versions
    * v2.3


[link_chronos]: https://github.com/mesos/chronos
