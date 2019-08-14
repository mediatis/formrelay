# Introduction

EXT:formrelay acts as a relay for form submissions.
It can hook into form systems (like EXT:form) and send its output to any (defined) endpoint.

Thereby it evaluates automatically (configured in TypoScript), which endpoint should be triggered.
It is also adding relevant data from the environment, and it is filtering and transforming the submitted data into a format specific to the endpoint.

The available endpoints depend on the Formelay Extensions being installed. EXT:formrelay alone will not to anything. It only provides the tools that every endpoint needs to do its job.

# Setup Formrelay

The extension is enabled by default. You simply have to install it and include its TypoScript template.

`plugin.tx_formrelay.settings.enabled = 1`

How you can use it depends on the use case.

## EXT:form

The extension EXT:form is using FormFinishers. EXT:formrelay is providing such a finisher, which can be used in any form, using the form editor in the backend module "Forms".

[//]: # (## EXT:powermail)

## Other (Form) Extensions

In order to implement other hooks for EXT:formrelay, you can simply implement one where you instantiate `\Mediatis\Formrelay\Service\FormrelayManager`.  
Call its method `process($data, $formSettings = [], $simulate = false, $attachments = false)` to trigger a form submission.  
This extension is not bound to forms exclusively. You can implement a "form submission" where ever you like. After all it is just a set of key-value-pairs. But make sure that the keys (field names) are configured properly.

# Setup Formrelay Endpoints
## Custom Setup
## Basic Setup
### enabled
### gate
### defaults
### fields.ignore
### fields.mapping.<field_name>
### fields.unmapped
### values.ignoreIfEmpty
### values.mapping.<field_name>
### values.mapping.<field_name>.<field_value>
### values.unmapped
### 1..n
## Evaluation

# Overwrite Setup of Formelay Endpoints
## Defaults in EXT:formrelay
## Overwrites in Backend

# Change EXT:formrelay Behaviour

## DataProvider
### addData

## Configuration Update
### updateConfig

## Formrelay Endpoint
### beforeGateEvaluation
### afterGateEvaluation
### beforeDataMapping
### afterDataMapping
### dispatch

# Implement Formrelay Endpoints

Build your own extension formrelay_myendpoint. Now there are multiple ways to connect to EXT:formrelay.

## ... by Signal Slot

You can connect to (at least) two signal slots:

class: `\Mediatis\Formrelay\Service\FormrelayManager`  
signal: `register`  
signature: `register(array $list):array`

The signal slot takes an array as its only argument. Push your extension key into the array and return all arguments.  
Now your extension key will be evaluated and its endpoint can be triggered.

class: `\Mediatis\Formrelay\Service\FormrelayManager`  
signal: `dispatch`  
signature: `dispatch(string $extKey, int $index, array $conf, array $data, array|bool $attachments, bool|null $result):array`

The dispatcher should only act if the argument $extKey equals to its own extension key. The index indicates, the how often the endpoint has been triggered by now. 0 means it is triggered for the first time.  
The argument $data is holding the actual data of the submission.  
The argument $result should be set to true if the submission was successful. Otherwise to false. If the dispatcher wants to decline processing the data, it should leave $result as it is.
The return value should be an associative array with all arguments.  
`['extKey' => $extKey, 'index' => $index, 'conf' => $conf, 'data' => $data, 'attachments' => $attachments, 'result' => $result]`

## ... by Interface Registration
## ... by extending FormrelayExtension

# Miscellaneous

## Resend form submissions

An example usage of the command is this:
```./vendor/bin/typo3cms formrelay:formsimulator --pageId=1 --filePath=/absolute/path/to/the/log/file.xml```

* The input file must have the format of the standard output of the log file (which is not xml-well-formed).
* Log entries you do not want to re-send have to be deleted from the file before running the command.
* The command script is generous enough to forgive and ignore duplicate prefix lines ```<?xml version="1.0" encoding="UTF-8"?>```.
* The --page-id option is the TYPO3 page, whose TypoScript will be loaded for the formrelay configuration. If different form submissions were made from different TYPO3 pages which had different formrelay configurations, then you are out of luck. There is no way of reconstructing this from the current format of the log entries. The --page-id option defaults to 1.

## Running the unit tests from the command line

```bash
composer install &&
.Build/bin/phpunit -c Tests/UnitTests.xml Tests/Unit/
```

## Running the tests in PhpStorm

PhpStorm > Preferences > Languages & Frameworks > PHP > Test Frameworks

- Click "add"
- Select PHPUnit (local)
- (*) Use Composer autoloader
- Path to script: select `.Build/vendor/autoload.php` in your project folder

In the Run configurations, edit the PHPUnit configuration and use these
settings so this configuration can serve as a template:

- Directory: use the `Tests/Unit` directory in your project
- [x] Use alternative configuration file
- use `.Build/vendor/nimut/testing-framework/res/Configuration/UnitTests.xml`
  in your project folder

## Generating changelog
- CHANGELOG.md must be generated together with every git release and commited together with ext_emconf.php. 
`git log --since=1/1/2019 --no-merges --format=%B > CHANGELOG.txt`
