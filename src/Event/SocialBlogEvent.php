<?php

namespace BlueSpice\Social\Blog\Event;

use BlueSpice\Social\Blog\Entity\Blog as BlogEntity;
use BlueSpice\Social\Event\SocialEvent;
use Message;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\EventLink;

class SocialBlogEvent extends SocialEvent {

	/**
	 * @return Message
	 */
	public function getKeyMessage(): Message {
		return Message::newFromKey( "bs-social-event-blog-$this->action-desc" );
	}

	/**
	 * @inheritDoc
	 */
	public function getMessage( IChannel $forChannel ): Message {
		return Message::newFromKey( "bs-social-event-blog-$this->action" )->params(
			$this->getAgent()->getName(),
			$this->entity->get( BlogEntity::ATTR_BLOG_TITLE )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getLinks( IChannel $forChannel ): array {
		return [
			new EventLink(
				$this->getTitle()->getFullURL(),
				Message::newFromKey( 'bs-social-notification-blog-primary-link-label' )
			)
		];
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'bs-social-event-blog';
	}
}
