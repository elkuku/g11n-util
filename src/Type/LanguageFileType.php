<?php
/**
 * Date: 28/06/18
 *
 * @copyright 2018 Nikolai Plath
 * @license   http://www.wtfpl.net WTFPL
 */

namespace ElKuKu\G11nUtil\Type;

/**
 * Class LanguageFileType
 * @since 1.0
 */
class LanguageFileType
{
	/**
	 * @var string
	 */
	public $extension = '';

	/**
	 * @var string
	 */
	public $extensionDir = '';

	/**
	 * @var string
	 */
	public $domain = '';

	/**
	 * @var string
	 */
	public $lang = '';

	/**
	 * @var string
	 */
	public $templatePath = '';

	/**
	 * @param string $extension
	 *
	 * @return LanguageFileType
	 */
	public function setExtension(string $extension): LanguageFileType
	{
		$this->extension = $extension;

		return $this;
	}

	/**
	 * @param string $domain
	 *
	 * @return LanguageFileType
	 */
	public function setDomain(string $domain): LanguageFileType
	{
		$this->domain = $domain;

		return $this;
	}

	/**
	 * @param string $lang
	 *
	 * @return LanguageFileType
	 */
	public function setLang(string $lang): LanguageFileType
	{
		$this->lang = $lang;

		return $this;
	}

	/**
	 * @param string $templatePath
	 *
	 * @return LanguageFileType
	 */
	public function setTemplatePath(string $templatePath): LanguageFileType
	{
		$this->templatePath = $templatePath;

		return $this;
	}
}
