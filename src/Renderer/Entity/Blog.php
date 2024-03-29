<?php

namespace BlueSpice\Social\Blog\Renderer\Entity;

class Blog extends \BlueSpice\Social\Renderer\Entity\Text {

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_content( $val ) {
		if ( $this->renderType !== static::RENDER_TYPE_DEFAULT ) {
			return parent::render_content( $val );
		}

		if ( !$this->getEntity()->getConfig()->get( 'BSSocialUseBlogTeaser' ) ) {
			return parent::render_content( $val );
		}
		return '';
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_teasertext( $val ) {
		if ( $this->renderType !== static::RENDER_TYPE_DEFAULT || empty( $val ) ) {
			return parent::render_content( $val );
		}
		if ( $this->getEntity()->getConfig()->get( 'BSSocialUseBlogTeaser' ) ) {
			$teaserTextParsed = $this->getEntity()->get(
				\BlueSpice\Social\Blog\Entity\Blog::ATTR_TEASER_TEXT_PARSED,
				''
			);

			return nl2br( $teaserTextParsed );
		}
		return '';
	}
}
