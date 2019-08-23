<?php

namespace BlueSpice\Social\Blog\Notification;

class Registrator {
	public static function registerNotifications( \BlueSpice\NotificationManager $notificationsManager ) {
		$echoNotifier = $notificationsManager->getNotifier();

		$config = [
			'category' => 'bs-social-entity-cat',
			'summary-params' => [
				'agent', 'realname', 'blogname'
			],
			'email-subject-params' => [
				'agent', 'realname', 'blogname'
			],
			'email-body-params' => [
				'agent', 'realname', 'blogname', 'blogteaser'
			],
			'web-body-params' => [
				'agent', 'realname', 'blogname'
			]
		];

		$notificationsManager->registerNotification(
			'bs-social-blog-create',
			array_merge( $config, [
				'summary-message' => 'bs-social-notifications-blog-create',
				'email-subject-message' => 'bs-social-notifications-email-blog-create-subject',
				'email-body-message' => 'bs-social-notifications-email-blog-create-body',
				'web-body-message' => 'bs-social-notifications-web-blog-create-body',
				'extra-params' => [
					'secondary-links' => [
						'agentlink' => []
					],
					'icon' => 'edit'
				]
			] )
		);

		$notificationsManager->registerNotification(
			'bs-social-blog-edit',
			array_merge( $config, [
				'summary-message' => 'bs-social-notifications-blog-edit',
				'email-subject-message' => 'bs-social-notifications-email-blog-edit-subject',
				'email-body-message' => 'bs-social-notifications-email-blog-edit-body',
				'web-body-message' => 'bs-social-notifications-web-blog-edit-body',
				'extra-params' => [
					'secondary-links' => [
						'agentlink' => []
					],
					'icon' => 'edit'
				]
			] )
		);

		$notificationsManager->registerNotification(
			'bs-social-blog-delete',
			array_merge( $config, [
				'summary-message' => 'bs-social-notifications-blog-delete',
				'email-subject-message' => 'bs-social-notifications-email-blog-delete-subject',
				'email-body-message' => 'bs-social-notifications-email-blog-delete-body',
				'web-body-message' => 'bs-social-notifications-web-blog-delete-body',
				'extra-params' => [
					'secondary-links' => [
						'agentlink' => []
					],
					'icon' => 'delete'
				]
			] )
		);
	}
}