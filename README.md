## Configure TYPO3 Mail Form 
	postProcessor {
		2 = Mediatis\Formrelay\Plugins\MailFormPostProcessor
		2 {
		}
	}


## Configure Formhandler

	plugin.Tx_Formhandler.settings.predef.FORMNAME {
		...
		finishers {
			1.class = \Mediatis\Formrelay\Plugins\FormhandlerFinisher
		}
	}

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
