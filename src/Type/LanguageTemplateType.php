<?php
/**
 * Date: 28/06/18
 *
 * @copyright 2018 Nikolai Plath
 * @license   http://www.wtfpl.net WTFPL
 */

namespace ElKuKu\G11nUtil\Type;

/**
 * Class LanguageTemplateType
 * @since 1.0
 */
class LanguageTemplateType
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
	public $type = '';

	/**
	 * @var array
	 */
	public $paths = [];

	/**
	 * @var array
	 */
	public $excludes = [];

	/**
	 * @var string
	 */
	public $templatePath = '';

	/**
	 * @var string
	 */
	public $copyrightHolder = '';

	/**
	 * @var string
	 */
	public $packageName = '';

	/**
	 * @var string
	 */
	public $packageVersion = '';

	/**
	 * @param string $copyrightHolder
	 *
	 * @return LanguageTemplateType
	 */
	public function setCopyrightHolder(string $copyrightHolder): LanguageTemplateType
	{
		$this->copyrightHolder = $copyrightHolder;

		return $this;
	}

	/**
	 * @param string $packageName
	 *
	 * @return LanguageTemplateType
	 */
	public function setPackageName(string $packageName): LanguageTemplateType
	{
		$this->packageName = $packageName;

		return $this;
	}

	/**
	 * @param string $packageVersion
	 *
	 * @return LanguageTemplateType
	 */
	public function setPackageVersion(string $packageVersion): LanguageTemplateType
	{
		$this->packageVersion = $packageVersion;

		return $this;
	}

	/**
	 * @param string $extension
	 *
	 * @return LanguageTemplateType
	 */
	public function setExtension(string $extension): LanguageTemplateType
	{
		$this->extension = $extension;

		return $this;
	}

	/**
	 * @param string $domain
	 *
	 * @return LanguageTemplateType
	 */
	public function setDomain(string $domain): LanguageTemplateType
	{
		$this->domain = $domain;

		return $this;
	}

	/**
	 * @param string $type
	 *
	 * @return LanguageTemplateType
	 */
	public function setType(string $type): LanguageTemplateType
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * @param array $paths
	 *
	 * @return LanguageTemplateType
	 */
	public function setPaths(array $paths): LanguageTemplateType
	{
		$this->paths = $paths;

		return $this;
	}

	/**
	 * @param string $templatePath
	 *
	 * @return LanguageTemplateType
	 */
	public function setTemplatePath(string $templatePath): LanguageTemplateType
	{
		$this->templatePath = $templatePath;

		return $this;
	}

	/**
	 * @param string $extensionDir
	 *
	 * @return LanguageTemplateType
	 */
	public function setExtensionDir(string $extensionDir): LanguageTemplateType
	{
		$this->extensionDir = $extensionDir;

		return $this;
	}

	/**
	 * @param array $excludes
	 *
	 * @return LanguageTemplateType
	 */
	public function setExcludes(array $excludes): LanguageTemplateType
	{
		$this->excludes = $excludes;

		return $this;
	}

}
