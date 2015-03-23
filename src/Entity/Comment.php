<?php

namespace re24\Qiiter\Entity;

/**
 * Comment entity class
 *
 * @property string $body
 * @property string $created_at
 * @property string $id
 * @property string $rendered_body
 * @property string $updated_at
 * @property User $user
 */
class Comment extends AbstractEntity
{
    protected $body;
    protected $created_at;
    protected $id;
    protected $rendered_body;
    protected $updated_at;
    protected $user;
}
