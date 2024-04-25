<?php

namespace jurasciix\objeckson\test;

use jurasciix\objeckson\JsonAdapter;

#[JsonAdapter(new CortegeAdapter())]
class Cortege {

    public int $x;
    public int $y;
    public int $z;
}