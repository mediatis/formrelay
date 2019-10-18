.. include:: ../../Includes.txt


.. _basic:

===========
Basic Setup
===========

The basic setup is of the same structure for all Formrelay extensions.

settings.enabled
****************

A boolean flag that enables this destination. The destination will not receive any data if it is disabled.

Default should be 0, but depends on the implementation of the Formrelay extension.

settings.gate
*************

The ``gate`` is determining whether or not a destination will receive a form submission. The logic by which this is determined is described in the section ``Evaluation``.

If the ``gate`` is not set and the enabled flag is set, then all submissions will be sent to the destination.

::

	settings {
	  gate {
	    # conjunctive (and) list of evaluations
	    form_field_name_a = form_field_value_a_1
	    form_field_name_b.not.in = form_field_value_b_1,form_field_value_b_2
	  }
	}

settings.defaults
*****************

The ``defaults`` are a list of key-value-pairs which will be sent to the destination without being processed by the field- or the value-mapper. This means that the external field-names and -values have to be used, which are defined by the destination.

Those default values are being used if the actual field data (after processing field- and value-mappers) is not overwriting them.

::

	settings {
	  defaults {
	    external_field_name_a = external_field_value_a
	    external_field_name_b = external_field_value_b
	  }
	}

settings.fields.ignore
**********************
This is a comma-separated list of form fields that will be ignored completely by the processing of the destination.

::

	settings {
	  fields {
	    ignore = form_field_name_a,form_field_name_b
	  }
	}

settings.fields.mapping.<field_name>
************************************

The object ``settings.fields.mapping`` contains a list of all form field names that shall have a customised mapping to an external field defined by the destination. The logic by which the mapping of a field is happening is described in the section ``FieldMapper``.

The easiest way to setup a mapping is to use a simple string:

::

	settings.fields.mapping {
	  form_field_name_x = external_field_name_x
	}

More complex features can be found in the section ``FieldMapper``.

settings.fields.unmapped
************************

All fields, that are not mapped explicitly by ``settings.fields.mapping.<field_name>`` are implicitly mapped by ``settings.fields.unmapped``.
The logic applied is the sames as the one used for ``settings.fields.mapping``.

Common settings are:

::

	# pass every unmapped field with its orginal field name
	settings.fields.unmapped.passthrough = 1

::

	# ignore all unmapped fields
	settings.fields.unmapped.ignore = 1

::

	# map all unmapped fields into a single external field, appending their key and value
	settings.fields.unmapped = comments
	settings.fields.unmapped.appendKeyValue = 1

All features can be found in the section ``FieldMapper``.

settings.values.ignoreIfEmpty
*****************************

This flag determines whether empty form fields shall be ignored. This value usually defaults to ``1``.

::

	settings.values.ignoreIfEmpty = 1

settings.values.mapping.<field_name>
************************************

The object ``settings.values.mapping`` contains a list of all form field names that shall have a customised value mapping to match the needs of the destination. The logic by which the mapping of the field's values is happening is described in the section ``ValueMapper``.

The most common way to map a field value is to use simple pairs of strings for the internal and external value.

::

	settings.values.mapping {
	  form_field_name_x {
	    form_field_value_x_1 = external_field_value_x_1
	    form_field_value_x_2 = external_field_value_x_2
	  }
	}

More complex features can be found in the section ``ValueMapper``.

If either a field or the value of a field is not defined in the value-mapping, the original form value is used. So if there no value-mapping needed, the setting object ``settings.values.mapping`` can be omitted completely. This is also why there is no such object as ``settings.values.unmapped``.




1..n
****

When one type of destination shall receive the same form submission multiple times, we can use sub-settings.
All settings from ``settings`` will be used, and can be extended and overwritten by the sub-settings.

::

	settings {
	  receiverEmail = foo@bar.com
	  senderEmail = bar@foo.com
	  1 {
	    subject = Hello World
	  }
	  2 {
	    subject = Hello Other World
	    receiverEmail = foo@baz.com
	  }
	}

This feature can also be used to change settings depending on the form data. The TypoScript below is just an example for an imaginary email Formrelay.

::

	settings {
	  senderEmail = foo@bar.com
	  1 {
	    gate {
	      country = US
	    }
	    receiverEmail = support-us@foo.com
	    subject = Hello US
	  }
	  2 {
	    gate {
	      country.not = US
	    }
	    receiverEmail = support@foo.com
	    subject = Hello World
	  }
	}