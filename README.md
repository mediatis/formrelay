# Introduction

EXT:formrelay acts as a relay for form submissions.
It can hook into form systems (like EXT:form) and send its output to any (defined) destination.

Thereby it evaluates automatically (configured in TypoScript), which destination should be triggered.
It is also adding relevant data from the environment, and it is filtering and transforming the submitted data into a format specific to the destination.

The available destinations depend on the Formelay Extensions being installed. EXT:formrelay alone will not do anything. It only provides the tools that every destination needs to do its job.

# Setup Formrelay

The extension is enabled by default. You simply have to install it and include its TypoScript template.

`plugin.tx_formrelay.settings.enabled = 1`

How you can use it depends on the use case.

## EXT:form

The extension EXT:form is using FormFinishers. EXT:formrelay is providing such a finisher, which can be used in any form, using the form editor in the backend module "Forms".

[//]: # (## EXT:powermail)

## Other (Form) Extensions

In order to implement other input systems for EXT:formrelay, you can implement those where ever you want. For example you can implement a Finisher for EXT:powermail. Or you can hook into any other system that is providing data which shall be sent to various destinations.

To feed data into EXT:formrelay, you need to instantiate `\Mediatis\Formrelay\Service\Relay`.

Call its method `process(array $data, array $formSettings = [])` to trigger a form submission, where `$data` is an associative array with field names as keys and field values as values. The second parameter $formsettings can overwrite global settings for all destinations registered in the system.


This extension is not bound to forms exclusively. You can implement a "form submission" where ever you like. After all it is just a set of key-value-pairs. But make sure that the keys (field names) are configured properly.

# Setup Formrelay Destinations

Every Formrelay destination is a separate package or extension. It brings both custom settings that are specific for this package (like an email address if the data shall be sent via email) and basic settings that is similar for all Formrelay extensions.

All settings, basic and custom should be within the TypoScript `plugin.tx_formrelay_<my_extension>.settings`.

## Custom Setup

The custom setup is specific to the Formrelay extension that you are using and has to be described there. It can be any TypoScript as long as it is not interfering with the basic setup.

## Basic Setup

The basic setup is of the same structure for all Formrelay extensions.

### settings.enabled

A boolean flag that enables this destination. The destination will not receive any data if it is disabled. 

Default should be `0`, but depends on the implementation of the Formrelay extension.

### settings.gate

The `gate` is determining whether or not a destination will receive a form submission. The logic by which this is determined is described in the section `Evaluation`.

If the `gate` is not set and the enabled flag is set, then all submissions will be sent to the destination.

```
settings {
  gate {
    # conjunctive (and) list of evaluations
    form_field_name_a = form_field_value_a_1
    form_field_name_b.not.in = form_field_value_b_1,form_field_value_b_2
  }
}
```

### settings.defaults

The `defaults` are a list of key-value-pairs which will be sent to the destination without being processed by the field- or the value-mapper. This means that the external field-names and -values have to be used, which are defined by the destination.

Those default values are being used if the actual field data (after processing field- and value-mappers) is not overwriting them.

```
settings {
  defaults {
    external_field_name_a = external_field_value_a
    external_field_name_b = external_field_value_b
  }
}
```

### settings.fields.ignore

This is a comma-separated list of form fields that will be ignored completely by the processing of the destination.


```
settings {
  fields {
    ignore = form_field_name_a,form_field_name_b
  }
}
```

### settings.fields.mapping.<field_name>

The object `settings.fields.mapping` contains a list of all form field names that shall have a customised mapping to an external field defined by the destination. The logic by which the mapping of a field is happening is described in the section `FieldMapper`.

The easiest way to setup a mapping is to use a simple string:

```
settings.fields.mapping {
  form_field_name_x = external_field_name_x
}
```

More complex features can be found in the section `FieldMapper`.

### settings.fields.unmapped

All fields, that are not mapped explicitly by `settings.fields.mapping.<field_name>` are implicitly mapped by `settings.fields.unmapped`.  
The logic applied is the sames as the one used for `settings.fields.mapping`.

Common settings are:
```
# pass every unmapped field with its orginal field name
settings.fields.unmapped.passthrough = 1
```

```
# ignore all unmapped fields
settings.fields.unmapped.ignore = 1
```

```
# map all unmapped fields into a single external field, appending their key and value
settings.fields.unmapped = comments
settings.fields.unmapped.appendKeyValue = 1
```

All features can be found in the section `FieldMapper`.

### settings.values.ignoreIfEmpty

This flag determines whether empty form fields shall be ignored. This value usually defaults to `1`.

```
settings.values.ignoreIfEmpty = 1
```

### settings.values.mapping.<field_name>

The object `settings.values.mapping` contains a list of all form field names that shall have a customised value mapping to match the needs of the destination. The logic by which the mapping of the field's values is happening is described in the section `ValueMapper`.

The most common way to map a field value is to use simple pairs of strings for the internal and external value.

```
settings.values.mapping {
  form_field_name_x {
    form_field_value_x_1 = external_field_value_x_1
    form_field_value_x_2 = external_field_value_x_2
  }
}
```

More complex features can be found in the section `ValueMapper`.

If either a field or the value of a field is not defined in the value-mapping, the original form value is used. So if there no value-mapping needed, the setting object `settings.values.mapping` can be omitted completely. This is also why there is no such object as `settings.values.unmapped`.

### 1..n

When one type of destination shall receive the same form submission multiple times, we can use sub-settings.  
All settings from `settings` will be used, and can be extended and overwritten by the sub-settings.

```
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
```

This feature can also be used to change settings depending on the form data. The TypoScript below is just an example for an imaginary email Formrelay.

```
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
```

## Form Data

The usual data coming from a form submission is quite simple, since it is a list of string-pairs: field name and field value.  
However, while every field value must behave like a string (it must either be a string or an object that implements the method `__toString()`), it doesn't have to be a string in itself.  
Here are such those cases, that are shipped with EXT:formrelay, all located in the namespace `Mediatis\Formrelay\Domain\Model\FormField`.

### MultiValueFormField

The class `MultiValueFormField` is an `ArrayObject` and can hold multiple values. `Destinations` and `Dispatchers` can check for this class and act accordingly. If they ignore the class and treat it as `string`, it will `implode` all values in its internal array, using `,` as `glue`.

### DiscreteMultiValueFormField

Technically speaking the class `DiscreteMultiValueFormField` is equivalent to `MultiValueFormField`. The difference is, that its class tries to indicate to `Dispatchers` (that care about this), that all values of this field want to be dispatched in separate, discrete fields (that all have the same name). `Dispatchers` (and `Destinations`) can take this information into account, but it is not guaranteed that they will do.

### UploadFormField

The class `UploadFormField` holds data about a file that has been uploaded through a form submission.  
If it is handled as a simple `string`, it will return the absolute URL to this file, which is stored on the server publicly.  
```
public function getPublicUrl(): string
```

It also provides (for those `Dispatchers` that care) the local path of the uploaded file, so that the dispatcher can use the file directly instead of just having a link to it.
```
public function getRelativePath(): string
```

## Configuration Resolver

Generally speaking, there are different types of resolvers who crawl through a given TypoScript object and build some kind of result.
The algorithm by which the result is resolved depends on the type of resolver and is very extendable. Extensions can register new resolvers of a given type, which are called and processed automatically. To register a new Resolver, we can use the methods in `\Mediatis\Formrelay\Service\Registry`, which are registering signal slots that have to implement the interface of the resolver we want to extend. Alternatively we can use the signal slots directly.

All resolvers have in common that their constructor is fed with the configuration that has to be resolved. This can be either be a scalar value or an array.

### Evaluation

Interface: `\Mediatis\Formrelay\ConfigurationResolver\Evaluation\EvaluationInterface`  
Optional abstract class to extend: `\Mediatis\Formrelay\ConfigurationResolver\Evaluation\Evaluation`  

Resolvers of the type `Evaluation` will try to make some kind of decision. They can be used in multiple places, while their result can be processed differently depending on the context in which they are used. 

Usually the Formrelay will start with the `GeneralEvaluation` which provides the methods `eval` and `resolve`.

The method `eval` will process the actual logic and return a boolean value. The logic has all form fields as input variables.

```
public function eval(array $context = [], array $keysEvaluated = []): bool;
```

The `$context` will contain the context of the current evaluation, which is at minimum the form data, which is used as input variables for the logic.  
The `$keysEvaluated` is a list of extension keys, that have been evaluated so far. This is important for the `GateEvaluation` to avoid evaluation loops. Other evaluations should just pass this value to sub-evaluations, if there are any.

```
settings.evaluateSomeEvaluation {
  and {
    field_a = A
    field_b = B
  }
}
```
  
The method `resolve` will call the method `eval` and will return the TypoScript object path `then` or `else` depending on the result. If the corresponding object path does not exist, it will return `null` instead.

```
public function resolve(array $context, array $keysEvaluated = []): mixed|null
```

This input of the function is the same as for `eval`, but the result will either be one of the configuration paths `then` or `else`, or `null` if the path is not existent.

```
settings.resolveSomeEvaluation {
  and {
    field_a = A
    field_b = B
  }
  then = Foo
  else = { ... possibly more config to resolve ... }
}
```

Whether `eval` or `resolve` is called depends on the context in which the evaluation is being used.  
The most common context is the `gate` evaluation described above, where the method `eval` determines whether or not a destination will be triggered.

All evaluations other than `GeneralEvaluation` do not need to implement the method `resolve`. They only need to take care of the method `eval` in order to provide a boolean result for their evaluation.

Here are all default evaluations, shipped with EXT:formrelay.

#### GeneralEvaluation

This evaluation is the entry point for every evaluation. It acts as an `AndEvaluation`, but also provides the method `resolve`.

#### AndEvaluation

This evaluation expects a list of sub-evaluations and will process them in an and-context which means that all sub-evaluations have to evaluate to `true` in order for the `AndEvaluation` to also return `true`. The sub-evaluations are conjunctive.

```
and {
  not {
    ...
  }
  or {
    ...
  }
  field_x = value_y
  someEvaluation = ...
}
```

Numeric keys will be thought of as encapsulated evaluations, which will start as a new `GeneralEvaluation`.

```
and {
  10 {
    ...
  }
  20 {
    ...
  }
}
```

Keys that do not represent a number nor a keyword for a specific evaluation, will be recognised as field name, followed by a new evaluation. Depending on the value it will be either a `GeneralEvaluation` for arrays or an `EqualsEvaluation` for scalar values.

```
and {
  field_name_a = field_value_a_1
  field_name_b = field_value_b_13
}
```

```
and {
  field_name_a {
    or {
      10.equals = field_value_a_1
      20.equals = field_value_a_2
    }
  }
  field_name_b {
    not.equals = field_value_b_13
  }
}
```

The keyword `field` is interpreted as new field name for all following evaluations, very much like keys that do not represent a number nor a keyword. This is helpful for field names that are actual keywords.

```
and {
  # the field named "and" must be equal to "foobar"
  field = and
  equals = foobar
}
```

```
and {
  1 {
    # the field named "and" must be equal to "foobar"
    field = and
    equals = foobar
  }
  2 {
    # and the field named "not" must not be equal to "baz"
    field = not
    not.equals = baz
  }
}
```

#### EmptyEvaluation

This evaluation must have a context (a field name) already. It expects a scalar value which determines whether the field value is evaluated as empty or not empty.

```
# the field named "field_name_a" must be empty
field_name_a {
  empty = 1
}
```

```
# the field named "field_name_a" must not be empty
field_name_a.empty = 0
```

#### EqualsEvaluation

This evaluation must have a context (a field name) already. It expects a scalar value which is compared to the field value. In the context of a `GeneralEvaluation` it is used implicitly if the value of a context changing key is a scalar one.

```
# explicit call
field_name_a {
  equals = field_value_a_1
}
```

```
# implicit call
field_name_a = field_value_a_1
```

#### GateEvaluation

This evaluation will load the gate configuration of the given extension (other than the one being currently evaluated) and will return its evaluation.
Since Formrelay extensions can be called multiple times for one form submission (see section `1..n`), we can tell the `GateEvaluation` which call shall be evaluated.

```
gate {
  extKey = tx_formrelay_some_other_extension
  index = 0
}
```

To see if any or all calls to one extension are triggered, we can use the keywords `any` or `all` instead of a number.

```
gate {
  extKey = tx_formrelay_some_other_extension
  index = any
}
```

```
gate {
  extKey = tx_formrelay_some_other_extension
  index = all
}
```

The shorthand to the index `any` is to use a scalar value for the configuration.

```
gate = tx_formrelay_some_other_extension
```

We can also list (comma-separated) multiple extension which shall be evaluated together. In such a case the `GateEvaluation` will return `true` as soon as one of the extensions evaluates to `true`.

```
gate = tx_formrelay_some_other_extension,tx_formrelay_yet_another_extension
```

#### InEvaluation

This evaluation must have a context (a field name) already. It expects either an array of values or a comma-separated list of values and will check whether the form field value is within this list.

```
field_name_a.in = field_value_a_1,field_value_a_2,field_value_a_3
```

```
field_name_a.in {
  1 = field_value_a_1
  2 = field_value_a_2
  3 = field_value_a_3
}
```

#### NotEvaluation

This evaluation will create a new `GeneralEvaluation` with its own configuration and will simply negate the result.
If it is used on a scalar value, it will assume a context (a field name) and will create an `EqualsEvaluation` instead.

```
not {
  field_name_a = field_value_a_1
  or { ... }
  field_name_b.in = field_value_b_1,field_value_b_2
}
```

```
not.field_name_a = field_value_a_1
```

```
field_name_a.not = field_value_a_1
```

#### OrEvaluation

This evaluation acts exactly like the `AndEvaluation` except that the sub-evaluations are disjunctive. As soon as one sub-evaluation is `true`, the `OrEvaluation` becomes `true`.

#### RequiredEvaluation

This evaluation expects either an array of field names or a comma-separated list of field names. It will check whether all of them exist and contain values that do not evaluate to `false` (non-empty values).

```
required = field_name_a,field_name_b
```

```
required {
  1 = field_name_a
  2 = field_name_b
}
```

### FieldMapper

Interface: `\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\FieldMapperInterface`  
Optional abstract class to extend: `\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\FieldMapper`  

Resolvers of the type `FieldMapper` will add processed form fields and their (processed) values into the result array. They can add, change and even delete multiple output fields which is why they get the current result passed as reference, so that they can modify it however needed.

The Formrelay will start with the `GeneralFieldMapper` which provides the method `resolve`.

```
public function resolve(array $context, array $result = []): array
```

All other field mappers do not have to implement the method `resolve`. Instead they will operate in two steps: `prepare` and `finish`.

```
public function prepare(&$context, &$result);
public function finish(&$context, &$result): bool;
```

The method `prepare` is used to change the context of the process for all field mappers that are operating on the current data.  
The method `finish` will apply the actual changes on the result object. The return value `true` indicates, that the processing is complete and no further field mappers should be called for the current form field. The return value `false` indicates that the field mapper was not able to finish the processing of the field an the following field mappers should be called.

Here are all default field mappers, shipped with EXT:formrelay.

#### GeneralFieldMapper

This field mapper is the entry point and provides the method `resolve`. It is used on the settings objects `settings.fields.mapping.<field_name>` and `settings.fields.unmapped`.

It is applying the method `prepare` on all sub-field mappers. After that it is applying the method `finish` on all sub-field mappers until one of them reports success (returns `true`).

In general the `GeneralFieldMapper` is using the order of field mappers as they appear in the configuration, but it makes two exceptions.  
The `PlainFieldMapper` will be applied last so that specialised field mappers can overwrite its behaviour.  
The `IfFieldMapper` will be applied first because it shall be able to overwrite every other field mapper.

#### PlainFieldMapper

This field mapper simply maps a form field name to an external field name.

```
form_field_name_a = external_field_name_a
```

Be careful: If two form fields are mapped to the same external field (without any other mappers being involved), they will overwrite each other.

#### AppendKeyValueFieldMapper

This field mapper has to be combined with another field mapper, that is providing an actual mapping (like the `PlainFieldMapper`). It will then not blindly write the form field's value into the result field but rather append it. Also, it will append the pair of field name and field value. Not just the field value.

```
form_field_name_a = external_field_name_a
form_field_name_a.appendKeyValue = 1
```

You can also configure the separators used between field name and field value as well as the separators used between different name-value-pairs.  
This example shows the default values, where `\s` is mapped to the space character and `\n` is mapped to a line break character.

```
form_field_name_a = external_field_name_a
form_field_name_a.appendKeyValue {
  separator = \n
  keyValueSeparator = \s=\s
}
```

#### AppendValueFieldMapper

This field mapper has to be combined with another field mapper, that is providing an actual mapping (like the `PlainFieldMapper`). It will then not blindly write the form field's value into the result field but rather append it.

```
form_field_name_a = external_field_name_a
form_field_name_a.appendValue = 1
```

You can also configure the separator used between the values of different form fields (mapped to this result field).  
This example shows the default value, where `\s` is mapped to the space character and `\n` is mapped to a line break character.

```
form_field_name_a = external_field_name_a
form_field_name_a.separator = \n
```

#### DiscreteFieldFieldMapper

This field mapper has to be combined with another field mapper, that is providing an actual mapping (like `PlainFieldMapper`). It will make sure that the result field will be a `DiscreteMultiValueFormField` (described above). If the result field already holds data, it will be converted into a `DiscreteMultiValueFormField` (if necessary) and then the field value or values are appended to it.

```
form_field_name_a = external_field_name_a
form_field_name_a.discreteField = 1
```

#### DistributeFieldMapper

This field mapper expects multiple external field names and will write the form field value into all those result fields.

We can configure the external fields simply as comma separated list.

```
form_field_name_a.distribute = external_field_name_a_1,external_field_name_a_2
```

Or we can configure them as array.

```
form_field_name_a.distribute {
  1 = external_field_name_a_1
  2 = external_field_name_a_2
}
```

Having the external fields as array gives the opportunity to use sub-configurations as well.

```
form_field_name_a.distribute {
  1 = external_field_name_a_1
  2 = external_field_name_a_2
  2.appendValue = 1
  2.negate = 1
  3 = external_field_name_a_3
  3.discreteField = 1
}
```

#### IfEmptyFieldMapper

This field mapper will abort the processing if the result field already contains a value.

```
form_field_name_a = external_field_name_a
form_field_name_a.ifEmpty = 1
```

Note: This feature exists for legacy reasons only. It should not be used anymore, since it relies on the consistent order of the form data which can not be guaranteed. A better way to get this behaviour is to check the other input values that want to write to the same result field.

```
form_field_name_a = external_form_field_a
form_field_name_b = external_form_field_a
form_field_name_b.if {
  form_field_name_a.not.empty = 1
  then.ignore = 1
}
```

#### IfFieldMapper

This field mapper applies a `GeneralEvaluation` (using its `resolve` method). If the result is not null (if the corresponding configuration path `then` or `else` exists) it will be used for a new `GeneralFieldMapper` to resolve the field mapping.

```
form_field_name_a.if {
  form_field_name_b = xyz
  then = external_form_field_a_2
  else = external_form_field_a_1
}
```

If not both paths `then` and `else` exist, the `IfFieldMapper` may be ignored, depending on the evaluation.

```
form_field_name_a = external_form_field_a_2
form_field_name_a.if {
  form_field_name_b = xyz
  then = external_form_field_a_1
}
```

All evaluations can be used, so we can create arbitrary, complex conditions.

```
form_field_name_a = external_form_field_a_1
form_field_name_a.if {
  or {
    form_field_name_b = xzy
    form_field_name_c.not = abc
    not {
      or {
        1 = and {
          form_field_name_d = 123
          form_field_name_e = 456
        }
        2 = and {
          form_field_name_f = 789
          form_field_name_g = 321
        }
      }
    }
  }
  then.if {
    or {
      gate = tx_formrelay_foo
      not.gate = tx_formrelay_bar
      form_field_name_b.in = 42,666,foo
    }
    form_field_name_b.not = 666
    then = external_form_field_a_2
    then.appendKeyValue = 1
    else.ignore = 1
  }
  else = external_form_field_a_999
}
```

#### IgnoreFieldMapper

This field mapper stops the processing of the form field completely.

It can be used to ignore specific form fields explicitly.

```
settings.fields.mapping.form_field_name_a.ignore = 1
```

And it can be used to implicitly ignore all fields that are not specified in the `mapping` object.

```
settings.fields.unmapped.ignore = 1
```

It can also be used in conditioned field mappers.

```
form_field_name_a = external_field_name_a
form_field_name_a.if {
  form_field_name_b = form_field_value_b
  then.ignore = 1
}
```

#### JoinFieldMapper

This field mapper has to be combined with another field mapper, that is providing an actual mapping (like the `PlainFieldMapper`). It will check whether the given form field value is a `MultiValueFormField` and will then implode the values to a single `string`.

```
form_field_name_a = external_field_name_a
form_field_name_a.join = 1
```

We can also configure the `glue` of the `implode` call. The example below shows the default, where `\s` is replaced with the space character and `\n` is replaced with a line break character.

```
form_field_name_a = external_field_name_a
form_field_name_a.join = 1
form_field_name_a.join {
  glue = \n
}
```

If we configure the `join` field mapper, there is actually no need anymore to explicitly enable it.

```
form_field_name_a = external_field_name_a
form_field_name_a.join.glue = \n
```

#### NegateFieldMapper

This field mapper has to be combined with another field mapper, that is providing an actual mapping (like the `PlainFieldMapper`). It will evaluate the given form field value to a `boolean` value and return a negated version.

By default it will return `0` for values, that evaluate to `true`, and `1` for values, that evaluate to `false`.

```
form_field_name_a = external_field_name_a
form_field_name_a.negate = 1
```

We can also configure the negated values.

```
form_field_name_a = external_field_name_a
form_field_name_a.negate = 1
form_field_name_a.negate {
  true = foo
  false = bar
}
```

If we configure the `negate` field mapper, there is actually no need anymore to explicitly enable it.

```
form_field_name_a = external_field_name_a
form_field_name_a.negate {
  true = foo
  false = bar
}
```

#### PassthroughFieldMapper

This field mapper will use the original form field name as external field name for the current destination.

```
form_field_name_a.passthrough = 1
```

It is pretty much the same as an identity mapping.

```
form_field_name_a = form_field_name_a
```

However, it comes in handy on the settings object `settings.fields.unmapped` which applies to all form fields that do not have an explicit mapping.

```
settings.fields.unmapped.passthrough = 1
```

#### SplitFieldMapper

This field mapper splits the given form field value and distributes the result among different external fields.

```
form_field_name_a.split.fields {
  1 = external_field_name_a_1
  2 = external_field_name_a_2
  3 = external_field_name_a_3
}
```

The split token is the space character by default, but it can be configured, too, where `\s` is replaced with the space character and `\n` is replaced with a line break character.
```
form_field_name_a.split {
  token = .
  fields {
    1 = external_field_name_a_1
    2 = external_field_name_a_2
  }
}
```

Example:

```
name.split.fields {
  1 = first_name
  2 = last_name
}
```

```
name === "Foo Bar"
result === [first_name => "Foo", last_name => "Bar"]
```
If the form field value can not be split into enough parts, only the external fields that have a (split) value, will be used.

```
name === "Foo"
result === [first_name => "Foo"]
```

If the form field value can be split into more parts than external fields are configured, the last field will get all remaining (split) values.

```
name === "Foo Bar Baz"
result === [first_name => "Foo", last_name => "Bar Baz"]
```

### ValueMapper

Interface: `\Mediatis\Formrelay\ConfigurationResolver\ValueMapper\ValueMapperInterface`  
Optional abstract class to extend: `\Mediatis\Formrelay\ConfigurationResolver\ValueMapper\ValueMapper`

Resolvers of the type `ValueMapper` will map the value of a given form field to an appropriate value of the current destination.  
If there is no mapping provided the original form field value is used.

The Formrelay will use the `GeneralValueMapper` to resolve the configuration `settings.values.mapping.<field_name>`.

All value mappers must implement the method `resolve`.

```
/**
 * @param array $context
 * @return string|null
 */
public function resolve(array $context);
```

The parameter `$context` provides all necessary context data, at minimum the form data and the name of the field whose value is to be mapped.

#### GeneralValueMapper

This value mapper processes its configuration in search for sub-value mappers that will eventually return a mapped value.  
Generally speaking the configuration is processed in order of its appearance, but there are two exceptions.

The `PlainValueMapper` will be applied last so that specialised value mappers can overwrite its behaviour.  
The `IfValueMapper` will be applied first because it shall be able to overwrite every other value mapper.

If a configuration key can be resolved as sub-value mapper, it will be resolved and used if its result is not `null`. Otherwise the configuration processing will continue.

```
settings.values.mapping.form_field_name_a {
  keyword_a { ... sub config ... }
  keyword_b = ... scalar config ...
  ...
}
```
  
If a configuration key can not be resolved as sub-value mapper, it will instead be compared to the value of the current form field. If they equal each other, its sub configuration will be used to resolve a new `GeneralValueMapper`.  

```
settings.values.mapping.form_field_name_a {
  form_field_value_a_1 { ... sub config ... }
  form_field_value_a_2 = ... sub config ...
  ...
}
```

If a scalar configuration is found (processed after all other value mappers), the `PlainValueMapper` is used to determine the mapped value.
This is actually the default use case. A simple internal-value-external-value mapping.

```
settings.values.mapping.form_field_name_a {
  form_field_value_a_1 = external_field_value_a_1
  form_field_value_a_2 = external_field_value_a_2
  ...
}
```

If there is no value mapper found, that is returning a valid value (not equal to `null`), then the original value is returned.

#### PlainValueMapper

This value mapper is simply returning the (scalar) value it has received as configuration. It has already been described at the `GeneralValueMapper`.

Theoretically speaking it can be applied to the field itself, though this doesn't make a lot of sense, since all values of that field would be mapped to the same value.

```
settings.values.mapping.form_field_name_a = constant_external_value_a_for_all_internal_values
```

It is more common to use it for specific internal values or as result for other sub-value mappers.

```
settings.values.mapping.form_field_name_a {
  form_field_value_a_1 = external_field_value_a_1
}
```

#### IfValueMapper

This value mapper applies a `GeneralEvaluation` (using its `resolve` method). If the result is not null (if the corresponding configuration path `then` or `else` exists) it will be used for a new `GeneralValueMapper` to resolve the value mapping.

If the corresponding path (`then` or `else`) does not exist, it will return `null` (as does the `GeneralEvaluation`) and therefore will be ignored by its parent value mapper.

```
settings.values.mapping.form_field_name_a {
  form_field_value_a_1 = external_field_value_a_1
  form_field_value_a_1.if {
    form_field_name_b = form_field_value_b_1
    then = external_field_value_a_2
  }
}
```

It can also be applied on the field itself.

```
settings.values.mapping.form_field_name_a.if {
  form_field_name_b = form_field_value_b_1
  then {
    form_field_value_a_1 = external_field_value_a_1a
    form_field_value_a_2 = external_field_value_a_2a
  }
  else {
    form_field_value_a_2 = external_field_value_a_2b
    form_field_value_a_1 = external_field_value_a_1b
  }
}
```

Such conditions can also be nested in any order with any value mapper, that is triggering a new `GeneralValueMapper`.
   
```
settings.values.mapping.form_field_name_a.if {
  form_field_name_b = form_field_value_b_1
  then {
    form_field_value_a_1 = external_field_value_a_1a
    form_field_value_a_2 = external_field_value_a_2a
  }
  else {
    form_field_value_a_2 = external_field_value_a_2b
    form_field_value_a_1.if {
      form_field_name_c = form_field_value_c_1
      then.if {
        form_field_name_d.not = form_field_value_d_1
        not.gate = tx_formrelay_foo,tx_formrelay_bar
        then = external_field_value_a_1b
        else.negate = 1
      }
      else = external_field_value_a_1c
    }
  }
}
```

#### NegateValueMapper

This value mapper will evaluate the given form field value to a `boolean` value and return a negated version.

By default it will return `0` for values, that evaluate to `true`, and `1` for values, that evaluate to `false`.

```
settings.values.mapping.form_field_name_a {
  negate = 1
}
```

Those values can also be configured. In the example below the result will be `no` if the form value evaluates to `true` and `yes` if the value evaluates to `false`.

```
settings.values.mapping.form_field_name_a {
  negate = 1
  negate {
    true = yes
    false = no
  }
}
```

If the `negate` value mapper is configured, we do not need to enable it specifically.

```
settings.values.mapping.form_field_name_a {
  negate {
    true = yes
    false = no
  }
}
```

The `negate` value mapper triggers actually a new `GeneralValueMapper` which means that there can even be sub-value mappers.  
As the `GeneralValueMapper` does, the original form value is used if no sub-value mapper exists (or no sub-value mapper returns a valid result).

```
settings.values.mapping.form_field_name_a {
  negate {
    form_field_value_a_1 = 0
    form_field_value_a_1.if {
      form_field_name_b = form_field_value_b
      then = 1
    }
  }
}
```

#### RawValueMapper

This value mapper behaves like a `GeneralValueMapper` except that it will not search for any keywords for sub-value mappers.  
It is helpful if the form values may contain keywords of value mappers.

```
settings.values.mapping.form_field_name_a.raw {
    negate = some_external_value_negieren
    if = some_external_value_for_if
}
```

#### SwitchValueMapper

This value mapper allows field values to be TypoScript configuration values instead of configuration keys.  
This is helpful for values which do not follow the rules of TypoScript keys.  
The configuration of a `switch` value mapper is an array of cases where each has one value `case` which is compared to the actual form field value and one value `value` which is the result if the comparison succeeds.

```
settings.values.mapping.form_field_name_a.switch {
  10 {
    case = form.field.value.a.1
    value = external_field_value_a_1
  }
  20 {
    case = form field \value.a 2
    value = external_field_value_a_2
  }
}
```

The configuration value `case` can also be applied as scalar value of the `switch` object.

```
settings.values.mapping.form_field_name_a.switch {
  10 = form.field.value.a.1
  10.value = external_field_value_a_1
  20 = form field \value.a 2
  20.value = external_field_value_a_2
}
```

The `value` of a case is actually processed as a new `GeneralValueMapper` which leaves room for sub-value mappers.

```
settings.values.mapping.form_field_name_a.switch {
  10 = form.field.value.a.1
  10.value = external_field_value_a_1
  10.value.if {
    form_field_name_b = form_field_value_b
    then = external_field_value_a_2
    else.negate = 1
  }
  20 ...
}
```

### ContentResolver

Interface: `\Mediatis\Formrelay\ConfigurationResolver\ContentResolver\ContentResolverInterface`  
Optional abstract class to extend: `\Mediatis\Formrelay\ConfigurationResolver\ContentResolver\ContentResolver`  

Resolvers of the type `ContentResolver` will generate a string by parsing the given configuration as a kind of template.

EXT:formrelay will not use the content resolver itself, but other extension can use it, to process parts of the configuration. For example, a mail-formrelay can use a content resolver to insert form data into the email, e.g. as receiver, subject, mail-body.

The entry point will always be the `GeneralContentResolver` which provides the method `resolve`.

```
public function resolve(array $context): string
```

All other content resolvers do not need to implement this method. Instead their work is split into two steps: `build` and `finish`.

```
public function build(array &$context): string;
public function finish(array &$context, string &$result): bool;
```

The method `build` will create the actual result, while the method `finish` can modify the accumulated result of all content resolvers, that are involved, afterwards. Also a content resolver can abort the finishing of all remaining content resolvers by returning the value `true`.

#### GeneralContentResolver

This content resolver is just processing its configuration and reacting to everything that it finds.

A scalar configuration is triggering a `PlainContentResolver` which will just use the configuration itself to build the result.

```
output = This is a scalar configuration that is also the output of the content resolver.
```

Numeric keys will indicate sub-content resolvers, each starting as a new `GeneralContentResolver`.

```
output {
  10 = This is a scalar configuration...
  20 = in two parts.
}
# result === This is a scalar configuration...in two parts.
```

The keyword `glue` will be used to set a glue string that is written between the (non-empty) results of its sub-content resolvers, where `\s` is replaced with the space character and `\n` is replaced with a line break character.  
The default `glue` is an empty string.

```
output {
  glue = \s
  10 = This is a scalar configuraiton
  20 = in two parts.
}
# result === This is a scalar configuration in two parts.
```

Any actual keyword of a sub-content resolver will trigger its `build` and `finish` methods.

```
output{
  field = form_field_name_a
  trim = 1
}
``` 

```
output = {first_name}
output.insertData = 1
```

#### PlainContentResolver

This content resolver has already been described by the `GeneralContentResolver` for scalar configurations. It will simply `build` a result from its configuraiton.

```
output = Hello World
```

#### FieldContentResolver

This content resolver will try to find a field with the name of its configuration in the form data and will output its value.

```
output.field = form_field_name_a
```

#### IfContentResolver

This content resolver applies a `GeneralEvaluation` (using its `resolve` method). If the result is not null (if the corresponding configuration path `then` or `else` exists) it will be used for a new `GeneralContentResolver` to build the content.  

```
output = Hello World
output.if {
  form_field_name_a = form_field_value_a_1
  then = Hello Universe
}
```

If the `IfContentResolver` produces any output, it will disable all other content resolvers on this configuration. All content resolvers that want to apply in this case, will have to be set in the `then` or `else` path of the `if` structure.  
If the corresponding path (`then` or `else`) does not exist, the content resolver won't do anything.

```
# In the example below the then-part does need to set insertData = 1 again,
# because the outer configuration will be disabled completely.
# Inside the if-structure there will also be no trimming, unless we set it there.

output = Hello {name}
output.insertData = 1
output.if {
    form_field_name_a = form_field_value_a_1
    then = Hello {other_name}
    then.insertData = 1
}
output.trim = 1
```

As usual we can nest the evaluations and content resolver however it suits us.

```
output {
  10 = Hello
  10.if {
    language = de
    then = Hallo
    else.if {
      language = es
      then = Hola
    }
  }
  20.if {
    form_field_name_a = form_field_value_a_1
    then {
      glue = \s
      10 = Hello
      20 = World
      30.field = form_field_name_b
      trim = 1
    }
    else {
      10 = Foo
      20.if {
        form_field_name_c = form_field_value_c_1
        else = foo {bar} baz
        else.insertData = 1
      }
    }
  }
}
```

#### InsertDataContentResolver

This content resolver is modifying the result that is built by all content resolvers, that are involved.  
It is going through all form fields `form_field_name_x` and is replacing all occurrences of the string `{form_field_name_x}` with the value of the form field.  
Afterwards it will also remove all such placeholders `{...}` which were not replaced.  
And it will replace all `\s` with the space character and all `\n` with a line break character.

```
output = \s\s\sfoobar\s\s\s
output.insertData = 1
# result === "   foobar   "
```

```
output = foo {bar} baz
# result === "foo <value_of_field_bar> baz"
```

```
output = foo{some_field_name_that_does_not_exist}bar
output.insertData = 1
# result === "foobar"
```

#### TrimContentResolver

This content resolver is modifying the result that is built ba yll content resolvers, that are involved.  
It is trimming the result, removing all whitespace characters at the beginning and the end of the result.

```
output = \s\s\sfoobar\s\s\s
output.insertData = 1
output.trim = 1
# result === "foobar"
```

# Overwrite Setup of Formelay Destinations
## Defaults in EXT:formrelay
## Overwrites in Backend

# Change EXT:formrelay Behaviour

## DataProvider
### addData

## Configuration Update
### updateConfig

## Formrelay Destination
### beforeGateEvaluation
### afterGateEvaluation
### beforeDataMapping
### afterDataMapping
### dispatch

# Implement Formrelay Destinations

Build your own extension formrelay_mydestination. Now there are multiple ways to connect to EXT:formrelay.

## ... by Signal Slot

You can connect to (at least) two signal slots:

class: `\Mediatis\Formrelay\Service\FormrelayManager`  
signal: `register`  
signature: `register(array $list):array`

The signal slot takes an array as its only argument. Push your extension key into the array and return all arguments.  
Now your extension key will be evaluated and its destination can be triggered.

class: `\Mediatis\Formrelay\Service\FormrelayManager`  
signal: `dispatch`  
signature: `dispatch(string $extKey, int $index, array $conf, array $data, array|bool $attachments, bool|null $result):array`

The dispatcher should only act if the argument $extKey equals to its own extension key. The index indicates, the how often the destination has been triggered by now. 0 means it is triggered for the first time.  
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
`printf '%s\n%s\n' "$(git log $(git describe --tags --abbrev=0)..HEAD --no-merges --format=%B)" "$(cat CHANGELOG.txt)" > CHANGELOG.txt`

