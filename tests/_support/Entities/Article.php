<?php

namespace Tests\Support\Entities;

use CodeIgniter\Entity\Entity;
use Michalsn\CodeIgniterTranslatable\Traits\TranslatableEntity;

class Article extends Entity
{
    use TranslatableEntity;

    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [];
}
