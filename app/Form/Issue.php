<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Form;

use Tinyissue\Model;

/**
 * Issue is a class to defines fields & rules for add/edit issue form.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Issue extends FormAbstract
{
    /**
     * An instance of project model.
     *
     * @var Model\Project
     */
    protected $project;

    /**
     * Collection of all tags.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $tags = null;

    /**
     * @param string $type
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    protected function getTags($type = null)
    {
        if ($this->tags === null) {
            $this->tags = (new Model\Tag())->getGroupTags();
        }

        if ($type) {
            return $this->tags->where('name', $type)->first()->tags;
        }

        return $this->tags;
    }

    /**
     * Get issue tag for specific type/group.
     *
     * @param string $type
     *
     * @return int
     */
    protected function getIssueTag($type)
    {
        if ($this->isEditing()) {
            $groupId     = $this->getTags($type)->first()->parent_id;
            $selectedTag = $this->getModel()->tags->where('parent_id', $groupId);

            if ($selectedTag->count() > 0) {
                return $selectedTag->last();
            }
        }

        return new Model\Tag();
    }

    /**
     * @param array $params
     *
     * @return void
     */
    public function setup(array $params)
    {
        $this->project = $params['project'];
        if (!empty($params['issue'])) {
            $this->editingModel($params['issue']);
        }
    }

    /**
     * @return array
     */
    public function actions()
    {
        $actions = [
            'submit' => $this->isEditing() ? 'update_issue' : 'create_issue',
        ];

        if ($this->isEditing() && auth()->user(Model\Permission::PERM_ISSUE_MODIFY)) {
            $actions['delete'] = [
                'type'         => 'danger_submit',
                'label'        => trans('tinyissue.delete_something', ['name' => '#' . $this->getModel()->id]),
                'class'        => 'close-issue',
                'name'         => 'delete-issue',
                'data-message' => trans('tinyissue.delete_issue_confirm'),
            ];
        }

        return $actions;
    }

    /**
     * @return array
     */
    public function fields()
    {
        $issueModify = \Auth::user()->permission('issue-modify');

        $fields = $this->fieldTitle();
        $fields += $this->fieldBody();
        $fields += $this->fieldTypeTags();

        // Only on creating new issue
        if (!$this->isEditing()) {
            $fields += $this->fieldUpload();
        }

        // Show fields for users with issue modify permission
        if ($issueModify) {
            $fields += $this->issueModifyFields();
        }

        return $fields;
    }

    /**
     * Return a list of fields for users with issue modify permission.
     *
     * @return array
     */
    protected function issueModifyFields()
    {
        $fields = [];

        $fields['internal_status'] = [
            'type' => 'legend',
        ];

        // Status tags
        $fields += $this->fieldStatusTags();

        // Assign users
        $fields += $this->fieldAssignedTo();

        // Quotes
        $fields += $this->fieldTimeQuote();

        // Resolution tags
        $fields += $this->fieldResolutionTags();

        return $fields;
    }

    /**
     * Returns title field.
     *
     * @return array
     */
    protected function fieldTitle()
    {
        return [
            'title' => [
                'type'  => 'text',
                'label' => 'title',
            ],
        ];
    }

    /**
     * Returns body field.
     *
     * @return array
     */
    protected function fieldBody()
    {
        return [
            'body' => [
                'type'  => 'textarea',
                'label' => 'issue',
            ],
        ];
    }

    /**
     * Returns status tag field.
     *
     * @return array
     */
    protected function fieldStatusTags()
    {
        $currentTag = $this->getIssueTag('status');

        if ($currentTag && !$currentTag->canView()) {
            $tags = [$currentTag];
        } else {
            $tags = $this->getTags('status');
        }

        $options = [];
        foreach ($tags as $tag) {
            $options[ucwords($tag->name)] = [
                'name'      => 'tag_status',
                'value'     => $tag->id,
                'data-tags' => $tag->id,
                'color'     => $tag->bgcolor,
            ];
        }

        $fields['tag_status'] = [
            'label'  => 'status',
            'type'   => 'radioButton',
            'radios' => $options,
            'check'  => $this->getIssueTag('status')->id,
        ];

        return $fields;
    }

    /**
     * Returns tags field.
     *
     * @return array
     */
    protected function fieldTypeTags()
    {
        $currentTag = $this->getIssueTag('type');

        if ($currentTag && !$currentTag->canView()) {
            $tags = [$currentTag];
        } else {
            $tags = $this->getTags('type');
        }

        $options = [];
        foreach ($tags as $tag) {
            $options[ucwords($tag->name)] = [
                'name'      => 'tag_type',
                'value'     => $tag->id,
                'data-tags' => $tag->id,
                'color'     => $tag->bgcolor,
            ];
        }

        $fields['tag_type'] = [
            'label'  => 'type',
            'type'   => 'radioButton',
            'radios' => $options,
            'check'  => $this->getIssueTag('type')->id,
        ];

        return $fields;
    }

    /**
     * Returns tags field.
     *
     * @return array
     */
    protected function fieldResolutionTags()
    {
        $currentTag = $this->getIssueTag('resolution');

        if ($currentTag && !$currentTag->canView()) {
            $tags = [$currentTag];
        } else {
            $tags = $this->getTags('resolution');
        }

        $options = [
            trans('tinyissue.none') => [
                'name'      => 'tag_resolution',
                'value'     => 0,
                'data-tags' => 0,
                'color'     => '#62CFFC',
            ],
        ];
        foreach ($tags as $tag) {
            $options[ucwords($tag->name)] = [
                'name'      => 'tag_resolution',
                'value'     => $tag->id,
                'data-tags' => $tag->id,
                'color'     => $tag->bgcolor,
            ];
        }

        $fields['tag_resolution'] = [
            'label'  => 'resolution',
            'type'   => 'radioButton',
            'radios' => $options,
            'check'  => $this->getIssueTag('resolution')->id,
        ];

        return $fields;
    }

    /**
     * Returns assigned to field.
     *
     * @return array
     */
    protected function fieldAssignedTo()
    {
        return [
            'assigned_to' => [
                'type'    => 'select',
                'label'   => 'assigned_to',
                'options' => [0 => ''] + $this->project->usersCanFixIssue()->get()->lists('fullname', 'id')->all(),
                'value'   => (int) $this->project->default_assignee,
            ],
        ];
    }

    /**
     * Returns upload field.
     *
     * @return array
     */
    protected function fieldUpload()
    {
        $user                      = \Auth::guest() ? new Model\User() : \Auth::user();
        $fields                    = $this->projectUploadFields('upload', $this->project, $user);
        $fields['upload']['label'] = 'attachments';

        return $fields;
    }

    /**
     * Returns time quote field.
     *
     * @return array
     */
    protected function fieldTimeQuote()
    {
        $fields = [
            'time_quote' => [
                'type'     => 'groupField',
                'label'    => 'quote',
                'fields'   => [
                    'h'    => [
                        'type'          => 'number',
                        'append'        => trans('tinyissue.hours'),
                        'value'         => $this->extractQuoteValue('h'),
                        'addGroupClass' => 'col-sm-5 col-md-5 col-lg-4',
                    ],
                    'm'    => [
                        'type'          => 'number',
                        'append'        => trans('tinyissue.minutes'),
                        'value'         => $this->extractQuoteValue('m'),
                        'addGroupClass' => 'col-sm-5 col-md-5 col-lg-4',
                    ],
                    'lock' => [
                        'type'          => 'checkboxButton',
                        'label'         => '',
                        'noLabel'       => true,
                        'class'         => 'eee',
                        'addGroupClass' => 'sss col-sm-12 col-md-12 col-lg-4',
                        'checkboxes'    => [
                            'Lock Quote' => [
                                'value'     => 1,
                                'data-tags' => 1,
                                'color'     => 'red',
                                'checked'   => $this->isEditing() && $this->getModel()->isQuoteLocked(),
                            ],
                        ],
                        'grouped'       => true,
                    ],
                ],
                'addClass' => 'row issue-quote',
            ],
        ];

        // If user does not have access to lock quote, then remove the field
        if (!auth()->user()->permission(Model\Permission::PERM_ISSUE_LOCK_QUOTE)) {
            unset($fields['time_quote']['fields']['lock']);

            // If quote is locked then remove quote fields
            if ($this->isEditing() && $this->getModel()->isQuoteLocked()) {
                return [];
            }
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|max:200',
            'body'  => 'required',
        ];

        return $rules;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        if ($this->isEditing()) {
            return $this->getModel()->to('edit');
        }

        return 'project/' . $this->project->id . '/issue/new';
    }

    /**
     * Extract number of hours, or minutes, or seconds from a quote.
     *
     * @param string $part
     *
     * @return float|int
     */
    protected function extractQuoteValue($part)
    {
        if ($this->getModel() instanceof Model\Project\Issue) {
            $seconds = $this->getModel()->time_quote;
            if ($part === 'h') {
                return floor($seconds / 3600);
            }

            if ($part === 'm') {
                return ($seconds / 60) % 60;
            }
        }

        return 0;
    }
}
