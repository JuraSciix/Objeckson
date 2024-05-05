<?php

namespace jurasciix\objeckson\test;

use jurasciix\objeckson\JsonProperty;

class Model {

    /**
     * @var string|null|null
     */
    #[JsonProperty]
    public ?string $foo;

    /**
     * @var PairModel<string, string>|null
     */
    #[JsonProperty]
    public ?PairModel $pair;

    /**
     * @var IssueModel|Cortege|null
     */
    #[JsonProperty]
    public IssueModel|Cortege|null $x;
}