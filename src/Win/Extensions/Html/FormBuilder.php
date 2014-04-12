<?php namespace Win\Extensions\Html;

use Illuminate\Html\FormBuilder as IlluminateFormBuilder;
use QueryString, Session;

class FormBuilder extends IlluminateFormBuilder {

    protected $gridClass = 'col-md-';
    protected $labelClass = 4;
    protected $inputClass = 8;


    /**
     * Bootstrap form group input
     *
     * @param  string  $type
     * @param  string  $name
     * @param  string  $label
     * @param  mixed   $value
     * @param  Illuminate\Support\MessageBag   $errors
     * @return string
     */
    public function groupInput($type, $name, $label, $value, $errors)
    {
        $label = $this->groupLabel($name, $label);

        $attributes = ['id' => $name, 'class' => 'form-control'];

        $input = $this->$type($name, $value, $attributes)
               . $errors->first($name, '<span class="help-block">:message</span>');

        $input = $this->groupContainer($input);

        return $this->wrapGroup($label . $input, $name);
    }

    /**
     * Bootstrap date picker
     *
     * @param  string  $name
     * @param  string  $label
     * @param  mixed   $value
     * @param  Illuminate\Support\MessageBag   $errors
     * @return string
     */
    public function groupDatePicker($name, $label, $value, $errors)
    {
        $label = $this->groupLabel($name, $label);

        $attributes = ['id' => $name, 'class' => 'form-control datepicker'];

        $input = $this->text($name, $value, $attributes)
               . $errors->first($name, '<span class="help-block">:message</span>');

        $input = $this->groupContainer($input);

        return $this->wrapGroup($label . $input, $name);
    }

    /**
     * CRUD buttons: 'Save', 'Delete' and 'Close'
     *
     * @param  string  $route
     * @param  boolean $mode
     * @return string
     */
    public function crudButtons($route, $mode)
    {
        $uri = route($route, QueryString::getByRoute($route));

        $buttons  = '<a href="' . $uri . '" class="btn btn-default">Close</a> ';

        if ($mode == 'edit' or $mode == 'create')
        {
            $buttons .= '<button type="submit" class="btn btn-primary">Save</button> ';
        }

        if ($mode == 'edit')
        {
            $buttons .= '<a class="btn btn-danger pull-right" data-toggle="modal" data-target=".modal-delete-form">Delete</a> ';
        }

        if($mode == 'undelete')
        {
            $buttons .= '<a class="btn btn-danger pull-right" data-toggle="modal" data-target=".modal-undelete-form">Un-Delete</a> ';
        }

        return $this->wrapGroup($this->groupContainer($buttons, true));
    }

    /**
     * Twitter typeahead
     *
     * @param  string    $displayKey
     * @param  string    $valueKey
     * @param  string    $label
     * @param  stdClass  $repo
     * @return string
     */
    public function typeahead($displayKey, $valueKey, $label, $repo)
    {
        $id = $repo->data->{$valueKey};

        $label = '<label for="' . $displayKey . '" class="col-md-4 control-label">'
               . '<span data-toggle="tooltip" data-placement="top" title="' . $id . '">' . $label . '</span>'
               . '</label>';

        $input = '<input id="' . $displayKey . '" name="' . $displayKey . '" value="' . $repo->data->$displayKey . '" class="typeahead-' . $displayKey . ' form-control" type="text">'
               . '<input type="hidden" name="' . $valueKey . '" value="' . $repo->data->$valueKey . '" id="' . $valueKey . '">';

        $input  = $this->groupContainer($input);

        return $this->wrapGroup($label . $input, $valueKey);
    }

    /**
     * Javascript for twitter typeahead
     *
     * @param  string    $displayKey
     * @param  string    $valueKey
     * @param  string    $route
     * @return string
     */
    public function typeaheadJs($displayKey, $valueKey, $route)
    {
        $html  = '<script type="text/javascript">';
        $html .= '$(document).ready(function() {';
        $html .= '    var ' . $displayKey . ' = new Bloodhound({';
        $html .= '        datumTokenizer: Bloodhound.tokenizers.obj.whitespace("' . $displayKey . '"),';
        $html .= '        queryTokenizer: Bloodhound.tokenizers.whitespace,';
        $html .= '        limit: 100,';
        $html .= '        remote: "' . route($route) . '?q=%QUERY"';
        $html .= '    });';
        $html .=      $displayKey . '.initialize();';
        $html .= '    $(".typeahead-' . $displayKey . '").typeahead({';
        $html .= '        minLength: 2,';
        $html .= '        highlight: true';
        $html .= '    },';
        $html .= '    {';
        $html .= '        displayKey: "' . $displayKey . '",';
        $html .= '        source: ' . $displayKey . '.ttAdapter()';
        $html .= '    })';
        $html .= '    .on("typeahead:selected", function($e, datum) {';
        $html .= '        $("#' . $valueKey . '").val(datum["id"]);';
        $html .= '    });';
        $html .= '});';
        $html .= '</script>';

        return $html;
    }

    /**
     * Wrap html with form-group class.
     *
     * @param  string  $html
     * @param  string  $name
     * @return string
     */
    public function wrapGroup($html, $name = '')
    {
        if ($errors = Session::get('errors'))
        {
            $errors = $errors->has($name) ? ' has-error' : '';
        }

        return '<div class="form-group' . $errors . '">' . $html . '</div>';
    }

    /**
     * Group label
     *
     * @param  string  $name
     * @param  string  $label
     * @return string
     */
    public function groupLabel($name, $label = null)
    {
        $class = $this->gridClass . $this->labelClass;

        $attributes = [
            'class' => 'control-label ' . $class,
        ];

        return $this->label($name, $label, $attributes);
    }

    /**
     * Group input control container
     *
     * @param  string   $html
     * @param  boolean  $offset
     * @return string
     */
    public function groupContainer($input, $offset = false)
    {
        $class[] = $this->gridClass . $this->inputClass;

        if ($offset)
        {
            $class[] = $this->gridClass . 'offset-' . $this->labelClass;
        }

        return '<div class="' . array_space($class) . '">' . $input . '</div>';
    }


}