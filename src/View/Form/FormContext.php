<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Cake\View\Form;

use Cake\Http\ServerRequest;
use Cake\Utility\Hash;

/**
 * Provides a context provider for Cake\Form\Form instances.
 *
 * This context provider simply fulfils the interface requirements
 * that FormHelper has and allows access to the request data.
 */
class FormContext implements ContextInterface
{

    /**
     * The request object.
     *
     * @var \Cake\Http\ServerRequest
     */
    protected $_request;

    /**
     * The form object.
     *
     * @var \Cake\Form\Form
     */
    protected $_form;

    /**
     * Constructor.
     *
     * @param \Cake\Http\ServerRequest $request The request object.
     * @param array $context Context info.
     */
    public function __construct(ServerRequest $request, array $context)
    {
        $this->_request = $request;
        $context += [
            'entity' => null,
        ];
        $this->_form = $context['entity'];
    }

    /**
     * {@inheritDoc}
     */
    public function primaryKey()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function isPrimaryKey($field)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isCreate()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException
     */
    public function val($field, $options = [])
    {
        $options += [
            'default' => null,
            'schemaDefault' => true
        ];

        $val = $this->_request->getData($field);
        if ($val !== null) {
            return $val;
        }

        return $options['default'];
    }

    /**
     * {@inheritDoc}
     */
    public function isRequired($field)
    {
        $validator = $this->_form->validator();
        if (!$validator->hasField($field)) {
            return false;
        }
        if ($this->type($field) !== 'boolean') {
            return $validator->isEmptyAllowed($field, $this->isCreate()) === false;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function fieldNames()
    {
        return $this->_form->schema()->fields();
    }

    /**
     * {@inheritDoc}
     */
    public function type($field)
    {
        return $this->_form->schema()->fieldType($field);
    }

    /**
     * {@inheritDoc}
     */
    public function attributes($field)
    {
        $column = (array)$this->_form->schema()->field($field);
        $whitelist = ['length' => null, 'precision' => null];

        return array_intersect_key($column, $whitelist);
    }

    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException
     */
    public function hasError($field)
    {
        $errors = $this->error($field);

        return count($errors) > 0;
    }

    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException
     */
    public function error($field)
    {
        return array_values((array)Hash::get($this->_form->errors(), $field, []));
    }
}
