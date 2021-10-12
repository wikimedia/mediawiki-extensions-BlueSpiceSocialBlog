<?php

namespace BlueSpice\Social\Blog\HookHandler;

use BlueSpice\Social\Blog\MainLinkPanel;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class DiscoverySkin implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'MainLinksPanel',
			[
				'special-blog' => [
					'factory' => static function () {
						return new MainLinkPanel();
					},
					'position' => 40
				]
			]
		);
	}
}
