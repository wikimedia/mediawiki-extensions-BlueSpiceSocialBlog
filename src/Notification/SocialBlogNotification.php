<?php

namespace BlueSpice\Social\Blog\Notification;

use BlueSpice\Social\Notifications\SocialNotification;
use BlueSpice\Social\Blog\Entity\Blog as BlogEntity;

class SocialBlogNotification extends SocialNotification {
	public function getParams() {
		$params = parent::getParams();

		$params['primary-link-label'] = wfMessage( 'bs-social-notification-blog-primary-link-label' )->plain();

		$params['blogname'] = $this->entity->get( BlogEntity::ATTR_BLOG_TITLE );
		$params['blogteaser'] = $this->entity->get( BlogEntity::ATTR_TEASER_TEXT );

		return $params;
	}
}