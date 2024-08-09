<?php

namespace BlueSpice\Social\Blog\Event;

use BlueSpice\EntityFactory;
use BlueSpice\Social\Blog\Entity\Blog as BlogEntity;
use BlueSpice\Social\Event\SocialEvent;
use MediaWiki\Permissions\GroupPermissionsLookup;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserIdentity;
use Message;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\EventLink;
use stdClass;
use TitleFactory;
use Wikimedia\Rdbms\ILoadBalancer;

class SocialBlogEvent extends SocialEvent {

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 * @param ILoadBalancer $lb
	 * @param UserFactory $userFactory
	 * @param GroupPermissionsLookup $gpl
	 * @param EntityFactory $entityFactory
	 * @param TitleFactory $titleFactory
	 * @param UserIdentity $agent
	 * @param stdClass $entityData
	 * @param string $action
	 */
	public function __construct(
		ILoadBalancer $lb, UserFactory $userFactory, GroupPermissionsLookup $gpl, EntityFactory $entityFactory,
		TitleFactory $titleFactory, UserIdentity $agent, stdClass $entityData, string $action = self::ACTION_EDIT
	) {
		$this->titleFactory = $titleFactory;
		parent::__construct( $lb, $userFactory, $gpl, $entityFactory, $agent, $entityData, $action );
	}

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
	 * @return \Title|null
	 */
	protected function getWatchedTitle() {
		// For blogs, related title is always Main_page, need to get the actual related page from tags
		$data = $this->entity->getFullData();
		if ( isset( $data['tags'] ) && count( $data['tags'] ) ) {
			foreach ( $data['tags'] as $tag ) {
				$title = $this->titleFactory->newFromText( $tag );
				if ( $title && !$title->isMainPage() ) {
					// Try to return any page but the main page, as its always assigned, mostly wrongly
					// If no other page is found, return the main page (as it will be related title)
					return $title;
				}
			}
		}

		return $this->entity->getRelatedTitle();
	}

	/**
	 * @inheritDoc
	 */
	public function getLinks( IChannel $forChannel ): array {
		$relevant = $this->doGetRelevantTitle();
		if ( !$relevant ) {
			return [];
		}
		return [
			new EventLink(
				$relevant->getFullURL(),
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
