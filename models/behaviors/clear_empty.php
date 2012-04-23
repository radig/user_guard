<?php
/**
 * Seta para nulo ou valor correspondente (default, '', 0) cada
 * campo do modelo que venha como uma string vazia, de acordo
 * com o tipo do campo.
 *
 */
class ClearEmptyBehavior extends ModelBehavior
{
	/**
	 * Configurações default
	 * @var array
	 */
	protected $_defaultSettings = array(
		'ignoreFields' => array('id')
	);

	/**
	 * Configurações em uso
	 * @var array
	 */
	public $settings;

	/**
	 * Inilicializador invocado pelo CakePHP
	 *
	 * Opções de configuração:
	 *    ignoreFields      : Array of model specific fields
	 *
	 * @param Object $Model
	 * @param array $config
	 */
	public function setup(&$Model, $config = array())
	{
		$this->settings[$Model->alias]= array_merge($this->_defaultSettings, $config);
	}

	/**
	 *
	 * @param Model $Model
	 * @return bool
	 */
	public function beforeSave(&$Model)
	{
		$this->clearEmpty($Model);

		return parent::beforeSave($Model);
	}

	public function beforeValidate(&$Model)
	{
		$this->clearEmpty($Model);

		return parent::beforeValidate($Model);
	}

	/**
	 * Atribui o valor defaults dos campos que estão vazios, de acordo
	 * com seu tipo.
	 *
	 * @param Model &$Model Modelo corrente
	 * @return void
	 */
	public function clearEmpty(&$Model)
	{
		foreach($Model->_schema as $field => $v)
		{
			if(!isset($Model->data[$Model->alias][$field]))
				continue;

			if(!empty($Model->data[$Model->alias][$field]) || $Model->data[$Model->alias][$field] === false)
				continue;

			if(in_array($field, $this->settings[$Model->alias]['ignoreFields']))
				continue;

			if($v['null'])
			{
				$Model->data[$Model->alias][$field] = null;
				continue;
			}

			switch($v['type'])
			{
				case 'number':
				case 'integer':
					$Model->data[$Model->alias][$field] = $v['default'] ?: 0;
					break;
				case 'string':
				case 'text':
					$Model->data[$Model->alias][$field] = $v['default'] ?: '';
			}
		}
	}
}