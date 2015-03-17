<?php

namespace re24\Qiiter\Entity;

/**
 * Tag Entity class
 * 
 * @property int $followers_count
 * @property string $icon_url
 * @property string $id
 * @property int $items_count
 */
class Tag extends AbstractEntity
{
	protected $followers_count;
	protected $icon_url;
	protected $id;
	protected $items_count;
}

