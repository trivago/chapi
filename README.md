# chapi
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
php app/console configure 
```

## Usage

### jobs
Display your jobs and filter they by failed

### info
Display your job information from chronos

### status
Show the working tree status

### diff
Show changes between jobs and working tree, etc

### add
Add job contents to the index
 
### reset 
Remove jobs from the index

### pull
(coming soon)
Dump job from chronos and store it to local repository

### commit
Submit changes to chronos



[link_chronos]: https://github.com/mesos/chronos