.. include:: ../Includes.txt


.. _Formdata:

=========
Form Data
=========

The usual data coming from a form submission is quite simple, since it is a list of string-pairs: field name and field value.
However, while every field value must behave like a string (it must either be a string or an object that implements the method ``__toString())``, it doesn't have to be a string in itself.
Here are such those cases, that are shipped with EXT:formrelay, all located in the namespace ``Mediatis\Formrelay\Domain\Model\FormField``.

MultiValueFormField
*******************

The class ``MultiValueFormField`` is an ``ArrayObject`` and can hold multiple values. ``Destinations`` and ``Dispatchers`` can check for this class and act accordingly. If they ignore the class and treat it as ``string``, it will ``implode`` all values in its internal array, using ``,`` as ``glue``.

DiscreteMultiValueFormField
***************************

Technically speaking the class ``DiscreteMultiValueFormField`` is equivalent to ``MultiValueFormField``. The difference is, that its class tries to indicate to Dispatchers (that care about this), that all values of this field want to be ``dispatched`` in separate, discrete fields (that all have the same name). ``Dispatchers`` (and ``Destinations``) can take this information into account, but it is not guaranteed that they will do.

UploadFormField
***************

The class ``UploadFormField´´ holds data about a file that has been uploaded through a form submission.
If it is handled as a simple string, it will return the absolute URL to this file, which is stored on the server publicly.

::

	public function getPublicUrl(): string

It also provides (for those ``Dispatchers`` that care) the local path of the uploaded file, so that the dispatcher can use the file directly instead of just having a link to it.

::

	public function getRelativePath(): string