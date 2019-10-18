.. include:: ../Includes.txt



.. _installation:

============
Installation
============

The extension is enabled by default. You simply have to install it and include its TypoScript template.

::

	plugin.tx_formrelay.settings.enabled = 1

How you can use it depends on the use case.

EXT:form
********
The extension EXT:form is using FormFinishers. EXT:formrelay is providing such a finisher, which can be used in any form, using the form editor in the backend module "Forms".

Other (Form) Extensions
***********************
In order to implement other input systems for EXT:formrelay, you can implement those where ever you want. For example you can implement a Finisher for EXT:powermail. Or you can hook into any other system that is providing data which shall be sent to various destinations.

To feed data into EXT:formrelay, you need to instantiate ``\Mediatis\Formrelay\Service\Relay``.

Call its method ``process(array $data, array $formSettings = [])`` to trigger a form submission, where $data is an associative array with field names as keys and field values as values. The second parameter $formsettings can overwrite global settings for all destinations registered in the system.

This extension is not bound to forms exclusively. You can implement a "form submission" where ever you like. After all it is just a set of key-value-pairs. But make sure that the keys (field names) are configured properly.
