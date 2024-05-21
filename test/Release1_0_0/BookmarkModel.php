<?php

namespace jurasciix\objeckson\test\Release1_0_0;

use jurasciix\objeckson\JsonProperty;

class BookmarkModel {

    #[JsonProperty]
    public string $description;

    /**
     * @var string[]
     */
    #[JsonProperty]
    public array $tags;

    /**
     * @var PairModel<string, AttributeModel>[]
     */
    #[JsonProperty]
    public array $attributes;
}