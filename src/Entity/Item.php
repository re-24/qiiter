<?php

namespace re24\Qiiter\Entity;

/**
 * Item Entity class
 * 
 * @property string $body
 * @property boolean $coediting 
 * @property string $created_at
 * @property string $id
 * @property boolean $private
 * @property string $rendered_body
 * @property array $tags
 * @property string $title
 * @property string $updated_at
 * @property string $url
 * @property boolean $gist
 * @property boolean $tweet
 * @property User $user
 */
class Item extends AbstractEntity
{
	protected $body;
	protected $coediting;
	protected $created_at;
	protected $id;
	protected $private;
	protected $rendered_body;
	protected $tags;
	protected $title;
	protected $updated_at;
	protected $url;
	protected $gist;
	protected $tweet;
	protected $user;
}