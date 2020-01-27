.. include:: ../Includes.txt


.. _introduction:

============
Introduction
============

EXT:formrelay acts as a relay for form submissions.
It can hook into form systems (like EXT:form) and send its output to any (defined) destination.

Thereby it evaluates automatically (configured in TypoScript), which destination should be triggered.
It is also adding relevant data from the environment, and it is filtering and transforming the submitted data into a format specific to the destination.

The available destinations depend on the Formelay Extensions being installed. EXT:formrelay alone will not do anything. It only provides the tools that every destination needs to do its job.