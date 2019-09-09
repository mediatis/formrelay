.. include:: ../Includes.txt


.. _developer:

================
Developer Corner
================

Target group: **Developers**

Configure TYPO3 Mail Form
.. code-block::
   postProcessor {
      2 = Mediatis\Formrelay\Plugins\MailFormPostProcessor
      2 {
      }
   }

Configure Formhandler
.. code-block::

   plugin.Tx_Formhandler.settings.predef.FORMNAME {
      ...
      finishers {
         1.class = \Mediatis\Formrelay\Plugins\FormhandlerFinisher
      }
   }


.. toctree::
	:maxdepth: 5
	:hidden:

	Resend/Index
