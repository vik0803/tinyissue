<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Traits\User\Activity\RelationTrait;
use Tinyissue\Model\Activity as ActivityModel;
use Tinyissue\Model\User;

/**
 * Activity is model class for user activities.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int $id
 * @property string $data
 * @property int $type_id
 * @property int $parent_id
 * @property int $user_id
 * @property int $item_id
 * @property int $action_id
 * @property ActivityModel $activity
 * @property Issue $issue
 * @property User $user
 * @property User $assignTo
 * @property Issue\Comment $comment
 * @property Project $project
 * @property Project\Note $note
 */
class Activity extends Model
{
    use RelationTrait;

    /**
     * Timestamp enabled.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Name of database table.
     *
     * @var string
     */
    protected $table = 'users_activity';

    /**
     * List of allowed columns to be used in $this->fill().
     *
     * @var array
     */
    protected $fillable = ['type_id', 'parent_id', 'user_id', 'item_id', 'action_id', 'data'];

    /**
     * List of columns and their cast data-type.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get a value from the data field using "dot" notation.
     *
     * @param string $name
     *
     * @return Collection
     */
    public function dataCollection($name)
    {
        return new Collection($this->dataValue($name));
    }

    /*
     * Get a value from the data field using "dot" notation.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function dataValue($name)
    {
        return array_get($this->data, $name);
    }
}
