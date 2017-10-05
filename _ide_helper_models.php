<?php
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Framework{
/**
 * App\Framework\Model
 *
 * @mixin \Eloquent
 * @property int $current_user_id
 */
	class Model extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Group
 *
 * @property int $id
 * @property string $name
 * @property int $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereIsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereUpdatedAt($value)
 */
	class Group extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Menu
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property array $page_ids
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read mixed $pages
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Menu whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Menu whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Menu whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Menu wherePageIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Menu whereUpdatedAt($value)
 */
	class Menu extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Page
 *
 * @property int $id
 * @property int|null $page_id
 * @property int|null $back_page_id
 * @property int|null $owner_user_id
 * @property int|null $owner_group_id
 * @property int|null $access_owner
 * @property int|null $access_group
 * @property int|null $access_all
 * @property string $uri
 * @property string|null $controller
 * @property string|null $title
 * @property string|null $topic
 * @property string|null $menu
 * @property string|null $keywords
 * @property string|null $description
 * @property string|null $template_resource
 * @property string|null $template_env
 * @property string|null $template_view
 * @property bool $show_in_menu
 * @property int $menu_sort_order
 * @property bool $is_published
 * @property bool $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Page[] $child
 * @property-read \App\Models\PageForm $form
 * @property-read mixed $angular_link
 * @property-read mixed|null $env
 * @property-read mixed $http_link
 * @property-read mixed $is_current
 * @property-read string $link
 * @property-read array|null $parent
 * @property-read array $path
 * @property-read mixed|null $resource
 * @property-read mixed $sub_menu
 * @property-read mixed $view
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereAccessAll($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereAccessGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereAccessOwner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereBackPageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereController($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereIsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereMenu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereMenuSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereOwnerGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereOwnerUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereShowInMenu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereTemplateEnv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereTemplateResource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereTemplateView($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereTopic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Page whereUri($value)
 */
	class Page extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PageForm
 *
 * @property int $page_id
 * @property string|null $data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PageForm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PageForm whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PageForm whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PageForm wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PageForm whereUpdatedAt($value)
 */
	class PageForm extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property int|null $group_id
 * @property string $email
 * @property string|null $password
 * @property string|null $api_token
 * @property string|null $remember_token
 * @property string|null $name
 * @property int $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereApiToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereIsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 * @property int $id
 * @property int|null $group_id
 * @property string $email
 * @property string|null $password
 * @property string|null $api_token
 * @property string|null $remember_token
 * @property string|null $name
 * @property int $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereApiToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereIsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

