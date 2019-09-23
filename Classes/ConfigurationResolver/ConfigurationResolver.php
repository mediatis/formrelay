<?php

namespace Mediatis\Formrelay\ConfigurationResolver;

use InvalidArgumentException;
use TYPO3\CMS\Extbase\Object\Container\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\Exception\UnknownClassException;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extbase\Object\Exception;

abstract class ConfigurationResolver
{
    const SIGNAL_REGISTER = 'register';

    /** @var ObjectManager */
    protected $objectManager;

    /** @var Dispatcher */
    protected $signalSlotDispatcher;

    /** @var array|string */
    protected $config;

    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    /**
     * ConfigurationResolver constructor.
     * @param array|string $config
     */
    public function __construct($config = [])
    {
        if ($this->ignoreScalarConfig() && !is_array($config)) {
            $this->config = [];
        } elseif ($this->convertScalarConfigToArray() && !is_array($config)) {
            $this->config = $config ? explode(',', $config) : [];
        } else {
            $this->config = $config;
        }
    }

    /**
     * signal slot: register as resolver of type getResolverClass()
     *
     * @param array $list
     * @return array
     */
    public function register(array $list): array
    {
        $keyword = $this->getKeyword();
        if ($keyword) {
            $list[$keyword] = static::class;
        }
        return [$list];
    }

    /**
     * checks whether or not a given sub-resolver is a valid resolver
     * in accordance to this one
     *
     * @param $resolver
     * @return bool
     */
    protected function validResolver($resolver)
    {
        if (!$resolver) {
            return false;
        }
        $className = get_class($resolver);
        $interfaceName = $this->getResolverClass() . 'Interface';
        if (!in_array($interfaceName, class_implements($className))) {
            throw new InvalidArgumentException(
                'Error detected - "' . $className . '" must implement interface ' . $interfaceName . '.',
                1565086200
            );
        }
        return true;
    }

    /**
     * fetches a registered sub-resolver for a configuration keyword (cascading down the config)
     *
     * @param string $keyword
     * @param $config
     * @return object|null
     */
    protected function resolveKeyword(string $keyword, $config)
    {
        // try the keyword itself as class
        try {
            $result = $this->objectManager->get($keyword, $config);
            if ($this->validResolver($result)) {
                return $result;
            }
        } catch (UnknownObjectException $e) {
            // TYPO3 8
            // that's okay, we will try to gather more information
        }  catch (UnknownClassException $e) {
            // TYPO3 9
            // that's okay, we will try to gather more information
        }

        // check for registered instances
        $candidates = [];
        try {
            $candidates = $this->signalSlotDispatcher->dispatch(
                $this->getResolverClass(),
                static::SIGNAL_REGISTER,
                [$candidates]
            )[0];
        } catch (Exception $e) {
            // @TODO use logging service to report invalid signal slot
            //       then ignore it and move on
        }
        if (isset($candidates[$keyword])) {
            $result = $this->objectManager->get($candidates[$keyword], $config);
            if ($this->validResolver($result)) {
                return $result;
            }
        }

        // maybe this isn't a keyword at all (but rather data/content)
        return null;
    }

    /**
     * return the config keyword of the particular resolver
     * we are kind of guessing, based on its classname (which is the convention)
     * may be overwritten in the specific implementation
     *
     * the class VendorX\ExtensionY\Something\FoobarEvaluation would have the keyword foobar
     * if it extended the class Mediatis\Formrelay\ConfigurationResolver\Evaluation\Evaluation (getResolverClass)
     *
     * @return string
     */
    protected function getKeyword()
    {
        $resolverClassPath = explode('\\', $this->getResolverClass());
        $resolverClassBase = array_pop($resolverClassPath);
        if (preg_match('/([^\\\\]+)' . $resolverClassBase . '$/', static::class, $matches)) {
            return lcfirst($matches[1]);
        }
        return '';
    }

    /**
     * determines if the configuration should be an empty array if the passed config is a scalar value
     * this is useful for configurations like:
     * field.appendValue = 1
     * ... which can (but does not have to) have a configuration like:
     * field.appendValue.separator = \n
     *
     * @return boolean
     */
    protected function ignoreScalarConfig()
    {
        return false;
    }

    /**
     * determines if the configuration should be converted to an array (explode) if it is a scalar value
     * this is useful for configurations like:
     * gate.required = field_a,field_b,field_c
     * ... which can also be expressed like:
     * gate.required {
     *     1 = field_a
     *     2 = field_b
     *     3 = field_c
     * }
     *
     * @return bool
     */
    protected function convertScalarConfigToArray()
    {
        return false;
    }

    /**
     * Returns the class of the type of resolver
     * which must be (excluding the namespace) the postfix of all resolver classes of its type
     * if they want to be linked to their typoscript keyword automatically
     *
     * example
     * resolver class: Mediatis\Formrelay\ConfigurationResolver\Evaluation\Evaluation
     * resolver type:  Evaluation
     * resolver:       SomeVendor\FormrelayXyz\ConfigurationResolver\Evaluation\FoobarEvaluation
     * keyword:        foobar
     *
     * @return string
     */
    abstract protected function getResolverClass(): string;

    /**
     * The main method to resolve a configuration
     *
     * @param array $context
     * @return mixed
     */
    public function resolve(array $context)
    {
        $processedConfig = $this->preprocessConfigurationArray($this->config);
        foreach ($processedConfig as $key => $value) {
            $resolver = $this->resolveKeyword($key, $value);
            if ($resolver) {
                $result = $resolver->resolve($context);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        return null;
    }

    private function appendConfigKeys(array $keys, array &$result) {
        foreach ($keys as $key) {
            $processedKey = $key === 'plain' ? '_typoScriptNodeValue' : $key;
            if (isset($this->config[$processedKey])) {
                $result[$key] = $this->config[$processedKey];
            }
        }
    }

    /**
     * converts the typoscript configuration into a format that is better to process
     * the result is an array of pairs, each having a key and a config-value
     *
     * respects scalar config and the config key "_typoScriptNodeValue" (both translated to the key "plain")
     * respects numeric keys as sub-config, which need to be addressed by a new general resolver each
     * can change ordering by pre- or appending given keys to the result
     *
     * @param array $prepend
     * @param array $append
     * @return array
     */
    protected function preprocessConfigurationArray(array $prepend = [], array $append = []): array
    {
        $result = [];

        if (!is_array($this->config)) {
            $result['plain'] = $this->config;
        } else {

            // process prepended keys
            $this->appendConfigKeys($prepend, $result);

            // process other keys
            $orderedKeys = array_merge($prepend, $append);
            ksort($this->config, SORT_NUMERIC);
            foreach ($this->config as $key => $value) {
                $processedKey = $key === '_typoScriptNodeValue' ? 'plain' : $key;
                if (in_array($processedKey, $orderedKeys)) {
                    continue;
                }
                $result[$processedKey] = $value;
            }

            // process appended keys
            $this->appendConfigKeys($append, $result);
        }
        return $result;
    }
}
