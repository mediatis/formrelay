.. include:: ../../Includes.txt


.. _resend:

=======================
Resend form submissions
=======================

To get the help info about this command, use: 
.. code-block::
   ./typo3/cli_dispatch.phpsh extbase help formsimulation:run

An example usage of the command is this:
.. code-block::
   ./typo3/cli_dispatch.phpsh extbase formsimulation:run --page-id=8 /absolute/path/to/the/log/file.xml

* The input file must have the format of the standard output of the log file (which is not xml-well-formed).
* Log entries you do not want to re-send have to be deleted from the file before running the command.
* The command script is generous enough to forgive and ignore duplicate prefix lines *<?xml version="1.0" encoding="UTF-8"?>*
* The --page-id option is the TYPO3 page, whose TypoScript will be loaded for the formrelay configuration. If different form submissions were made from different TYPO3 pages which had different formrelay configurations, then you are out of luck. There is no way of reconstructing this from the current format of the log entries. The --page-id option defaults to 1.
