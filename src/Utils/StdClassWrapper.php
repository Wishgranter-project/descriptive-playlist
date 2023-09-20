<?php 
namespace AdinanCenci\DescriptivePlaylist\Utils;

/**
 * A class to wrap around a \stdClass and provide basic validation.
 */
abstract class StdClassWrapper 
{
    protected \stdClass $data;

    protected $schema = [
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

    public function __construct($data = null) 
    {
        if ($data === null) {
            $this->data = new \stdClass();
        } else if ($data instanceof \stdClass) {
            $this->data = $data;
        } else {
            throw new \InvalidArgumentException('Inform an object');
        }
    }

    public function __toString() : string
    {
        return json_encode($this->data);
    }

    public function __set($property, $value) : void
    {
        $this->setProperty($property, $value);
    }

    public function __unset($property) : void 
    {
        unset($this->data->{$property});
    }

    public function __get($propertyName) 
    {
        return $this->getProperty($propertyName);
    }

    public function __isset($propertyName) : bool
    {
        return !empty($this->data->{$propertyName});
    }

    public function isValid(&$errors = []) : bool
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

    public function isValidProperty($propertyName, $value, &$errors = []) : bool
    {
        return $this->validateProperty($propertyName, $value, $errors);
    }

    /**
     * Sanitize and tidy up data.
     *
     * It unsets invalid or empty properties and transforms
     * one-item-long arrays into strings/numbers ( when applicable ).
     */
    public function sanitize() : void
    {
        foreach ($this->data as $propertyName => $propertyValue) {
            if (is_array($propertyValue)) {
                $propertyValue = array_filter($propertyValue, function($i) 
                {
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
     * Clears the stdClass object of all properties $exceptFor the ones specified.
     * 
     * @param string[] $exceptFor
     */
    public function empty($exceptFor = []) : void
    {
        foreach ($this->data as $prp => $v) {
            if (!in_array($prp, $exceptFor)) {
                unset($this->data->{$prp});
            }
        }
    }

    /**
     * Returns a copy of the stdClass.
     */
    public function getCopyOfTheData() : \stdClass
    {
        return clone $this->data;
    }

    public function getProperty(string $propertyName) 
    {
        return isset($this->data->{$propertyName})
            ? $this->data->{$propertyName}
            : null;
    }

    public function setProperty(string $propertyName, $propertyValue) : void
    {
        if ($this->validateProperty($propertyName, $propertyValue, $errors)) {
            $this->data->{$propertyName} = $propertyValue;
            return;
        }

        throw new \InvalidArgumentException(implode(', ', $errors));
    }

    /**
     * @return string[]
     */
    public function getSetPropertiesNames() : array
    {
        return array_keys(get_object_vars($this->data));
    }

    /**
     * @param string[] &$missingProperties Return the name of the properties that should be set but are not.
     * @return bool
     */
    public function requiredPropertiesAreSet(&$missingProperties) : bool
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
     * @return string[]
     */
    public function getRequeridProperties() : array
    {
        return array_filter($this->schema, function($item) 
        {
            return isset($item['required']) && $item['required'];
        });
    }

    public function isValidPropertyName(string $propertyName) : bool
    {
        return $this->isCanonicalProperty($propertyName) || $this->isValidCustomPropertyName($propertyName);
    }

    public function isValidCustomPropertyName(string $propertyName) : bool
    {
        return preg_match('/xxx[\w]{1,100}$/', $propertyName);
    }

    public function isCanonicalProperty(string $propertyName) : bool
    {
        return in_array($propertyName, $this->getCanonicalProperties());
    }

    /**
     * @return string[]
     */
    public function getCanonicalProperties() : array
    {
        return array_keys($this->schema);
    }

    protected function validateProperty(string $propertyName, $propertyValue, &$errors = []) : bool
    {
        $errors = [];
        if ($this->isCanonicalProperty($propertyName)) {

            return $this->validateCanonicalProperty($propertyName, $propertyValue, $errors);

        } else if ($this->isValidCustomPropertyName($propertyName)) {

            return $this->validateCustomProperty($propertyName, $propertyValue, $errors);

        } else {

            $errors[] = 'unrecognized ' . $propertyName . ' property';
            return false;

        }
    }

    protected function validateCanonicalProperty(string $propertyName, $propertyValue, &$errors = []) : bool
    {;
        $errors = [];
        $rules = array_filter($this->schema[$propertyName], function($r) { return $r != 'required'; });
        foreach ($rules as $rule) {
            if (! $this->validateRule($propertyName, $propertyValue, $er, $rule)) {
                $errors[] = $er;
            }
        }

        return count($errors) == 0;
    }

    protected function validateCustomProperty(string $propertyName, $propertyValue, &$errors = []) : bool
    {
        $this->validateRule($propertyName, $propertyValue, $error, 'is:null|alphanumeric|alphanumeric[]');
        $this->validateRule($propertyName, $propertyValue, $error, 'maxLength:255');
        $errors = empty($error) ? [] : (array) $error;
        return empty($error);
    }

    /**
     * @param string $propertyName
     * @param mixed $propertyValue
     * @param string &$error
     * @param string $rule
     * @return bool
     */
    protected function validateRule(string $propertyName, $propertyValue, &$error, string $rule) : bool
    {
        $error = '';
        $parameters = explode(':', $rule);
        $method = array_shift($parameters);

        array_walk($parameters, function(&$p) 
        {
            $p = substr_count($p, '|') ? explode('|', $p) : $p;
        });

        array_unshift($parameters, $propertyName, $propertyValue);

        $error = call_user_func_array([$this, $method], $parameters);

        return $error == '';
    }

    /**
     * Validating function. Returns an error message.
     * 
     * @param string $propertyName
     * @param mixed $propertyValue
     * @param int $maxLength
     * @return string
     */
    protected function maxLength($propertyName, $propertyValue, $maxLength) : string
    {
        if ($this->strLength($propertyValue) > $maxLength) {
            return $propertyName . ' should not be longer than ' . $maxLength . ' characters';
        }

        return '';
    }

    /**
     * Validating function. Returns an error message.
     * 
     * @param string $propertyName
     * @param mixed $propertyValue
     * @param int $length
     * @return string
     */
    protected function length(string $propertyName, $propertyValue, $length) : string
    {
        if ($this->strLength($propertyValue) != $length) {
            return $propertyName . ' should be ' . $length . ' characters long';
        }

        return '';
    }

    /**
     * Validating function. Returns an error message.
     * 
     * @param string $propertyName
     * @param mixed $propertyValue
     * @param string|string[] $types
     * @return string
     */
    protected function is(string $propertyName, $propertyValue, $types) : string
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
     * @param string|array
     * @return int
     */
    protected function strLength($data) : int
    {
        $length = 0;
        foreach((array) $data as $str) {
            $length += strlen($str);
        }

        return $length;
    }

    public static function createFromJson($json) 
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
