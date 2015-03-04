<?php

namespace Tinyissue\Extensions\Html;

class FormBuilder extends \Illuminate\Html\FormBuilder
{
    public function form(\Tinyissue\Form\FormInterface $form, array $attrs = [])
    {
        $model = $form->getModel();
        if ($model instanceof \Illuminate\Database\Eloquent\Model) {
            \Former::populate($model);
        }

        $formType = $form->openType();
        $former = \Former::$formType();
        array_walk($attrs, function ($value, $attr) use ($former) {
            if ($value === null) {
                $former->$attr();
            } else {
                $former->$attr($value);
            }
        });
        $form->rules($form->rules());

        $output = $former;
        $fields = $form->fields();
        foreach ($fields as $name => $field) {
            $element = $this->element($name, $field);

            if ($element instanceof \Former\Traits\Field) {
                if (null === $model) {
                    $element->value(\Request::input($name));
                }
            }

            $output .= $element;
        }

        $actions = \Former::actions()->addClass('form-actions');
        $buttons = $form->actions();
        foreach ($buttons as $name => $options) {
            if (is_array($options)) {
                $actions->{$options['type']}($options['label'], $options);
            } else {
                $actions->primary_submit(trans('tinyissue.' . $options));
            }
        }
        $output .= $actions;

        $output .= \Former::close();

        return $output;
    }

    /**
     * @param $name
     * @param $field
     *
     * @return mixed
     */
    public function element($name, $field)
    {
        $type = $field['type'];
        unset($field['type']);

        $element = \Former::$type($name);

        array_walk($field, function ($value, $attr) use ($element) {
            if ($value === null) {
                $element->$attr();
            } else {
                $element->$attr($value);
            }
        });

        return $element;
    }
}