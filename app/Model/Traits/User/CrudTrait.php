<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\User;

use Hash;
use Illuminate\Database\Eloquent;
use Mail;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;

/**
 * CrudTrait is trait class containing the methods for adding/editing/deleting the User model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait CrudTrait
{
    /**
     * Add a new user.
     *
     * @param array $info
     *
     * @return bool
     */
    public function createUser(array $info)
    {
        $insert = [
            'email'     => $info['email'],
            'firstname' => $info['firstname'],
            'lastname'  => $info['lastname'],
            'role_id'   => $info['role_id'],
            'private'   => (boolean) $info['private'],
            'password'  => Hash::make($info['password']),
            'status'    => $info['status'],
            'language'  => app('tinyissue.settings')->getLanguage(),
        ];

        return $this->fill($insert)->save();
    }

    /**
     * Soft deletes a user and empties the email.
     *
     * @return bool
     */
    public function delete()
    {
        $this->update([
            'email'   => $this->email . '_deleted',
            'deleted' => User::DELETED_USERS,
        ]);
        Project\User::where('user_id', '=', $this->id)->delete();

        return true;
    }

    /**
     * Updates the users settings, validates the fields.
     *
     * @param array $info
     *
     * @return Eloquent\Model
     */
    public function updateSetting(array $info)
    {
        $update = array_intersect_key($info, array_flip([
            'email',
            'firstname',
            'lastname',
            'language',
            'password',
            'private',
            'status',
        ]));

        return $this->updateUser($update);
    }

    /**
     * Update the user.
     *
     * @param array $info
     *
     * @return Eloquent\Model
     */
    public function updateUser(array $info = [])
    {
        if ($info['password']) {
            $info['password'] = Hash::make($info['password']);
        } elseif (empty($info['password'])) {
            unset($info['password']);
        }

        return $this->update($info);
    }

    /**
     * Update user messages setting.
     *
     * @param array $input
     */
    public function updateMessagesSettings(array $input)
    {
        return (new Project\User())
            ->where('user_id', '=', $this->id)
            ->whereIn('project_id', array_keys($input))
            ->get()
            ->each(function (Project\User $project) use ($input) {
                $project->message_id = $input[$project->project_id];
                $project->save();
            });
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     *
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    abstract public function fill(array $attributes);
}
