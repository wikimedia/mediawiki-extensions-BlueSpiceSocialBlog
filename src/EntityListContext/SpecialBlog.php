<?php

namespace BlueSpice\Social\Blog\EntityListContext;

use BlueSpice\Services;
use BlueSpice\Social\Blog\Entity\Blog;

class SpecialBlog extends \BlueSpice\Social\EntityListContext {
	const CONFIG_NAME_OUTPUT_TYPE = 'EntityListSpecialBlogOutputType';
	const CONFIG_NAME_TYPE_ALLOWED = 'EntityListSpecialBlogTypeAllowed';
	const CONFIG_NAME_TYPE_SELECTED = 'EntityListSpecialBlogTypeSelected';

	/**
	 * Owner of the user page
	 * @var \User
	 */
	protected $owner = null;

	public function getLimit() {
		return 5;
	}

	public function getLockedFilterNames() {
		return array_merge(
			parent::getLockedFilterNames(),
			[ Blog::ATTR_TYPE ]
		);
	}

	protected function getSortProperty() {
		return Blog::ATTR_TIMESTAMP_CREATED;
	}

	public function getOutputTypes() {
		return array_merge( parent::getOutputTypes(), [
			'blog' => 'Page'
		]);
	}

	public function getPreloadedEntities() {
		$preloaded = parent::getPreloadedEntities();
		$blog = Services::getInstance()->getBSEntityFactory()->newFromObject(
			$this->getRawBlog()
		);
		if( !$blog instanceof Blog ) {
			return $preloaded;
		}

		$status = $blog->userCan( 'create', $this->getUser() );
		if( !$status->isOK() ) {
			return $preloaded;
		}

		$preloaded[] = $this->getRawBlog();
		return $preloaded;
	}

	protected function getRawBlog() {
		return (object) [
			Blog::ATTR_TYPE => Blog::TYPE,
		];
	}

	/**
	 *
	 * @return boolean
	 */
	public function showEntitySpawner() {
		return false;
	}

}
