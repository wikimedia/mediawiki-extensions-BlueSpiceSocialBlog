<?php

namespace BlueSpice\Social\Blog\ConfigDefinition;

class BSSocialUseBlogTeaser extends \BlueSpice\ConfigDefinition\BooleanSetting {

	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_PERSONALISATION . '/BlueSpiceSocialBlog',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceSocialBlog/' . static::FEATURE_PERSONALISATION,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_PRO . '/BlueSpiceSocialBlog',
		];
	}

	public function getLabelMessageKey() {
		return 'bs-socialblog-toc-useblogteaser';
	}

	public function isRLConfigVar() {
		return true;
	}

}
