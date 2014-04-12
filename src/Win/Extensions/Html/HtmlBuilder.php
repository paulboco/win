<?php namespace Win\Extensions\Html;

use Illuminate\Html\HtmlBuilder as IlluminateHtmlBuilder;
use QueryString;

class HtmlBuilder extends IlluminateHtmlBuilder {

    /**
     * Sortable table header
     *
     * @param  stdClass  $repo
     * @param  string    $field
     * @param  string    $title
     * @return string
     */
    public function sortableHeader($repo, $field, $title)
    {
        $route = "shiphed.{$repo->table_name}.index";

        // Disable link when search is active.
        // if ($repo->query->q)
        // {
        //     return $title;
        // }

        $query = clone $repo->query;

        // Build the link for the active field
        // and toggle direction and arrow.
        $direction = 'asc';
        $arrow = '';

        if ($query->o == $field)
        {
            if ($query->d == 'asc')
             {
                $direction = 'desc';
             }

            $arrow = $direction == 'desc' ? ' &#9650;' : ' &#9660;';
        }

        // Set the orderby and direction.
        $query->o = $field;
        $query->d = $direction;

        return link_to_route($route, $title . $arrow, $query->toArray());
    }

    /**
     * Filter tabs for listing
     *
     * @param  stdClass  $repo
     * @return string
     */
    public function filterTabs($repo)
    {
        $route = "shiphed.{$repo->table_name}.index";

        $allClass     = $repo->query->f == 'all'     ? ' class="active"' : '';
        $trashedClass = $repo->query->f == 'trashed' ? ' class="active"' : '';

        // Open unordered list
        $tabs  = '<ul class="nav nav-tabs">';
        $tabs .= '<li>&nbsp;</li>';

        // Build the All tab
        $allQuery = QueryString::getByFilter($route, 'all');
        $tabs .= "<li{$allClass}>" . link_to_route($route, 'All', $allQuery) . '</li>';

        // Build the Trashed tab
        $trashedQuery = QueryString::getByFilter($route, 'trashed');
        $tabs .= "<li{$trashedClass}>" . link_to_route($route, 'Trashed', $trashedQuery) . '</li>';

        // Build the New and Reset tabs
        $tabs .= '<li>' . link_to_route("shiphed.{$repo->table_name}.create", 'Create New ' . $repo->model_name) . '</li>';
        // $tabs .= '<li>' . link_to_route('shiphed.reset', 'Reset', [$route]) . '</li>';

        return $tabs .= '</ul>';
    }

    /**
     * Highlight the last row updated
     *
     * @param  stdClass  $repo
     * @param  stdClass  $item
     * @return string
     */
    public function tableRow($repo, $item)
    {
        $classes[] = $repo->last_updated == $item->updated_at ? 'updated-at' : null;
        $classes[] = (isset($item->deleted_at) and $item->deleted_at) ? 'deleted-at' : null;

        $classes = array_space($classes);

        return '<tr class="' . $classes . '">';
    }

    /**
     * CRUD links
     *
     * @param  stdClass  $repo
     * @param  stdClass  $item
     * @return string
     */
    public function crudLinks($repo, $item)
    {
        if (isset($item->deleted_at) and $item->deleted_at)
        {
            $html  =  '<a href="' . route("shiphed.{$repo->table_name}.undelete", $item->id) . '"><i class="glyphicon glyphicon-share"></i></a>';
            return $html;
        }
        else
        {
            $html  = '<a href="' . route("shiphed.{$repo->table_name}.show", $item->id) . '"><i class="glyphicon glyphicon-eye-open"></i></a>';
            $html .= '<a href="' . route("shiphed.{$repo->table_name}.edit", $item->id) . '"><i class="glyphicon glyphicon-edit"></i></a>';

            return $html;
        }
    }

    /**
     * The index reset link
     *
     * @param  stdClass  $repo
     * @return string
     */
    public function indexResetLink($route)
    {
        $html  = '<a href="' . route('shiphed.reset', ['route' => $route]) . '">';
        $html .= '<span style="color:white;font-size:larger;" class="glyphicon glyphicon-flash"></span>';
        $html .= '</a>';

        return $html;
    }


}