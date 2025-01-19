<?php

namespace WishgranterProject\DescriptivePlaylist\Utils;

/**
 * A class to wrap around a \stdClass and provide basic validation.
 */
abstract class StdClassWrapper
{
    /**
     * @var \stdClass
     *   The actual data.
     */
    protected \stdClass $data;

    /**
     * @var array
     *   Describes the schema to validate the data.
     */
    protected array $schema = [
        'uuid' => [
            'required',
            'is:string',
            'length:36'
        ],
        'name' => [
            'is:string',
            'maxLength:255'
        ],
        'age' => [
            'is:int'
        ],
        'favouriteBand' => [
            'is:string|string[]',
            'maxLength:255',
        ]
    ];

    /**
     * Constructor.
     *
     * @param $data
     *   The data to wrap around.
     */
    public function __construct($data = null)
    {
        if ($data === null) {
            $this->data = new \stdClass();
        } elseif ($data instanceof \stdClass) {
            $this->data = $data;
        } else {
            throw new \InvalidArgumentException('Inform an object');
        }
    }

    /**
     * Encodes the data as a json string.
     *
     * @return string
     *   Json string.
     */
    public function __toString(): string
    {
        return json_encode($this->data);
    }

    /**
     * Sets a property.
     *
     * @param string $propertyName
     *   The property name.
     * @param mixed $propertyValue
     *   The value.
     *
     * @throws \InvalidArgumentException
     *   If the property or value are invalid.
     */
    public function __set(string $property, mixed $value): void
    {
        $this->setProperty($property, $value);
    }

    /**
     * Unsets a property.
     *
     * @param string $propertyName
     *   The name of the property.
     */
    public function __unset(string $property): void
    {
        unset($this->data->{$property});
    }

    /**
     * Retrieves a property.
     *
     * @param string $propertyName
     *   The property name.
     *
     * @return mixed
     *   The property value, null if the property is not there...
     *   or if it is just null ...
     */
    public function __get(string $propertyName)
    {
        return $this->getProperty($propertyName);
    }

    /**
     * Checks if a property is set.
     *
     * @param string $propertyName
     *   The property name.
     *
     * @return bool
     *   True if it is set.
     */
    public function __isset(string $propertyName): bool
    {
        return !empty($this->data->{$propertyName});
    }

    /**
     * Checks if the data is valid.
     *
     * @param array &$errors
     *   Will be populated with error messages.
     *
     * @return bool
     *   True if valid, false otherwise.
     */
    public function isValid(&$errors = []): bool
    {
        $errors = (array) $errors;
        if (! $this->requiredPropertiesAreSet($missingProperties)) {
            $errors[] = 'The following properties are missing: ' . implode(', ', $missingProperties);
        }

        foreach ($this->getSetPropertiesNames() as $propertyName) {
            $this->validateProperty($propertyName, $this->data->{$propertyName}, $prpErros);
            $errors = array_merge($errors, $prpErros);
        }

        return count($errors) == 0;
    }

    /**
     * Checks if a property/value pair is valid.
     *
     * Public alias fo ::validateProperty().
     *
     * @param string $propertyName
     *   Property name.
     * @param mixed $value
     *   The value.
     * @param array &$errors
     *   Will be populated with error messages.
     *
     * @return bool
     *   True if both are valid.
     */
    public function isValidProperty($propertyName, $value, &$errors = []): bool
    {
        return $this->validateProperty($propertyName, $value, $errors);
    }

    /**
     * Sanitize and tidy up data.
     *
     * Unsets invalid or empty properties and transforms one-item-long arrays
     * into single strings/numbers ( when applicable ).
     */
    public function sanitize(): void
    {
        foreach ($this->data as $propertyName => $propertyValue) {
            if (is_array($propertyValue)) {
                $propertyValue = array_filter($propertyValue, function ($i) {
                    return !empty($i) || $i === 0 || $i === '0';
                });
                $propertyValue = array_values($propertyValue);

                // Transform one-item-long array into a single value.
                $propertyValue = count($propertyValue) == 1 && $this->validateProperty($propertyName, $propertyValue[0])
                    ? $propertyValue[0]
                    : $propertyValue;

                $this->data->{$propertyName} = $propertyValue;
            }

            // Unset empty properties.
            if (empty($propertyValue) && $propertyValue !== 0 && $propertyValue !== '0') {
                unset($this->data->{$propertyName});
            }

            // Unset invalid properties.
            if (! $this->validateProperty($propertyName, $propertyValue)) {
                unset($this->data->{$propertyName});
            }
        }
    }

    /**
     * Clears the data of all properties.
     *
     * $exceptFor the ones specified.
     *
     * @param string[] $exceptFor
     *   The exceptions.
     */
    public function empty(array $exceptFor = []): void
    {
        foreach ($this->data as $prp => $v) {
            if (!in_array($prp, $exceptFor)) {
                unset($this->data->{$prp});
            }
        }
    }

    /**
     * Returns a copy of the data.
     *
     * @return \stdClass
     *   The copy.
     */
    public function getCopyOfTheData(): \stdClass
    {
        return clone $this->data;
    }

    /**
     * Retrieves a property.
     *
     * @param string $propertyName
     *   The property name.
     *
     * @return mixed
     *   The property value, null if the property is not there...
     *   or it is just null ...
     */
    public function getProperty(string $propertyName)
    {
        return isset($this->data->{$propertyName})
            ? $this->data->{$propertyName}
            : null;
    }

    /**
     * Sets a property.
     *
     * @param string $propertyName
     *   The property name.
     * @param mixed $propertyValue
     *   The value.
     *
     * @throws \InvalidArgumentException
     *   If the property or value are invalid.
     */
    public function setProperty(string $propertyName, $propertyValue): void
    {
        if ($this->validateProperty($propertyName, $propertyValue, $errors)) {
            $this->data->{$propertyName} = $propertyValue;
            return;
        }

        throw new \InvalidArgumentException(implode(', ', $errors));
    }

    /**
     * Retrieves the names of all properties currently set.
     *
     * @return string[]
     *   Property names.
     */
    public function getSetPropertiesNames(): array
    {
        return array_keys(get_object_vars($this->data));
    }

    /**
     * Checks if the required properties are set.
     *
     * @param string[] &$missingProperties
     *   Will be popualted with the property names that should be set but aren't.
     *
     * @return bool
     *   True if all of them are set.
     */
    public function requiredPropertiesAreSet(&$missingProperties): bool
    {
        $missingProperties = [];

        foreach ($this->getRequeridProperties() as $prpName) {
            if (!isset($this->data->{$prpName}) || empty($this->data->{$prpName})) {
                $missingProperties[] = $prpName;
            }
        }

        return count($missingProperties) == 0;
    }

    /**
     * Retrieves the name of the properties that are required.
     *
     * @return string[]
     *   Property names.
     */
    public function getRequeridProperties(): array
    {
        return array_filter($this->schema, function ($item) {
            return isset($item['required']) && $item['required'];
        });
    }

    /**
     * Checks if a $propertyName is valid.
     *
     * @param string $propertyName
     *   Property name.
     *
     * @return bool
     *   True if it is valid.
     */
    public function isValidPropertyName(string $propertyName): bool
    {
        return $this->isCanonicalProperty($propertyName) || $this->isValidCustomPropertyName($propertyName);
    }

    /**
     * Checks if a string is a valid custom property name.
     *
     * "xxx" prefixed alphanamerical string, 103 characters long max.
     *
     * @param string $propertyName
     *   Property name.
     *
     * @return bool
     *   True if it is valid.
     */
    public function isValidCustomPropertyName(string $propertyName): bool
    {
        return preg_match('/xxx[\w]{1,100}$/', $propertyName);
    }

    /**
     * Checks if a string is a valid property name.
     *
     * @param string $propertyName
     *   Property name.
     *
     * @return bool
     *   True if it is valid.
     */
    public function isCanonicalProperty(string $propertyName): bool
    {
        return in_array($propertyName, $this->getCanonicalProperties());
    }

    /**
     * Retrieves properties defined in the schema.
     *
     * @return string[]
     *   Property names.
     */
    public function getCanonicalProperties(): array
    {
        return array_keys($this->schema);
    }

    /**
     * Checks if a property/value pair is valid.
     *
     * @param string $propertyName
     *   Property name.
     * @param mixed $propertyValue
     *   The value.
     * @param array &$errors
     *   Will be populated with error messages.
     *
     * @return bool
     *   True if both are valid.
     */
    protected function validateProperty(string $propertyName, $propertyValue, &$errors = []): bool
    {
        $errors = [];
        if ($this->isCanonicalProperty($propertyName)) {
            return $this->validateCanonicalProperty(
                $propertyName,
                $propertyValue,
                $errors
            );
        } elseif ($this->isValidCustomPropertyName($propertyName)) {
            return $this->validateCustomProperty(
                $propertyName,
                $propertyValue,
                $errors
            );
        } else {
            $errors[] = 'unrecognized ' . $propertyName . ' property';
            return false;
        }
    }

    /**
     * Validates a property defined in the schema.
     *
     * @param string $propertyName
     *   Property name.
     * @param mixed $propertyValue
     *   The value.
     * @param array &$errors
     *   Will be populated with error messages.
     *
     * @return bool
     *   True if both are valid.
     */
    protected function validateCanonicalProperty(string $propertyName, mixed $propertyValue, &$errors = []): bool
    {
        $errors = [];
        $rules = array_filter($this->schema[$propertyName], function ($r) {
            return $r != 'required';
        });

        foreach ($rules as $rule) {
            if (! $this->validateRule($propertyName, $propertyValue, $er, $rule)) {
                $errors[] = $er;
            }
        }

        return count($errors) == 0;
    }

    /**
     * Validates a custom property.
     *
     * @param string $propertyName
     *   Property name.
     * @param mixed $propertyValue
     *   The value.
     * @param array &$errors
     *   Will be populated with error messages.
     *
     * @return bool
     *   True if both are valid.
     */
    protected function validateCustomProperty(string $propertyName, $propertyValue, &$errors = []): bool
    {
        $this->validateRule($propertyName, $propertyValue, $error, 'is:null|alphanumeric|alphanumeric[]');
        $this->validateRule($propertyName, $propertyValue, $error, 'maxLength:255');
        $errors = empty($error) ? [] : (array) $error;
        return empty($error);
    }

    /**
     * Validates a property against a rule.
     *
     * @param string $propertyName
     *   Property name.
     * @param mixed $propertyValue
     *   The value.
     * @param array &$errors
     *   Will be populated with error messages.
     * @param string $rule
     *   A string identifying a rule.
     *
     * @return bool
     *   True if both are valid.
     */
    protected function validateRule(string $propertyName, $propertyValue, &$error, string $rule): bool
    {
        $error = '';
        $parameters = explode(':', $rule);
        $method = array_shift($parameters);

        array_walk($parameters, function (&$p) {
            $p = substr_count($p, '|') ? explode('|', $p) : $p;
        });

        array_unshift($parameters, $propertyName, $propertyValue);

        $error = call_user_func_array([$this, $method], $parameters);

        return $error == '';
    }

    /**
     * Max lenght validation.
     *
     * @param string $propertyName
     *   Property name.
     * @param mixed $propertyValue
     *   The value.
     * @param int $maxLength
     *   Max length.
     *
     * @return string
     *   Error message.
     */
    protected function maxLength($propertyName, $propertyValue, $maxLength): string
    {
        if ($this->strLength($propertyValue) > $maxLength) {
            return $propertyName . ' should not be longer than ' . $maxLength . ' characters';
        }

        return '';
    }

    /**
     * Lenght validation.
     *
     * @param string $propertyName
     *   Property name.
     * @param mixed $propertyValue
     *   The value.
     * @param int $length
     *   Length.
     *
     * @return string
     *   Error message.
     */
    protected function length(string $propertyName, $propertyValue, $length): string
    {
        if ($this->strLength($propertyValue) != $length) {
            return $propertyName . ' should be ' . $length . ' characters long';
        }

        return '';
    }

    /**
     * Type validation.
     *
     * @param string $propertyName
     *   Property name.
     * @param mixed $propertyValue
     *   The value.
     * @param string|array $types
     *   Valid types.
     *
     * @return string
     *   Error message.
     */
    protected function is(string $propertyName, $propertyValue, $types): string
    {
        $types = (array) $types;
        foreach ($types as $type) {
            if (Helpers::is($propertyValue, $type)) {
                return '';
            }
        }

        return $propertyName . ' should be of the types: ' . implode(', ', $types);
    }

    /**
     * Gets the lengths of a string/array of strings.
     *
     * @param string|array $data
     *   String or array of strings.
     *
     * @return int
     *   Length.
     */
    protected function strLength($data): int
    {
        $length = 0;
        foreach ((array) $data as $str) {
            $length += strlen($str);
        }

        return $length;
    }

    /**
     * Instantiates a new class out of a json string.
     *
     * @param null|string Json
     *   Json string.
     *
     * @return WishgranterProject\DescriptivePlaylist\Utils\StdClassWrapper
     *   An object or null if it cannot decode the string.
     */
    public static function createFromJson($json): ?StdClassWrapper
    {
        if ($json === null) {
            return null;
        }

        $json = rtrim($json, "\n");

        if ($json == '') {
            return null;
        }

        $data = json_decode($json, false);

        if ($data === null) {
            return null;
        }

        $class = get_called_class();
        return new $class($data);
    }
}
