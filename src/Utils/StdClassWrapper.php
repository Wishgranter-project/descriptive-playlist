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
        $data = is_object($data) ? $data : new \stdClass();
        $this->data = $data;
    }

    public function __toString() : string
    {
        return json_encode($this->data);
    }

    public function __set($property, $value) : void
    {
        $this->setProperty($property, $value);
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
     * Clears invalid data
     */
    public function clear() : void
    {
        foreach ($this->data as $propertyName => $propertyValue) {
            if (! $this->validateProperty($propertyName, $propertyValue)) {
                unset($this->data->{$propertyName});
            }
        }
    }

    /**
     * Returns a "copy" of the stdClass.
     */
    public function getData() : \stdClass
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

        \trigger_error(implode(', ', $errors), \E_USER_ERROR);
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
        $valid = $this->validateRule($propertyName, $propertyValue, $error, 'is:null|alphanumeric|alphanumeric[]');
        $errors = empty($error) ? [] : (array) $error;
        return $valid;
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
