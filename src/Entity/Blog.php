<?php

/**
 * Blog class for BSSocial
 *
 * add desc
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocialBlog
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\Blog\Entity;

use Message;
use User;
use Status;
use Title;
use SpecialPage;
use BlueSpice\Social\Entity\Text;
use BlueSpice\Social\Parser\Teaser;
use BlueSpice\Social\Parser\Input;

/**
 * Blog class for BSSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocialBlog
 */
class Blog extends Text {
	const TYPE = 'blog';

	const ATTR_BLOG_TITLE = 'blogtitle';
	const ATTR_TEASER_TEXT = 'teasertext';

	/**
	 *
	 * @param string $attrName
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function get( $attrName, $default = null ) {
		if ( $attrName === static::ATTR_TEASER_TEXT ) {
			if ( empty( $this->attributes[static::ATTR_TEASER_TEXT] ) ) {
				$parser = new Teaser();
				$this->attributes[static::ATTR_TEASER_TEXT] = $parser->parse(
					$this->get( static::ATTR_PARSED_TEXT, '' )
				);
			}
		}
		return parent::get( $attrName, $default );
	}

	/**
	 * Returns the blogtitle attribute
	 * @deprecated since version 3.0.0 - use get( $attrName, $default ) instead
	 * @return string
	 */
	public function getBlogTitle() {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->get( static::ATTR_BLOG_TITLE, '' );
	}

	/**
	 * Returns the teasertext attribute
	 * @deprecated since version 3.0.0 - use get( $attrName, $default ) instead
	 * @return string
	 */
	public function getTeaserText() {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->get( static::ATTR_TEASER_TEXT, '' );
	}

	/**
	 * Sets the blogtitle attribute
	 * @deprecated since version 3.0.0 - use set( $attrName, $value ) instead
	 * @param string $sBlogTitle
	 * @return Blog
	 */
	public function setBlogTitle( $sBlogTitle ) {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->set( static::ATTR_BLOG_TITLE, $sBlogTitle );
	}

	/**
	 * Sets the teasertext attribute
	 * @deprecated since version 3.0.0 - use set( $attrName, $value ) instead
	 * @param string $sTeaserText
	 * @return Blog
	 */
	public function setTeaserText( $sTeaserText ) {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->set( static::ATTR_TEASER_TEXT, $sTeaserText );
	}

	/**
	 * Gets the attributes formated for the api
	 * @param array $a
	 * @return object
	 */
	public function getFullData( $a = [] ) {
		return parent::getFullData( array_merge(
			$a,
			[
				static::ATTR_BLOG_TITLE => $this->get(
					static::ATTR_BLOG_TITLE,
					''
				),
				static::ATTR_TEASER_TEXT => $this->get(
					static::ATTR_TEASER_TEXT,
					''
				),
			]
		) );
	}

	/**
	 *
	 * @param \stdClass $o
	 */
	public function setValuesByObject( \stdClass $o ) {
		if ( !empty( $o->{static::ATTR_BLOG_TITLE} ) ) {
			$this->set(
				static::ATTR_BLOG_TITLE,
				$o->{static::ATTR_BLOG_TITLE}
			);
		}
		if ( isset( $o->{static::ATTR_TEASER_TEXT} ) ) {
			$this->set(
				static::ATTR_TEASER_TEXT,
				$o->{static::ATTR_TEASER_TEXT}
			);
		}
		parent::setValuesByObject( $o );
	}

	/**
	 *
	 * @param Message|null $msg
	 * @return Message
	 */
	public function getHeader( $msg = null ) {
		$msg = parent::getHeader( $msg );
		return $msg->params( [
			$this->get( static::ATTR_BLOG_TITLE, '' ),
		] );
	}

	/**
	 *
	 * @param User|null $user
	 * @param array $options
	 * @return Status
	 */
	public function save( User $user = null, $options = [] ) {
		$parser = new Input;
		$this->set( static::ATTR_BLOG_TITLE,
			$parser->parse( $this->get( static::ATTR_BLOG_TITLE, '' ) )
		);
		if ( empty( $this->get( static::ATTR_BLOG_TITLE, '' ) ) ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_BLOG_TITLE )->plain()
			) );
		}
		$status = parent::save( $user, $options );
		if ( !$status->isOK() ) {
			return $status;
		}
		$this->set( static::ATTR_TEASER_TEXT,
			$parser->parse( $this->get( static::ATTR_TEASER_TEXT, '' ) )
		);
		return $status;
	}

	/**
	 *
	 * @return Title
	 */
	public function getBackLinkTitle() {
		return SpecialPage::getTitleFor( 'Blog' );
	}
}
