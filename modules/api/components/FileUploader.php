<?php

namespace app\modules\api\components;

use Yii;

class FileUploader
{
	private $_file;
	private $_maximum_size;
	private $_allowed_extensions;
	private $_error;

	function __construct($file, $maximum_size = null, $allowed_extensions = null)
	{
		$this->_file = $file;
		$this->_maximum_size = $maximum_size ? $maximum_size : Yii::$app->params['file_size'];
		$this->_allowed_extensions = $allowed_extensions ? $allowed_extensions : Yii::$app->params['file_ext'];
	}

	public function extension() {
		return strtolower(end(explode('.', $this->_file['name'])));
	}

	public function filename() {
		return $this->_file['name'];
	}

	public function filetype()
	{
		return $this->_file['type'];
	}

	private function validateFile() {
		if ($this->_file['size'] > $this->_maximum_size) {
			$this->_error = 'File Size Exceeded';
			return false;
		}

		$extension = $this->extension();
		if (!in_array($extension, $this->_allowed_extensions)) {
			$this->_error = 'Inappropriate Extension';
			return false;
		}

		return true;
	}

	public function save($path = null)
	{
		if (!$this->validateFile()) {
			return [
				'success' => 0,
				'error' => $this->_error,
			];
		}

		if (!$path) {
			$path = Yii::$app->basePath . '/web/images/' . $this->_file['name'];
		}

		move_uploaded_file($this->_file['tmp_name'], $path);

		return [
			'success' => 1,
		];
	}
}
