<?php

$extDir = dirname( dirname( __DIR__ ) );

require_once( "$extDir/BlueSpiceFoundation/maintenance/BSMaintenance.php" );

use BlueSpice\Social\Blog\Entity\Blog;
use BlueSpice\Social\Blog\EntityListContext\SpecialBlog as Context;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Data\FieldType;
use BlueSpice\Data\Filter\ListValue;

class MayFixMigratedBlogTimestamps extends Maintenance {

	protected $store = null;
	protected $data = [];

	public function __construct() {
		parent::__construct();

		//we hope, that the default blog namespace was used or the current blog
		//namespace is still defined in any configuration file!
		global $wgExtraNamespaces, $bsgSystemNamespaces;
		if( !defined( 'NS_BLOG' ) ) {
			define( 'NS_BLOG', 1502 );
			$wgExtraNamespaces[NS_BLOG] = 'Blog';
			$bsgSystemNamespaces[1502] = 'NS_BLOG';
		}

		if( !defined( 'NS_BLOG_TALK' ) ) {
			define( 'NS_BLOG_TALK', 1503 );
			$wgExtraNamespaces[NS_BLOG_TALK] = 'Blog_talk';
			$bsgSystemNamespaces[1503] = 'NS_BLOG_TALK';
		}

		$this->requireExtension( "BlueSpiceSocial" );

		$this->addOption( 'execute', 'Really execute the script' );

	}

	public function execute() {
		$this->output( "...bs_blog -> migration timestamp check...\n" );

		$execute = $this->getOption( 'execute', false ) ? true : false;

		$this->readData();
		$this->output( count($this->data). " blogs\n" );
		foreach( $this->data as $articleId => $blog ) {
			$title = \Title::newFromRow( $blog );
			$this->output( "\n '{$title->getText()}': " );
			
			if( !$entity = $this->getEntity( $title ) ) {
				$this->output( "ERROR: Entity Blog could not be found :(" );
				$this->output( "\n" );
				continue;
			}
			$this->output(
				" SocialEntity:{$entity->get( Blog::ATTR_ID, 0 )}"
			);
			$this->output( "\n    -Title last Rev ID: " );
			if( empty( $title->getLatestRevID() ) ) {
				$this->output( "ERROR: empty" );
				$this->output( "\n" );
				continue;
			}
			$this->output( $title->getLatestRevID() );
			$date = \DateTime::createFromFormat(
				'YmdHis',
				$title->getEarliestRevTime()
			);
			$this->output( "\n    -Title last Rev TS: " );
			if( !$date || !$ts = $date->format( 'YmdHis' ) ) {
				$this->output( "ERROR: invalid TS :(" );
				$this->output( "\n" );
				continue;
			}
			$this->output( "$ts ({$date->format( 'd.m.Y H:i:s' )})" );
			$this->output( "\n    =>Overwrite Blog TS: " );
			if( !$execute ) {
				$this->output( "not really.. use '--exucute'" );
				$this->output( "\n" );
				continue;
			}
			$result = $this->modifySourceTitleTimestamp(
				$entity->getTitle(),
				$title
			);
			$this->output( $result ? "OK" : "FAILED, unknown" );
			$this->output( "\n" );
		}
		$this->output( "\n" );
		return true;
	}

	protected function readData() {
		$res = $this->getDB( DB_REPLICA )->select(
			'page',
			'*',
			[ 'page_namespace' => NS_BLOG ] 
			
		);
		foreach( $res as $row ) {
			$this->data[$row->page_id] = $row;
		}
	}

	/**
	 *
	 * @param \stdClass $shout
	 * @param \Title $title
	 * @return Blog
	 */
	protected function getEntity( \Title $title ) {
		try {
			$store = $this->getStore();
			$params = new ReaderParams( [
				'filter' => [
					(object) [
						ListValue::KEY_PROPERTY => Blog::ATTR_TYPE,
						ListValue::KEY_VALUE => [ Blog::TYPE ],
						ListValue::KEY_COMPARISON => ListValue::COMPARISON_CONTAINS,
						ListValue::KEY_TYPE => FieldType::LISTVALUE
					],
					(object) [
						ListValue::KEY_PROPERTY => Blog::ATTR_BLOG_TITLE,
						ListValue::KEY_VALUE => $title->getText(),
						ListValue::KEY_COMPARISON => ListValue::COMPARISON_CONTAINS,
						ListValue::KEY_TYPE => FieldType::STRING
					],
				],
				'sort' => [ (object) [
					'property' => Blog::ATTR_BLOG_TITLE,
					'direction' => 'ASC'
				]],
				'limit' => ReaderParams::LIMIT_INFINITE,
				'start' => 0,
			] );
			$context = new Context(
				new \BlueSpice\Context( \RequestContext::getMain(), $this->getEntityConfig() ),
				$this->getEntityConfig(),
				$this->getMaintenanceUser()
			);
			$resultSet = $store->getReader( $context )->read( $params );
			foreach( $resultSet->getRecords() as $record ) {
				return $this->getFactory()->newFromObject(
					(object) $record->getData()
				);
			}
		} catch ( \Exception $e ) {
			$this->output( $e->getMessage() );
			return null;
		}
		return null;
	}

	/**
	 * @retrun \BlueSpice\EntityFactory
	 */
	protected function getFactory() {
		return \BlueSpice\Services::getInstance()->getBSEntityFactory();
	}

	/**
	 * 
	 * @return \BlueSpice\EntityConfig;
	 */
	protected function getEntityConfig() {
		return \BlueSpice\Services::getInstance()->getBSEntityConfigFactory()
			->newFromType( Blog::TYPE );
	}

	/**
	 *
	 * @return \BlueSpice\Social\Data\Entity\Store
	 * @throws \MWException
	 */
	protected function getStore() {
		if( $this->store ) {
			return $this->store;
		}
		$storeClass = $this->getEntityConfig()->get( 'StoreClass' );
		if( !class_exists( $storeClass ) ) {
			throw new \MWException( "Store class '$storeClass' not found" );
		}
		$this->store = new $storeClass();
		return $this->store;
	}

	/**
	 *
	 * @param \Title $title
	 * @param \Title $origTitle
	 */
	protected function modifySourceTitleTimestamp( $title, $origTitle ) {
		if( !$title || empty( $title->getLatestRevID() ) ) {
			return false;
		}

		//dont use any MWTimestamp here, as they are not reliably in cmd!
		$date = \DateTime::createFromFormat(
			'YmdHis',
			$origTitle->getEarliestRevTime()
		);
		if( !$date || !$ts = $date->format( 'YmdHis' ) ) {
			return false;
		}

		//hacky, hope for the best ;)
		return $this->getDB( DB_MASTER )->update(
			'revision',
			[ 'rev_timestamp' => $ts ],
			[ 'rev_id' => $title->getLatestRevID() ],
			__METHOD__
		);
	}

	protected function getMaintenanceUser() {
		return \BlueSpice\Services::getInstance()->getBSUtilityFactory()
			->getMaintenanceUser()->getUser();
	}

}

$maintClass = 'MayFixMigratedBlogTimestamps';
require_once( RUN_MAINTENANCE_IF_MAIN );
