<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template;

use LaravelEnso\VueDatatable\app\Classes\Template\Builders\Buttons;
use LaravelEnso\VueDatatable\app\Classes\Template\Builders\Columns;
use LaravelEnso\VueDatatable\app\Classes\Template\Builders\Structure;
use LaravelEnso\VueDatatable\app\Classes\Template\Builders\Style;

class Builder
{
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function run()
    {
        (new Columns($this->template))->build();
        (new Buttons($this->template))->build();
        (new Style($this->template))->build();
        (new Structure($this->template))->build();
    }
}
