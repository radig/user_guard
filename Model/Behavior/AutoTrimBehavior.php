<?php
App::uses('ModelBehavior', 'Model');
/**
 * Behavior que remove automaticamente espaços que podem ser inseridos
 * acidentalmente pelo usuário.
 *
 * PHP version > 5.3.1
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package radig.UserGuard
 * @subpackage Model.Behavior
 */
class AutoTrimBehavior extends ModelBehavior {

	private $_Model;

	/**
	 * Lista com nomes dos modelos nos quais o behavior
	 * não fará conversão.
	 *
	 * Isso permite a compatibilidade com datasources que
	 * não possuem tipos definidos.
	 *
	 * @var array
	 */
	private $_disabledFor = array();

	/**
	 * Inicializa os dados do behavior
	 *
	 * @see ModelBehavior::setup()
	 */
	public function setup(Model $model, $config = array())
	{
		$db = $model->getDataSource();

		if (!isset($db->columns) || is_a($db, 'MongodbSource')) {
			$this->_disabledFor[$model->alias] = true;
		}
	}

	/**
	* Trim através do callback beforeValidate
	*
	* @see ModelBehavior::beforeValidate()
	*/
	public function beforeValidate(Model $model, $options = array())
	{
		parent::beforeValidate($model, $options);

		$this->_Model = $model;

		if (isset($this->_disabledFor[$model->alias])) {
			return true;
		}

		$this->_autoTrim();

		return true;
	}

	/**
	 * Trim através do callback beforeSave
	 *
	 * @see ModelBehavior::beforeSave()
	 */
	public function beforeSave(Model $model, $options = array())
	{
		parent::beforeSave($model, $options);

		$this->_Model = $model;

		if (isset($this->_disabledFor[$model->alias])) {
			return true;
		}

		$this->_autoTrim();

		return true;
	}

	/**
	 * Trim das informações no callback beforeFind
	 *
	 * @see ModelBehavior::beforeFind()
	 */
	public function beforeFind(Model $model, $query)
	{
		parent::beforeFind($model, $query);

		$this->_Model = $model;

		if (!isset($this->_disabledFor[$model->alias])) {
			$this->_autoTrim($query['conditions']);
		}

		$this->_autoTrim();

		return $query;
	}

	private function _autoTrim(&$query = null)
	{
		// verifica se há dados setados no modelo
		if (isset($this->_Model->data[$this->_Model->alias]) && !empty($this->_Model->data[$this->_Model->alias])) {
			// varre os dados setados
			foreach ($this->_Model->data[$this->_Model->alias] as $field => $value) {
				// caso o campo esteja vazio E não tenha um array como valor E o campo faz parte do schema
				if (!empty($value) && !is_array($value) && isset($this->_Model->_schema[$field])) {
					switch ($this->_Model->_schema[$field]['type']) {
						case 'string':
						case 'text':
							$this->_Model->data[$this->_Model->alias][$field] = trim($this->_Model->data[$this->_Model->alias][$field]);
					}
				}
			}
		}

		// caso tenha sido invocado em um Find (haja query de busca)
		if (!empty($query) && is_array($query)) {
			// varre os campos da condição
			foreach ($query as $field => &$value) {
				if (strtolower($field) === 'or' || strtolower($field) === 'and' || is_numeric($field)) {
					$this->_autoTrim($value);
					continue;
				}

				// notação Model.field
				if (strpos($field, '.') !== false) {
					list($modelName, $field) = explode('.', $field, 2);
					$hasSpaceAfter = (strpos($field, ' ') !== false);

					if ($hasSpaceAfter) {
						list($field,) = explode(' ', $field, 2);
					}
				}

				$modelSchema = $this->_Model->schema();

				// campo esteja vazio E não tenha um array como valor
				if (!empty($value) && isset($modelSchema[$field]) && !is_array($value)) {
					switch ($modelSchema[$field]['type']) {
						case 'string':
						case 'text':
							$value = trim($value);
					}
				}
			}
		}
	}
}
