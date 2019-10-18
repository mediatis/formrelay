.. include:: ../Includes.txt


.. _configuration:

============================
Setup Formrelay Destinations
============================

Every Formrelay destination is a separate package or extension. It brings both custom settings that are specific for this package (like an email address if the data shall be sent via email) and basic settings that is similar for all Formrelay extensions.

All settings, basic and custom should be within the TypoScript ``plugin.tx_formrelay_<my_extension>.settings``.


.. toctree::
	:maxdepth: 2
	:hidden:

	Custom/Index
	Basic/Index
	Formdata/Index
	Configuration_resolver/Index
	Configuration_overwrite/Index