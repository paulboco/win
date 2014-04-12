<?php namespace Win\Extensions\Html;

use Illuminate\Html\FormBuilder as IlluminateFormBuilder;
use QueryString;

class FormBuilder extends IlluminateFormBuilder {

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
        $hasError = $errors->has($name) ? ' has-error' : '';

        $html  = '<div class="form-group' . $hasError . '">';
        $html .= '<label for="'. $name . '" class="col-md-4 control-label">' . $label . '</label>';
        $html .= '<div class="col-md-8">';
        $html .= $this->$type($name, $value, ['id' => $name, 'class' => 'form-control']);
        $html .= $errors->first($name, '<span class="help-block">:message</span>');
        $html .= '</div>';
        $html .= '</div>';

        return $html;
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
        $hasError = $errors->has($name) ? ' has-error' : '';

        $html  = '<div class="form-group' . $hasError . '">';
        $html .= '<label for="'. $name . '" class="col-md-4 control-label">' . $label . '</label>';
        $html .= '<div class="col-md-8">';
        $html .= $this->text($name, $value, ['id' => $name, 'class' => 'form-control datepicker']);
        $html .= $errors->first($name, '<span class="help-block">:message</span>');
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * CRUD buttons: 'Save', 'Delete' and 'Close'
     *
     * @param  string  $route
     * @param  boolean $mode  show=close, create=save & close, edit=save, delete & close
     * @return string
     */
    public function crudButtons($route, $mode)
    {
        $uri = route($route, QueryString::getByRoute($route));

        $html  = '<div class="form-group">';
        $html .= '<div class="col-sm-offset-4 col-sm-8">';
        $html .= '<a href="' . $uri . '" class="btn btn-default">Close</a> ';
        $html .= ($mode == 'edit' or $mode == 'create') ? '<button type="submit" class="btn btn-primary">Save</button> ' : '';
        $html .= ($mode == 'edit') ? '<a class="btn btn-danger pull-right" data-toggle="modal" data-target=".modal-delete-form">Delete</a> ' : '';
        $html .= ($mode == 'undelete') ? '<a class="btn btn-danger pull-right" data-toggle="modal" data-target=".modal-undelete-form">Un-Delete</a> ' : '';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
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

        $html  = '<div class="form-group">';
        $html .= '<label for="' . $displayKey . '" class="col-md-4 control-label"><span data-toggle="tooltip" data-placement="top" title="' . $id . '">' . $label . '</span></label>';
        $html .= '<div class="col-md-8">';
        $html .= '<input id="' . $displayKey . '" name="' . $displayKey . '" value="' . $repo->data->$displayKey . '" class="typeahead-' . $displayKey . ' form-control" type="text">';
        $html .= '<input type="hidden" name="' . $valueKey . '" value="' . $repo->data->$valueKey . '" id="' . $valueKey . '">';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
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


}