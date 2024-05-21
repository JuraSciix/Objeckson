<?php

namespace jurasciix\objeckson\test\Release1_0_0;

use jurasciix\objeckson\JsonAdapter;

#[JsonAdapter(new CortegeAdapter())]
class Cortege {

    public int $x;
    public int $y;
    public int $z;
}