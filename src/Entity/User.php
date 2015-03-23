<?php

namespace re24\Qiiter\Entity;

/**
 * User Entity class
 *
 * @property string|null $description
 * @property string|null  $facebook_id
 * @property int  $followees_count
 * @property int  $followers_count
 * @property string|null  $github_login_name
 * @property string  $id
 * @property int  $items_count
 * @property string|null  $linkedin_id
 * @property string|null  $location
 * @property string|null  $name
 * @property string|null  $organization
 * @property string  $profile_image_url
 * @property string|null  $twitter_screen_name
 * @property string|null  $website_url
 */
class User extends AbstractEntity
{
    protected $description;
    protected $facebook_id;
    protected $followees_count;
    protected $followers_count;
    protected $github_login_name;
    protected $id;
    protected $items_count;
    protected $linkedin_id;
    protected $location;
    protected $name;
    protected $organization;
    protected $profile_image_url;
    protected $twitter_screen_name;
    protected $website_url;
}
