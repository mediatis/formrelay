.. include:: ../Includes.txt


.. _developer:

=========
Developer 
=========

Change EXT:formrelay Behaviour
##############################

EXT:form ElementProcessor
*************************

processFormElement
==================

DataProvider
************

addData
=======

Configuration Update
********************

updateConfig
============

Formrelay Destination
*********************

beforeGateEvaluation
====================

afterGateEvaluation
===================

beforeDataMapping
==================

afterDataMapping
================

dispatch
========

Implement Formrelay Destinations
################################

Build your own extension formrelay_mydestination. Now there are multiple ways to connect to EXT:formrelay.

... by Signal Slot
******************

You can connect to (at least) two signal slots:

class: ``\Mediatis\Formrelay\Service\FormrelayManager``
signal: ``register``
signature: ``register(array $list):array``

The signal slot takes an array as its only argument. Push your extension key into the array and return all arguments.
Now your extension key will be evaluated and its destination can be triggered.

class: ``\Mediatis\Formrelay\Service\FormrelayManager``
signal: ``dispatch``
signature: ``dispatch(string $extKey, int $index, array $conf, array $data, array|bool $attachments, bool|null $result):array``

The dispatcher should only act if the argument $extKey equals to its own extension key. The index indicates, the how often the destination has been triggered by now. 0 means it is triggered for the first time.
The argument $data is holding the actual data of the submission.
The argument $result should be set to true if the submission was successful. Otherwise to false. If the dispatcher wants to decline processing the data, it should leave $result as it is. The return value should be an associative array with all arguments.
``['extKey' => $extKey, 'index' => $index, 'conf' => $conf, 'data' => $data, 'attachments' => $attachments, 'result' => $result]``

... by Interface Registration
*****************************

... by extending FormrelayExtension
***********************************