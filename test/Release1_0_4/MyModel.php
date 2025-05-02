<?php

namespace jurasciix\objeckson\test\Release1_0_4;

use jurasciix\objeckson\JsonAdapter;

#[JsonAdapter(new MyOwnAdapter())]
class MyModel {

}