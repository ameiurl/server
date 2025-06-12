<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Validator;

use Butterfly\Utility\Str;

/**
 * 数据验证
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Validator
{
    /**
     * 验证规则
     *
     * @var array
     */
    protected $rules = [];

    /**
     * 需要验证的数据
     *
     * @var array
     */
    protected $input = [];

    /**
     * 各个字段对应的标签
     *
     * @var array
     */
    protected $labels = [];

    /**
     * 自定义验证规则
     *
     * @var array
     */
    protected static $validators = [];

    /**
     * 错误信息
     *
     * @var array
     */
    protected $errors = [];

    /**
     * 集合类型验证（验证规则的参数个数是不确定的）
     *
     * @var array
     */
    protected $aggregateRules = ['in', 'not_in'];

    /**
     * 自定义错误信息
     *
     * @var array
     */
    protected static $customMessages = [];

    /**
     * 默认的错误信息
     *
     * @var array
     */
    protected static $defaultMessages = [
        'required'     => '%s 字段不能为空',
        'regex'        => '%s 格式无效',
        'email'        => '%s 必须是有效的电子邮件地址',
        'url'          => '%s 必须是合法的网址',
        'ip'           => '%s 必须是有效的IP地址',
        'ipv4'         => '%s 必须是有效的IP地址',
        'ipv6'         => '%s 必须是有效的IP v6 地址',
        'mobile_phone' => '%s 必须是有效的手机号码',
        'equal_to'     => '%1$s 必须等于 %2$s',
        'identifier'   => '%1$s 必须以字母开头，由 %2$s - %3$s 个字母、数字或下划线组成',
        'number'       => '%s 必须是整数或小数',
        'digits'       => '%s 只允许包含数字',
        'upper'        => '%s 只允许包含大写字母',
        'lower'        => '%s 只允许包含小写字母',
        'alpha'        => '%s 只允许包含字母',
        'alpha_num'    => '%s 只允许包含字母、数字',
        'alpha_dash'   => '%s 只允许包含字母、数字、下划线或连字符“-”',
        'min'          => '%1$s 最小须为 %2$s',
        'max'          => '%1$s 最大须为 %2$s',
        'min_length'   => '%1$s 最小长度须为 %2$s',
        'max_length'   => '%1$s 最大长度须为 %2$s',
        'in'           => '%1$s 必须使用值： %2$s',
        'not_in'       => '%1$s 不能使用值： %2$s',
        'range'        => '%1$s 必须在 %2$s - %3$s 之间',
        'rangelength'  => '%1$s 长度必须在 %2$s - %3$s 之间',
        'unique'       => '%s 已经存在',
        'exists'       => '%s 不存在',
        'nospace'      => '%s 不能包含空格',
        'classname'    => '%s 必须以大写字母开头，字母、数字、反斜杠组成',
    ];

    /**
     * 数据验证方法类
     *
     * @var string
     */
    protected static $validClass = '\Butterfly\Validator\Valid';

    /**
     * 构造函数
     *
     * @param array $input  需要验证的数据
     * @param array $rules  验证规则
     * @param array $labels 各个字段对应的标签
     */
    public function __construct(array $input, array $rules, array $labels)
    {
        $this->input  = $input + array_fill_keys(array_keys($rules), null);
        $this->rules  = $rules;
        $this->labels = $labels;

        unset($this->input['*']);
    }

    /**
     * 注册自定义验证规则
     *
     * @param string   $ruleName
     * @param callable $validator
     */
    public static function register(string $ruleName, callable $validator)
    {
        static::$validators[$ruleName] = $validator;
    }

    /**
     * 获取指定规则名称对应的错误信息
     *
     * @param string $ruleName
     * @return string
     */
    public static function message(string $ruleName)
    {
        if (isset(static::$defaultMessages[$ruleName])) {
            return static::$defaultMessages[$ruleName];
        } else if (isset(static::$customMessages[$ruleName])) {
            return static::$customMessages[$ruleName];
        } else {
            return '%s does not satisfy rule： ' . $ruleName;
        }
    }

    /**
     * 获取指定规则名称对应的错误信息
     *
     * @param string $ruleName
     * @param string $message
     */
    public static function setMessage(string $ruleName, string $message)
    {
        static::$customMessages[$ruleName] = $message;
    }

    /**
     * 解析所有的验证规则
     *
     * @return array
     */
    public function parseRules()
    {
        $parsedRules = [];
        foreach ($this->rules as $field => $rules) {
            if (is_string($rules)) {
                $rules = str_getcsv(trim($rules, '|'), '|');
            }

            foreach ($rules as $rule) {
                list($rule, $parameters) = $this->parseRule($rule);
                $validator = ['name' => $rule, 'parameters' => $parameters];
                if ($field === '*') {
                    $fields = array_keys($this->input);
                    foreach ($fields as $field) {
                        $parsedRules[$field][$rule] = $validator;
                    }
                } else {
                    $parsedRules[$field][$rule] = $validator;
                }
            }
        }

        return $parsedRules;
    }

    /**
     * 解析验证规则
     *
     * @param string $rule
     * @return array
     */
    protected function parseRule($rule)
    {
        $parameters = [];
        if (strpos($rule, ':') !== false) {
            list($ruleName, $parameter) = explode(':', $rule, 2);
            $parameters = $this->parseParameters($rule, $parameter);
        } else {
            $ruleName = $rule;
        }

        return [$ruleName, $parameters];
    }

    /**
     * 解析验证规则的参数
     *
     * @param string $rule
     * @param string $parameter
     * @return array
     */
    protected function parseParameters($rule, $parameter)
    {
        if (strtolower($rule) === 'regex') {
            return [$parameter];
        }

        return str_getcsv($parameter);
    }

    /**
     * 处理所有的验证规则
     */
    protected function process()
    {
        foreach ($this->parseRules() as $field => $validators) {
            if (in_array($this->input[$field], ['', null, []]) &&
                !isset($validators['required'])
            ) {
                continue;  // 非必填项
            }

            foreach ($validators as $validator) {
                if ($this->validate($field, $validator) === false) {
                    break;
                }
            }
        }
    }

    /**
     * 执行某个验证规则
     *
     * @param string $field
     * @param array  $validator
     */
    protected function validate(string $field, array $validator)
    {
        $ruleName   = $validator['name'];
        $fieldValue = $this->input[$field];
        $parameters = $validator['parameters'];

        $ruleMethod = Str::pascal($ruleName);
        $internalMethod = 'validate' . $ruleMethod;
        $externalMethod = lcfirst($ruleMethod);

        $valid = static::$validClass;
        if (method_exists($valid, $externalMethod)) {
            // http://cn2.php.net/manual/en/function.method-exists.php#111391
            // Note that prepending the namespace (if any) is required
            // even if the calling class is in the same namespace
            $passed = Valid::$externalMethod($fieldValue, $parameters);
        } elseif (method_exists($this, $internalMethod)) {
            $passed = $this->$internalMethod($fieldValue, $parameters);
        } elseif (isset(static::$validators[$ruleName])) {
            $callable = static::$validators[$ruleName];
            $passed   = $callable($fieldValue, $parameters, $field);
        } else {
            throw new \RuntimeException(vsprintf("%s(): Call to undefined validation rule '%s'.",
                [__METHOD__, $ruleName]));
        }

        if (!$passed) {
            $this->addError($field, $ruleName, $parameters);
        }

        return $passed;
    }

    /**
     * 添加错误信息
     *
     * @param string $field
     * @param string $ruleName
     * @param array  $parameters
     */
    protected function addError(
        string $field,
        string $ruleName,
        array $parameters
    ) {
        $message = static::message($ruleName);
        if (in_array($ruleName, $this->aggregateRules)) {
            $parameters = [implode(',', $parameters)];
        }
        $fieldLabel = $this->fieldLabel($field);

        if ($ruleName === 'equal_to') {
            $fieldNames = [
                $fieldLabel,
                $this->fieldLabel(array_shift($parameters))
            ];
        } else {
            $fieldNames = [$fieldLabel];
        }

        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $params                 = array_merge($fieldNames, $parameters);
        $this->errors[$field][] = sprintf($message, ...$params);
    }

    /**
     * 获取字段名对应的标签（用于错误信息的显示）
     *
     * @param string $field
     * @return string
     */
    protected function fieldLabel(string $field)
    {
        return $this->labels[$field] ?? $field;
    }

    /**
     * 判断是否所有的验证规则都验证通过
     */
    public function isValid()
    {
        $this->process();

        return empty($this->errors);
    }

    /**
     * 判断是否有验证规则验证不通过
     */
    public function isInValid()
    {
        $this->process();

        return !empty($this->errors);
    }

    /**
     * 获取所有的错误信息
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * 验证两值是否相等
     *
     * @param  string $value
     * @param  array  $parameters
     * @return bool
     */
    public function validateEqualTo($value, $parameters)
    {
        $other = $parameters[0];

        return array_key_exists($other,
            $this->input) && $value == $this->input[$other];
    }
}
