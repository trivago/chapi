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


## Supported Chronos versions
    * v2.3


[link_chronos]: https://github.com/mesos/chronos
